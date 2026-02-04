<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Controller;
use App\Models\GoogleSession;
use App\Models\User;
use App\Models\UserEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    public function index()
    {
        $board = Auth::guard('board')->user();
        
        // participation stats
        $participationCount = UserEvaluation::where('committee_id', $board->committee_id)
            ->where('type', 'participation')
            ->count();
            
        // recent sessions to evaluate
        $recentSessions = GoogleSession::where('committee_id', $board->committee_id)
            ->latest()
            ->take(5)
            ->get();
            
        return view('board.evaluations.index', compact('recentSessions', 'participationCount'));
    }

    public function interaction(GoogleSession $googleSession)
    {
        // Ensure user has access
        $board = Auth::guard('board')->user();
        if ($googleSession->committee_id !== $board->committee_id) {
            abort(403);
        }

        // Get users who joined this session (have an evaluation of type 'joining_meeting')
        // We can find them by looking at UserEvaluation for this session
        $joinedUserIds = UserEvaluation::where('related_type', get_class($googleSession))
            ->where('related_id', $googleSession->id)
            ->where('type', 'joining_meeting')
            ->pluck('user_id');

        $users = User::whereIn('id', $joinedUserIds)->get();

        // Pass existing interaction evaluations if any
        $evaluations = UserEvaluation::where('related_type', get_class($googleSession))
            ->where('related_id', $googleSession->id)
            ->where('type', 'interaction')
            ->get()
            ->keyBy('user_id');

        $session = $googleSession; // Alias for view compatibility
        return view('board.evaluations.interaction', compact('session', 'users', 'evaluations'));
    }

    public function storeInteraction(Request $request, GoogleSession $googleSession)
    {
        $board = Auth::guard('board')->user();
        if ($googleSession->committee_id !== $board->committee_id) {
            abort(403);
        }

        $data = $request->validate([
            'evaluations' => 'required|array',
            'evaluations.*.user_id' => 'required|exists:users,id',
            'evaluations.*.score' => 'required|numeric|min:1|max:5',
        ]);

        foreach ($data['evaluations'] as $eval) {
            UserEvaluation::updateOrCreate(
                [
                    'user_id' => $eval['user_id'],
                    'related_type' => get_class($googleSession),
                    'related_id' => $googleSession->id,
                    'type' => 'interaction',
                ],
                [
                    'evaluator_type' => get_class($board),
                    'evaluator_id' => $board->id,
                    'committee_id' => $googleSession->committee_id,
                    'score' => $eval['score'],
                    'max_score' => 5,
                ]
            );
        }

        return redirect()->back()->with([
            'success' => 'Evaluations saved successfully.',
            'show_instructor_link_modal' => true
        ]);
    }

    public function participation()
    {
        $board = Auth::guard('board')->user();
        $committeeId = $board->committee_id;
        
        $users = User::whereHas('committees', function($q) use ($committeeId) {
            $q->where('committees.id', $committeeId);
        })->get();
        
        return view('board.evaluations.participation', compact('users'));
    }

    public function getParticipants(GoogleSession $googleSession)
    {
        $board = Auth::guard('board')->user();
        if ($googleSession->committee_id !== $board->committee_id) {
            abort(403);
        }

        // Get users who joined this session
        $joinedUserIds = UserEvaluation::where('related_type', get_class($googleSession))
            ->where('related_id', $googleSession->id)
            ->where('type', 'joining_meeting')
            ->pluck('user_id');

        $users = User::whereIn('id', $joinedUserIds)->get();

        // Get existing evaluations
        $evaluations = UserEvaluation::where('related_type', get_class($googleSession))
            ->where('related_id', $googleSession->id)
            ->where('type', 'interaction')
            ->get()
            ->keyBy('user_id');

        return response()->json([
            'users' => $users->map(function($user) use ($evaluations) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'score' => $evaluations[$user->id]->score ?? 5
                ];
            })
        ]);
    }

    public function storeParticipation(Request $request)
    {
        $board = Auth::guard('board')->user();
        $committeeId = $board->committee_id;

        $data = $request->validate([
            'evaluation_date' => 'required|date',
            'event_name' => 'required|string|max:255',
            'evaluations' => 'required|array',
            'evaluations.*.user_id' => 'required|exists:users,id',
            'evaluations.*.score' => 'required|numeric|min:1|max:10',
        ]);

        foreach ($data['evaluations'] as $eval) {
            UserEvaluation::create([
                'user_id' => $eval['user_id'],
                'evaluator_type' => get_class($board),
                'evaluator_id' => $board->id,
                'committee_id' => $committeeId,
                'type' => 'participation',
                'score' => $eval['score'],
                'max_score' => 10,
                'evaluation_date' => $data['evaluation_date'],
                'event_name' => $data['event_name'],
            ]);
        }

        return redirect()->back()->with('success', 'Participation evaluations saved successfully for "' . $data['event_name'] . '".');
    }
}
