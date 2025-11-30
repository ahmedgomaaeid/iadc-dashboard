<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use Carbon\Carbon;

class ZoomService
{
    protected $clientId;
    protected $clientSecret;
    protected $accountId;
    protected $baseUrl = 'https://api.zoom.us/v2';

    public function __construct()
    {
        $this->clientId = env('ZOOM_CLIENT_ID');
        $this->clientSecret = env('ZOOM_CLIENT_SECRET');
        $this->accountId = env('ZOOM_ACCOUNT_ID');
    }

    public function getOAuthUrl()
    {
        $redirectUri = route('zoom.callback');
        return "https://zoom.us/oauth/authorize?response_type=code&client_id={$this->clientId}&redirect_uri={$redirectUri}";
    }

    public function handleCallback($code)
    {
        $redirectUri = route('zoom.callback');

        $response = Http::asForm()->post('https://zoom.us/oauth/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        return $response->json();
    }

    public function refreshAccessToken($user)
    {
        $response = Http::asForm()->post('https://zoom.us/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $user->zoom_refresh_token,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        $data = $response->json();

        if (isset($data['access_token'])) {
            $user->update([
                'zoom_access_token' => $data['access_token'],
                'zoom_refresh_token' => $data['refresh_token'],
                'zoom_token_expires_at' => Carbon::now()->addSeconds($data['expires_in']),
            ]);
            return $data['access_token'];
        }

        return null;
    }

    public function createMeeting($user, $data)
    {
        // Check if token is expired and refresh if needed
        if (Carbon::now()->gte($user->zoom_token_expires_at)) {
            $this->refreshAccessToken($user);
        }

        $response = Http::withToken($user->zoom_access_token)
            ->post("{$this->baseUrl}/users/me/meetings", [
                'topic' => $data['title'],
                'type' => 2, // Scheduled meeting
                'start_time' => Carbon::parse($data['start_time'])->toIso8601String(),
                'duration' => 60, // Default duration, can be adjusted
                'timezone' => 'UTC',
                'password' => substr(md5(uniqid()), 0, 8), // Generate random password
                'settings' => [
                    'join_before_host' => false,
                    'host_video' => true,
                    'participant_video' => true,
                    'mute_upon_entry' => true,
                    'waiting_room' => false,
                    'auto_recording' => 'local', // Auto record to local computer
                ],
            ]);

        return $response->json();
    }

    public function generateSignature($meetingNumber, $role)
    {
        $iat = time() - 30;
        $exp = $iat + 60 * 60 * 2;

        $payload = [
            'sdkKey' => $this->clientId,
            'mn' => $meetingNumber,
            'role' => $role,
            'iat' => $iat,
            'exp' => $exp,
            'appKey' => $this->clientId,
            'tokenExp' => $exp,
        ];

        return JWT::encode($payload, $this->clientSecret, 'HS256');
    }
}
