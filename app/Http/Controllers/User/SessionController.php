<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Session;
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

    public function join(Session $session)
    {
        $user = Auth::guard('user')->user();
        
        // Check if user has access to this session (member of the committee)
        // If session has no committee assigned, we assume it's accessible to all valid users (or handle as error).
        // Here we allow it if committee_id is null/0, otherwise check specific committee membership.
        if ($session->committee_id) {
            $hasAccess = $user->committees->pluck('id')->contains($session->committee_id);
            
            if (!$hasAccess) {
                abort(403, 'You do not have access to this session.');
            }
        }

        // Check if creator has joined
        // Disabled per user request for immediate join/redirect
        /*
        if (!$session->creator_joined) {
            return view('user.sessions.waiting', compact('session'));
        }
        */

        // Record User Evaluation (5 points for joining meeting)
        $existingEvaluation = \App\Models\UserEvaluation::where('user_id', $user->id)
            ->where('related_type', get_class($session))
            ->where('related_id', $session->id)
            ->where('type', 'joining_meeting')
            ->exists();

        if (!$existingEvaluation) {
            // Use session's committee_id if available, otherwise fallback to user's first committee
            $committeeId = $session->committee_id ?? $user->committees->first()?->id;
            
            \App\Models\UserEvaluation::create([
                'user_id' => $user->id,
                'committee_id' => $committeeId,
                'type' => 'joining_meeting',
                'score' => 5,
                'max_score' => 5,
                'related_type' => get_class($session),
                'related_id' => $session->id,
            ]);
        }

        // For GoogleSession, we just redirect to the URL
        if ($session->session_url) {
            return redirect()->away($session->session_url);
        }
        
        \Illuminate\Support\Facades\Log::warning('Session join failed: No URL', ['session_id' => $session->id]);

        // Fallback or error
        return redirect()->route('user.sessions.index')->with('error', 'Meeting URL not found for this session. Please contact the board.');
    }
}
