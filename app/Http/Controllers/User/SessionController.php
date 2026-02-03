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
        $hasAccess = $user->committees()->where('committees.id', $session->committee_id)->exists();
        if (!$hasAccess) {
            abort(403, 'You do not have access to this session.');
        }

        // Check if creator has joined
        if (!$session->creator_joined) {
            return view('user.sessions.waiting', compact('session'));
        }

        // Generate Zoom Signature
        $signature = $this->zoomService->generateSignature($session->zoom_meeting_id, 0); // Role 0 for participant

        return view('user.sessions.join', compact('session', 'signature', 'user'));
    }
}
