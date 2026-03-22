<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoogleSession;
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
        $board = Auth::guard('board')->user();
        
        // Check if user is connected to Google
        $isConnected = !is_null($board->google_access_token);
        
        // Get committee for the board member
        // Assuming Board belongs to a committee, pass it as a singleton collection or just use it
        $committees = \App\Models\Committee::where('id', $board->committee_id)->get();
        
        // Show sessions for the board member's committee
        $sessions = GoogleSession::where('committee_id', $board->committee_id)->get();
            
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
            
        return view('board.google_sessions.index', compact('sessions', 'isConnected', 'calendarEvents', 'committees'));
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
            $user = Auth::guard('board')->user();

            $user->update([
                'google_id' => $googleUser->id,
                'google_access_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
                'google_token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                'google_avatar' => $googleUser->avatar,
            ]);

            return redirect()->route('board.sessions.index')->with('success', 'Connected to Google Calendar successfully!');
        } catch (\Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage());
            return redirect()->route('board.sessions.index')->with('error', 'Failed to connect to Google Calendar.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $user = Auth::guard('board')->user();

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
                'description' => 'Session for committee: ' . $user->committee_id,
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
            
            // Validate that we have a meeting URL before saving
            if (empty($session->session_url)) {
                Log::error('No meeting URL generated by Google Calendar', [
                    'event_id' => $createdEvent->id,
                    'event_data' => json_encode($createdEvent)
                ]);
                return response()->json(['error' => 'Failed to generate Google Meet link. Please try again.'], 500);
            }
            
            $session->google_event_id = $createdEvent->id; 
            $session->start_time = $request->start_time;
            $session->end_time = $request->end_time;
            $session->creator_id = $user->id;
            $session->creator_type = get_class($user);
            $session->committee_id = $user->committee_id; 
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
        $user = Auth::guard('board')->user();

        if (!$user->google_access_token) {
            return response()->json(['error' => 'Not connected to Google Calendar'], 403);
        }

        $session = GoogleSession::findOrFail($id);

        // Prevent deleting sessions created by Highboard
        if ($session->creator_type === 'App\\Models\\Highboard') {
            return response()->json(['error' => 'You cannot delete sessions created by Highboard members.'], 403);
        }

        // Allow deletion if strict ownership or committee match (for peer board members)
        if ($session->committee_id !== $user->committee_id) {
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
            }
        } else {
            Log::warning('No google_event_id found for session', ['session_id' => $session->id]);
        }

        // Delete from local database
        $session->delete();

        return response()->json(['message' => 'Session deleted successfully']);
    }
    public function join(GoogleSession $googleSession)
    {
        $board = Auth::guard('board')->user();
        
        // Check if board member has access to this session (same committee)
        if ($googleSession->committee_id !== $board->committee_id) {
            abort(403, 'You do not have access to this session.');
        }

        // Check if this is the creator
        $isCreator = $googleSession->creator_id === $board->id && $googleSession->creator_type === get_class($board);
        
        // If creator, mark as joined (if column exists, GoogleSession might not have it)
        // Checking GoogleSession model... it extends Model. 
        // Assuming it might NOT have creator_joined. If not, I should add it or skip.
        // Let's check GoogleSession model first.
        
        // Record Management Evaluation (5 points for joining)
        // Record Management Evaluation (5 points for joining)
        // Disabled per user request
        /*
        \App\Models\ManagementEvaluation::firstOrCreate(
            [
                'user_type' => get_class($board),
                'user_id' => $board->id,
                'committee_id' => $googleSession->committee_id,
                'type' => 'joining_meeting',
                'related_type' => get_class($googleSession),
                'related_id' => $googleSession->id,
            ],
            [
                'score' => 5,
            ]
        );
        */

        // Redirect current tab to interaction evaluation page
        // Open Zoom link in new tab provided by view
        
        return view('board.sessions.join_redirect', [
            'session' => $googleSession, 
            'redirectUrl' => route('board.evaluations.interaction', $googleSession),
            'meetingUrl' => $googleSession->session_url ?? $googleSession->google_meet_link // fallbacks
        ]);
    }
}
