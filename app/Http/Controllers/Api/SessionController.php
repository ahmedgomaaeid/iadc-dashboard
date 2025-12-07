<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Services\ZoomService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    protected $zoomService;

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    /**
     * Get the status of a session.
     */
    public function status(Session $session)
    {
        $latestSession = $session->getLatestContinuation();

        return response()->json([
            'session_id' => $latestSession->id,
            'creator_joined' => $latestSession->creator_joined,
            'zoom_meeting_id' => $latestSession->zoom_meeting_id,
            'is_continuation' => $latestSession->is_continuation,
            'continuation_count' => $latestSession->continuation_count,
            'title' => $latestSession->title,
        ]);
    }

    /**
     * Get the latest continuation of a session.
     */
    public function latest(Session $session)
    {
        $latestSession = $session->getLatestContinuation();

        return response()->json([
            'id' => $latestSession->id,
            'title' => $latestSession->title,
            'creator_joined' => $latestSession->creator_joined,
            'zoom_meeting_id' => $latestSession->zoom_meeting_id,
            'zoom_join_url' => $latestSession->zoom_join_url,
            'zoom_password' => $latestSession->zoom_password,
            'is_continuation' => $latestSession->is_continuation,
            'continuation_count' => $latestSession->continuation_count,
            'is_same' => $latestSession->id === $session->id,
        ]);
    }

    /**
     * Recreate a meeting as a continuation.
     */
    public function recreate(Request $request, Session $session)
    {
        // Determine the authenticated user (board or highboard)
        $user = null;
        $creatorType = null;

        if (Auth::guard('board')->check()) {
            $user = Auth::guard('board')->user();
            $creatorType = 'App\Models\Board';
        } elseif (Auth::guard('highboard')->check()) {
            $user = Auth::guard('highboard')->user();
            $creatorType = 'App\Models\Highboard';
        }

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if user is the creator of the session
        if ($session->creator_id !== $user->id || $session->creator_type !== $creatorType) {
            return response()->json(['error' => 'Only the session creator can recreate the meeting'], 403);
        }

        // Check if user has Zoom tokens
        if (!$user->zoom_access_token) {
            return response()->json(['error' => 'Zoom account not connected'], 400);
        }

        try {
            // Get the root session to calculate continuation count
            $rootSession = $session->getRootSession();
            $continuationCount = $rootSession->continuation_count + 1;

            // Update root session's continuation count
            $rootSession->update(['continuation_count' => $continuationCount]);

            // Create new Zoom meeting
            $meetingData = [
                'title' => $session->title . ' (Part ' . ($continuationCount + 1) . ')',
                'start_time' => Carbon::now(),
            ];

            $zoomMeeting = $this->zoomService->createMeeting($user, $meetingData);

            // Create new session as continuation
            $newSession = Session::create([
                'title' => $meetingData['title'],
                'description' => $session->description,
                'start_time' => Carbon::now(),
                'meeting_link' => $zoomMeeting['id'],
                'creator_id' => $user->id,
                'creator_type' => $creatorType,
                'committee_id' => $session->committee_id,
                'zoom_meeting_id' => $zoomMeeting['id'],
                'zoom_join_url' => $zoomMeeting['join_url'],
                'zoom_start_url' => $zoomMeeting['start_url'],
                'zoom_password' => $zoomMeeting['password'],
                'parent_session_id' => $rootSession->id,
                'is_continuation' => true,
                'continuation_count' => $continuationCount,
                'creator_joined' => false,
            ]);

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $newSession->id,
                    'title' => $newSession->title,
                    'zoom_meeting_id' => $newSession->zoom_meeting_id,
                    'zoom_start_url' => $newSession->zoom_start_url,
                    'zoom_join_url' => $newSession->zoom_join_url,
                    'zoom_password' => $newSession->zoom_password,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create meeting: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mark session as creator joined.
     */
    public function markJoined(Session $session)
    {
        // Determine the authenticated user
        $user = null;
        $creatorType = null;

        if (Auth::guard('board')->check()) {
            $user = Auth::guard('board')->user();
            $creatorType = 'App\Models\Board';
        } elseif (Auth::guard('highboard')->check()) {
            $user = Auth::guard('highboard')->user();
            $creatorType = 'App\Models\Highboard';
        }

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if user is the creator
        if ($session->creator_id !== $user->id || $session->creator_type !== $creatorType) {
            return response()->json(['error' => 'Only the session creator can mark as joined'], 403);
        }

        $session->update(['creator_joined' => true]);

        return response()->json(['success' => true]);
    }
}
