<?php

namespace App\Http\Controllers\Highboard;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    protected $zoomService;

    public function __construct(\App\Services\ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    public function index()
    {
        $highboard = Auth::guard('highboard')->user();
        // Show sessions for all committees in the highboard member's field
        $sessions = Session::whereHas('committee', function($query) use ($highboard) {
                $query->where('field_id', $highboard->field_id);
            })
            ->orderBy('start_time', 'asc')
            ->get();
        return view('highboard.sessions.index', compact('sessions'));
    }

    public function create()
    {
        $highboard = Auth::guard('highboard')->user();
        // Get all committees in the highboard member's field
        $committees = \App\Models\Committee::where('field_id', $highboard->field_id)
            ->where('is_active', true)
            ->get();
        return view('highboard.sessions.create', compact('committees'));
    }

    public function store(Request $request)
    {
        $highboard = Auth::guard('highboard')->user();

        $request->validate([
            'title' => 'required|string|max:255',
            'committee_id' => 'required|exists:committees,id',
            'start_time' => 'required|date',
        ]);

        // Verify the committee belongs to the highboard member's field
        $committee = \App\Models\Committee::findOrFail($request->committee_id);
        if ($committee->field_id !== $highboard->field_id) {
            abort(403, 'You can only create sessions for committees in your field.');
        }

        // Check if user has connected Zoom account
        if (!$highboard->zoom_access_token) {
            return redirect()->route('zoom.oauth')->with('error', 'Please connect your Zoom account first.');
        }

        try {
            $zoomMeeting = $this->zoomService->createMeeting($highboard, $request->all());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create Zoom meeting: ' . $e->getMessage());
        }

        Session::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'meeting_link' => $zoomMeeting['id'],
            'creator_id' => $highboard->id,
            'creator_type' => 'App\Models\Highboard',
            'committee_id' => $request->committee_id,
            'zoom_meeting_id' => $zoomMeeting['id'],
            'zoom_join_url' => $zoomMeeting['join_url'],
            'zoom_start_url' => $zoomMeeting['start_url'],
            'zoom_password' => $zoomMeeting['password'],
        ]);

        return redirect()->route('highboard.sessions.index')->with('success', 'Session created successfully.');
    }

    public function edit(Session $session)
    {
        if ($session->creator_id !== Auth::guard('highboard')->id() || $session->creator_type !== 'App\Models\Highboard') {
            abort(403);
        }
        return view('highboard.sessions.edit', compact('session'));
    }

    public function update(Request $request, Session $session)
    {
        if ($session->creator_id !== Auth::guard('highboard')->id() || $session->creator_type !== 'App\Models\Highboard') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
        ]);

        $session->update($request->all());

        return redirect()->route('highboard.sessions.index')->with('success', 'Session updated successfully.');
    }

    public function destroy(Session $session)
    {
        if ($session->creator_id !== Auth::guard('highboard')->id() || $session->creator_type !== 'App\Models\Highboard') {
            abort(403);
        }

        $session->delete();

        return redirect()->route('highboard.sessions.index')->with('success', 'Session deleted successfully.');
    }

    public function join(Session $session)
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Check if highboard member has access to this session (same field)
        if ($session->committee && $session->committee->field_id !== $highboard->field_id) {
            abort(403, 'You do not have access to this session.');
        }

        // Check if this is the creator
        $isCreator = $session->creator_id === $highboard->id && $session->creator_type === 'App\Models\Highboard';
        
        // If creator, mark as joined
        if ($isCreator && !$session->creator_joined) {
            $session->update(['creator_joined' => true]);
        }

        return view('highboard.sessions.join', compact('session', 'isCreator'));
    }
}
