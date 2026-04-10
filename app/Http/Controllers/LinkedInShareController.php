<?php

namespace App\Http\Controllers;

use App\Models\DynamicFormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInShareController extends Controller
{
    private function clientId(): string
    {
        return config('services.linkedin.client_id');
    }

    private function clientSecret(): string
    {
        return config('services.linkedin.client_secret');
    }

    private function callbackUrl(): string
    {
        return config('services.linkedin.redirect');
    }

    /**
     * Redirect user to LinkedIn OAuth authorization page.
     */
    public function redirect(Request $request)
    {
        $submissionId = $request->query('submission_id');

        if (!$submissionId) {
            abort(400, 'Missing submission ID.');
        }

        // Encrypt submission ID into state to prevent CSRF
        $state = encrypt((string) $submissionId);

        $authUrl = 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query([
            'response_type' => 'code',
            'client_id'     => $this->clientId(),
            'redirect_uri'  => $this->callbackUrl(),
            'state'         => $state,
            'scope'         => 'openid profile w_member_social',
        ]);

        return redirect($authUrl);
    }

    /**
     * Handle LinkedIn OAuth callback — exchange code, upload image, create post.
     */
    public function callback(Request $request)
    {
        $code  = $request->query('code');
        $state = $request->query('state');

        if (!$code) {
            return redirect('https://pulse.form.iadcsuez.org')->with('linkedin_error', 'LinkedIn authorization was cancelled.');
        }

        try {
            $submissionId = decrypt($state);
        } catch (\Exception $e) {
            return redirect('https://pulse.form.iadcsuez.org')->with('linkedin_error', 'Invalid session state. Please try again.');
        }

        // 1. Exchange code for access token
        $tokenResp = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->callbackUrl(),
            'client_id'     => $this->clientId(),
            'client_secret' => $this->clientSecret(),
        ]);

        if (!$tokenResp->successful()) {
            Log::error('LinkedIn token error', $tokenResp->json());
            return redirect('https://pulse.form.iadcsuez.org')->with('linkedin_error', 'Failed to authenticate with LinkedIn. Please try again.');
        }

        $accessToken = $tokenResp->json('access_token');

        // 2. Get user profile (OpenID Connect)
        $profileResp = Http::withHeaders([
            'Authorization'              => "Bearer {$accessToken}",
            'X-Restli-Protocol-Version' => '2.0.0',
        ])->get('https://api.linkedin.com/v2/userinfo');

        if (!$profileResp->successful()) {
            Log::error('LinkedIn profile error', $profileResp->json());
            return redirect('https://pulse.form.iadcsuez.org')->with('linkedin_error', 'Failed to get LinkedIn profile. Please try again.');
        }

        $sub       = $profileResp->json('sub'); // person ID from OpenID Connect
        $authorUrn = "urn:li:person:{$sub}";

        // 3. Fetch the form submission and find the uploaded image
        $submission    = DynamicFormSubmission::findOrFail($submissionId);
        $orderedFields = $submission->dynamicForm->getOrderedFields();
        $imagePath     = null;

        foreach ($orderedFields as $fieldName => $fieldConfig) {
            if (($fieldConfig['type'] ?? '') === 'file' && !empty($submission->data[$fieldName])) {
                $imagePath = $submission->data[$fieldName];
                break;
            }
        }

        $postText  = "I’m excited to be attending PULSE - Petroleum Upstream Learning & Scientific Exchange.\n\nProud to be part of the first technical event at Suez University, organized by IADC Suez University Student Chapter.\n\nLooking forward to learning, networking, and gaining real industry insights on Tuesday, April 21, 2026 at FPME, Suez University.\n\n🔗 Register here: https://pulse.form.iadcsuez.org\n\n#PULSE \n#IADCSuez\n#ExploreYourPotential";
        $assetUrn  = null;

        // 4. Upload the image to LinkedIn
        if ($imagePath) {
            $imageFullPath = storage_path('app/public/' . $imagePath);

            if (file_exists($imageFullPath)) {
                // Register upload with LinkedIn
                $registerResp = Http::withHeaders([
                    'Authorization'              => "Bearer {$accessToken}",
                    'Content-Type'               => 'application/json',
                    'X-Restli-Protocol-Version' => '2.0.0',
                ])->post('https://api.linkedin.com/v2/assets?action=registerUpload', [
                    'registerUploadRequest' => [
                        'recipes'              => ['urn:li:digitalmediaRecipe:feedshare-image'],
                        'owner'                => $authorUrn,
                        'serviceRelationships' => [[
                            'relationshipType' => 'OWNER',
                            'identifier'       => 'urn:li:userGeneratedContent',
                        ]],
                    ],
                ]);

                if ($registerResp->successful()) {
                    $uploadMechanism = $registerResp->json('value.uploadMechanism');
                    $uploadUrl = $uploadMechanism['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? null;
                    $assetUrn  = $registerResp->json('value.asset');

                    if ($uploadUrl) {
                        // Upload the raw image bytes
                        $imageBytes = file_get_contents($imageFullPath);
                        $mimeType   = mime_content_type($imageFullPath) ?: 'image/jpeg';

                        Http::withHeaders([
                            'Authorization' => "Bearer {$accessToken}",
                            'Content-Type'  => $mimeType,
                        ])->withBody($imageBytes, $mimeType)->put($uploadUrl);
                    }
                } else {
                    Log::error('LinkedIn register upload error', $registerResp->json());
                }
            }
        }

        // 5. Create the LinkedIn post (with or without image)
        $shareContent = [
            'shareCommentary'    => ['text' => $postText],
            'shareMediaCategory' => $assetUrn ? 'IMAGE' : 'NONE',
        ];

        if ($assetUrn) {
            $shareContent['media'] = [[
                'status'      => 'READY',
                'media'       => $assetUrn,
                'title'       => ['text' => 'PULSE ⚡'],
                'description' => ['text' => 'The first technical petroleum upstream event at Suez University, organized by IADC SUSC.'],
            ]];
        }

        $postResp = Http::withHeaders([
            'Authorization'              => "Bearer {$accessToken}",
            'Content-Type'               => 'application/json',
            'X-Restli-Protocol-Version' => '2.0.0',
        ])->post('https://api.linkedin.com/v2/ugcPosts', [
            'author'          => $authorUrn,
            'lifecycleState'  => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => $shareContent,
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ]);

        if ($postResp->successful()) {
            // Redirect to LinkedIn feed to see the newly created post
            return redirect('https://www.linkedin.com/feed/')
                ->with('pulse_shared_success', true);
        }

        Log::error('LinkedIn UGC post error', [
            'status' => $postResp->status(),
            'body'   => $postResp->json(),
        ]);

        return redirect('https://pulse.form.iadcsuez.org')->with('linkedin_error', 'Post was created but LinkedIn returned an error. Check your LinkedIn feed.');
    }
}
