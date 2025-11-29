<?php

namespace App\Http\Controllers\Board;

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
        $board = Auth::guard('board')->user();
        // Show sessions for the board member's committee
        $sessions = Session::where('committee_id', $board->committee_id)
            ->orderBy('start_time', 'asc')
            ->get();
        return view('board.sessions.index', compact('sessions'));
    }

    public function create()
    {
        return view('board.sessions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
        ]);

        $board = Auth::guard('board')->user();

        // Check if user has connected Zoom account
        if (!$board->zoom_access_token) {
            return redirect()->route('zoom.oauth')->with('error', 'Please connect your Zoom account first.');
        }

        try {
            $zoomMeeting = $this->zoomService->createMeeting($board, $request->all());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create Zoom meeting: ' . $e->getMessage());
        }

        Session::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'meeting_link' => $zoomMeeting['id'], // Storing Zoom ID in meeting_link for compatibility
            'creator_id' => $board->id,
            'creator_type' => 'App\Models\Board',
            'committee_id' => $board->committee_id,
            'zoom_meeting_id' => $zoomMeeting['id'],
            'zoom_join_url' => $zoomMeeting['join_url'],
            'zoom_start_url' => $zoomMeeting['start_url'],
            'zoom_password' => $zoomMeeting['password'],
        ]);

        return redirect()->route('board.sessions.index')->with('success', 'Session created successfully.');
    }

    public function edit(Session $session)
    {
        if ($session->creator_id !== Auth::guard('board')->id() || $session->creator_type !== 'App\Models\Board') {
            abort(403);
        }
        return view('board.sessions.edit', compact('session'));
    }

    public function update(Request $request, Session $session)
    {
        if ($session->creator_id !== Auth::guard('board')->id() || $session->creator_type !== 'App\Models\Board') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
        ]);

        $session->update($request->all());

        return redirect()->route('board.sessions.index')->with('success', 'Session updated successfully.');
    }

    public function destroy(Session $session)
    {
        if ($session->creator_id !== Auth::guard('board')->id() || $session->creator_type !== 'App\Models\Board') {
            abort(403);
        }

        $session->delete();

        return redirect()->route('board.sessions.index')->with('success', 'Session deleted successfully.');
    }

    public function join(Session $session)
    {
        $board = Auth::guard('board')->user();
        
        // Check if board member has access to this session (same committee)
        if ($session->committee_id !== $board->committee_id) {
            abort(403, 'You do not have access to this session.');
        }

        // Check if this is the creator
        $isCreator = $session->creator_id === $board->id && $session->creator_type === 'App\Models\Board';
        
        // If creator, mark as joined
        if ($isCreator && !$session->creator_joined) {
            $session->update(['creator_joined' => true]);
        }

        return view('board.sessions.join', compact('session', 'isCreator'));
    }
}
