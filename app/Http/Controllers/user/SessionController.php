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
        // Show sessions for all committees the user belongs to
        $committeeIds = $user->committees()->pluck('committees.id');
        $sessions = Session::whereIn('committee_id', $committeeIds)
            ->orderBy('start_time', 'asc')
            ->get();
        return view('user.sessions.index', compact('sessions'));
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
