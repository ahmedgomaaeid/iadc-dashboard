<?php

namespace App\Http\Controllers\Highboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoogleSession;
use App\Models\UserEvaluation;
use Illuminate\Support\Facades\Auth;

use Laravel\Socialite\Facades\Socialite;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleSessionController extends Controller
{
    public function index()
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Check if user is connected to Google
        $isConnected = !is_null($highboard->google_access_token);
        
        // Get committees in the highboard member's field
        $committees = \App\Models\Committee::where('field_id', $highboard->field_id)
            ->where('is_active', true)
            ->get();
        
        // Show sessions for all committees in the highboard member's field
        $sessions = GoogleSession::whereHas('committee', function($query) use ($highboard) {
                $query->where('field_id', $highboard->field_id);
            })
            ->get();
            
        $now = now();
        $calendarEvents = $sessions->map(function($session) use ($now) {
            $isActive = $session->start_time <= $now && $session->end_time >= $now;
            
            return [
                'id' => $session->id,
                'title' => $session->title,
                'start' => $session->start_time->toIso8601String(),
                'end' => $session->end_time->toIso8601String(),
                'url' => $session->session_url,
                'className' => $isActive ? 'active-meeting-event' : '',
                'extendedProps' => [
                    'isActive' => $isActive,
                    'committeeId' => $session->committee_id
                ]
            ];
        });
            
        return view('highboard.google_sessions.index', compact('sessions', 'isConnected', 'calendarEvents', 'committees'));
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = Auth::guard('highboard')->user();

            $user->update([
                'google_id' => $googleUser->id,
                'google_access_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
                'google_token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                'google_avatar' => $googleUser->avatar,
            ]);

            return redirect()->route('highboard.sessions.index')->with('success', 'Connected to Google Calendar successfully!');
        } catch (\Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage());
            return redirect()->route('highboard.sessions.index')->with('error', 'Failed to connect to Google Calendar.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'committee_id' => 'required|exists:committees,id',
        ]);

        $user = Auth::guard('highboard')->user();

        if (!$user->google_access_token) {
            return response()->json(['error' => 'Not connected to Google Calendar'], 403);
        }

        // Create Google Calendar Event
        try {
            $client = new GoogleClient();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setAccessToken($user->google_access_token);

            if ($user->google_token_expires_at->isPast()) {
                if ($user->google_refresh_token) {
                    $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                    $newAccessToken = $client->getAccessToken();
                    $user->update([
                        'google_access_token' => $newAccessToken['access_token'],
                        'google_token_expires_at' => now()->addSeconds($newAccessToken['expires_in']),
                    ]);
                } else {
                    return response()->json([
                        'error' => 'Google session expired. Please reconnect.',
                        'requires_auth' => true
                    ], 401);
                }
            }

            $service = new GoogleCalendar($client);
            $event = new GoogleCalendar\Event([
                'summary' => $request->title,
                'description' => 'Session for committee: ' . $request->committee_id,
                'start' => [
                    'dateTime' => Carbon::parse($request->start_time)->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ],
                'end' => [
                    'dateTime' => Carbon::parse($request->end_time)->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ],
                'conferenceData' => [
                    'createRequest' => [
                        'requestId' => uniqid(),
                        'conferenceSolutionKey' => [
                            'type' => 'hangoutsMeet'
                        ]
                    ]
                ]
            ]);

            $calendarId = 'primary';
            $createdEvent = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);

            // Save to local database
            $session = new GoogleSession();
            $session->title = $request->title;
            
            // Get Google Meet link from conferenceData
            $meetLink = null;
            if (isset($createdEvent->conferenceData->entryPoints)) {
                foreach ($createdEvent->conferenceData->entryPoints as $entryPoint) {
                    if ($entryPoint->entryPointType === 'video') {
                        $meetLink = $entryPoint->uri;
                        break;
                    }
                }
            }
            // Fallback to hangoutLink or htmlLink if conferenceData not available
            $session->session_url = $meetLink ?? $createdEvent->hangoutLink ?? $createdEvent->htmlLink;
            
            $session->google_event_id = $createdEvent->id; // Store Google event ID for deletion 
            $session->start_time = $request->start_time;
            $session->end_time = $request->end_time;
            $session->creator_id = $user->id;
            $session->creator_type = get_class($user);
            // Assuming committee_id is needed, let's just make it nullable or handle it. 
            // The prompt says "add meeting to calendar", implying general usage.
            // But the existing code filters by committee.
            // For now, let's assume valid data or just save what we have.
            // Wait, existing index filters by committee.
            // We might need to select a committee in the modal.
            // Let's add committee_id to validation if needed, but for now I'll skip committee assignment validation to keep it simple as per prompt "add meeting to calendar".
            // Actually, better to assign it to a default committee or ask user.
            // Let's hardcode a check or just leave it null if allowed.
            // Looking at GoogleSession model, committee_id is in fillable.
            // I'll add 'committee_id' to validation and request.
             $session->committee_id = $request->committee_id; 
            $session->save();
            
            return response()->json(['message' => 'Event created successfully', 'event' => $session]);

        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            Log::error('Google Calendar Error: ' . $errorMsg);
            
            if (str_contains($errorMsg, 'insufficient permissions') || 
                str_contains($errorMsg, 'insufficient authentication scopes') ||
                str_contains($errorMsg, 'invalid_grant') ||
                str_contains($errorMsg, 'PERMISSION_DENIED') ||
                str_contains($errorMsg, 'unauthorized_client')) {
                return response()->json([
                    'error' => 'Google Calendar permissions are missing or expired. Please reconnect.',
                    'requires_auth' => true
                ], 401);
            }
            
            return response()->json(['error' => 'Failed to create event on Google Calendar: ' . $errorMsg], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::guard('highboard')->user();

        if (!$user->google_access_token) {
            return response()->json(['error' => 'Not connected to Google Calendar'], 403);
        }

        $session = GoogleSession::findOrFail($id);

        // Verify user has permission to delete (creator or same field)
        if ($session->creator_id !== $user->id && $session->committee->field_id !== $user->field_id) {
            return response()->json(['error' => 'Unauthorized to delete this session'], 403);
        }

        // Delete from Google Calendar if event ID exists
        if ($session->google_event_id) {
            try {
                Log::info('Attempting to delete Google Calendar event', [
                    'event_id' => $session->google_event_id,
                    'session_id' => $session->id
                ]);

                $client = new GoogleClient();
                $client->setClientId(config('services.google.client_id'));
                $client->setClientSecret(config('services.google.client_secret'));
                $client->setAccessToken($user->google_access_token);

                // Refresh token if expired
                if ($user->google_token_expires_at->isPast()) {
                    if ($user->google_refresh_token) {
                        $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                        $newAccessToken = $client->getAccessToken();
                        $user->update([
                            'google_access_token' => $newAccessToken['access_token'],
                            'google_token_expires_at' => now()->addSeconds($newAccessToken['expires_in']),
                        ]);
                        Log::info('Google token refreshed for deletion');
                    } else {
                        Log::warning('No refresh token available');
                        return response()->json([
                            'error' => 'Google session expired. Please reconnect.',
                            'requires_auth' => true
                        ], 401);
                    }
                }

                $service = new GoogleCalendar($client);
                $calendarId = 'primary';
                
                // Delete event from Google Calendar
                $service->events->delete($calendarId, $session->google_event_id);
                
                Log::info('Successfully deleted event from Google Calendar', [
                    'event_id' => $session->google_event_id
                ]);

            } catch (\Exception $e) {
                Log::error('Google Calendar Delete Error', [
                    'event_id' => $session->google_event_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue with local deletion even if Google deletion fails
            }
        } else {
            Log::warning('No google_event_id found for session', ['session_id' => $session->id]);
        }

        // Delete from local database
        $session->delete();

        return response()->json(['message' => 'Session deleted successfully']);
    }

    /**
     * Join a session - record highboard member joining and redirect to meeting
     */
    public function join(GoogleSession $googleSession)
    {
        $highboard = Auth::guard('highboard')->user();
        
        Log::info('Highboard joining session', [
            'highboard_id' => $highboard->id,
            'session_id' => $googleSession->id
        ]);

        // Verify highboard has access (session's committee must be in their field)
        $fieldCommittees = \App\Models\Committee::where('field_id', $highboard->field_id)->pluck('id');
        if (!$fieldCommittees->contains($googleSession->committee_id)) {
            Log::warning('Highboard unauthorized to join session', [
                'highboard_field' => $highboard->field_id,
                'session_committee' => $googleSession->committee_id
            ]);
            return redirect()->route('highboard.sessions.index')
                ->with('error', 'You do not have access to this session.');
        }

        // Note: We don't record UserEvaluation for highboard joins since they are managers, not members being evaluated
        // The join is logged above for tracking purposes

        // Return view that opens meeting in new tab and redirects current tab to evaluation
        return view('highboard.sessions.join_redirect', [
            'session' => $googleSession, 
            'redirectUrl' => route('highboard.evaluations.interaction', $googleSession),
            'meetingUrl' => $googleSession->session_url ?? $googleSession->google_meet_link
        ]);

    }
}
