<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\GoogleSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    protected $zoomService;

    public function __construct(\App\Services\ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    public function index()
    {
        $user = Auth::guard('user')->user();
        
        // Get sessions for all committees the user belongs to
        $committeeIds = $user->committees()->pluck('committees.id');
        
        $sessions = \App\Models\GoogleSession::whereIn('committee_id', $committeeIds)
            ->with('committee')
            ->get();
            
        // Format sessions for calendar
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
                    'committeeId' => $session->committee_id,
                    'committeeName' => $session->committee->name ?? 'Unknown Committee',
                    'sessionUrl' => $session->session_url
                ]
            ];
        });
        
        // Check for active meeting (happening right now)
        $activeMeeting = $sessions->filter(function($session) use ($now) {
            return $session->start_time <= $now && $session->end_time >= $now;
        })->first();

        return view('user.sessions.index', compact('sessions', 'calendarEvents', 'activeMeeting'));
    }

    public function join(GoogleSession $googleSession)
    {
        $user = Auth::guard('user')->user();
        \Illuminate\Support\Facades\Log::info('User joining session process started', ['user_id' => $user->id, 'session_id' => $googleSession->id, 'class' => get_class($googleSession)]);
        
        // Check if user has access to this session (member of the committee)
        // If session has no committee assigned, we assume it's accessible to all valid users (or handle as error).
        // Here we allow it if committee_id is null/0, otherwise check specific committee membership.
        if ($googleSession->committee_id) {
            $hasAccess = $user->committees->pluck('id')->contains($googleSession->committee_id);
            
            if (!$hasAccess) {
                abort(403, 'You do not have access to this session.');
            }
        }

        // Check if creator has joined
        // Disabled per user request for immediate join/redirect
        /*
        if (!$googleSession->creator_joined) {
            return view('user.sessions.waiting', compact('googleSession'));
        }
        */

        // Record User Evaluation (5 points for joining meeting)
        $existingEvaluation = \App\Models\UserEvaluation::where('user_id', $user->id)
            ->where('related_type', get_class($googleSession))
            ->where('related_id', $googleSession->id)
            ->where('type', 'joining_meeting')
            ->exists();

        if (!$existingEvaluation) {
            // Use session's committee_id if available, otherwise fallback to user's first committee
            $committeeId = $googleSession->committee_id ?? $user->committees->first()?->id;
            
            \Illuminate\Support\Facades\Log::info('Creating evaluation record', ['user_id' => $user->id, 'session_id' => $googleSession->id]);
            \App\Models\UserEvaluation::create([
                'user_id' => $user->id,
                'committee_id' => $committeeId,
                'type' => 'joining_meeting',
                'score' => 5,
                'max_score' => 5,
                'related_type' => get_class($googleSession),
                'related_id' => $googleSession->id,
            ]);
            \Illuminate\Support\Facades\Log::info('Evaluation record created');
        } else {
             \Illuminate\Support\Facades\Log::info('Evaluation record already exists');
        }

        // For GoogleSession, we just redirect to the URL
        if ($googleSession->session_url) {
            return redirect()->away($googleSession->session_url);
        }
        
        \Illuminate\Support\Facades\Log::warning('Session join failed: No URL', ['session_id' => $googleSession->id]);

        // Fallback or error
        return redirect()->route('user.sessions.index')->with('error', 'Meeting URL not found for this session. Please contact the board.');
    }
    public function evaluate(GoogleSession $googleSession)
    {
        $user = Auth::guard('user')->user();

        // 1. Check if user has joined this session
        $hasJoined = \App\Models\UserEvaluation::where('user_id', $user->id)
            ->where('related_type', get_class($googleSession))
            ->where('related_id', $googleSession->id)
            ->where('type', 'joining_meeting')
            ->exists();

        if (!$hasJoined) {
            return redirect()->route('user.sessions.index')->with('error', 'You must join the session before you can evaluate the instructor.');
        }
        
        // 2. Check if user has already evaluated
        $existingEvaluation = \App\Models\ManagementEvaluation::where('user_id', $user->id)
            ->where('google_session_id', $googleSession->id)
            ->first();
            
        return view('user.sessions.evaluate', compact('googleSession', 'existingEvaluation'));
    }

    public function storeEvaluation(Request $request, GoogleSession $googleSession)
    {
        $user = Auth::guard('user')->user();
        
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'message' => 'nullable|string|max:1000'
        ]);

        \App\Models\ManagementEvaluation::updateOrCreate(
            [
                'user_id' => $user->id,
                'google_session_id' => $googleSession->id,
            ],
            [
                'rating' => $request->rating,
                'message' => $request->message
            ]
        );

        return redirect()->back()->with('success', 'Thank you for your feedback! Your evaluation has been saved.');
    }
}
