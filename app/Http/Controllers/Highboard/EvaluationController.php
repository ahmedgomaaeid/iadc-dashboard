<?php

namespace App\Http\Controllers\Highboard;

use App\Http\Controllers\Controller;
use App\Models\GoogleSession;
use App\Models\User;
use App\Models\UserEvaluation;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Get the field ID for the current highboard user
     */
    private function getFieldId()
    {
        return Auth::guard('highboard')->user()->field_id;
    }

    /**
     * Get all committee IDs for the current highboard's field
     */
    private function getCommitteeIds()
    {
        return Committee::where('field_id', $this->getFieldId())->pluck('id');
    }

    public function index()
    {
        $committeeIds = $this->getCommitteeIds();
        
        // participation stats
        $participationCount = UserEvaluation::whereIn('committee_id', $committeeIds)
            ->where('type', 'participation')
            ->count();
            
        // recent sessions to evaluate
        $recentSessions = GoogleSession::whereIn('committee_id', $committeeIds)
            ->with('committee')
            ->latest()
            ->take(10)
            ->get();
            
        return view('highboard.evaluations.index', compact('recentSessions', 'participationCount'));
    }

    public function interaction(GoogleSession $googleSession)
    {
        $committeeIds = $this->getCommitteeIds();
        
        // Ensure highboard has access to this session's committee
        if (!$committeeIds->contains($googleSession->committee_id)) {
            abort(403);
        }

        // Get users who joined this session (have an evaluation of type 'joining_meeting')
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

        $session = $googleSession;
        return view('highboard.evaluations.interaction', compact('session', 'users', 'evaluations'));
    }

    public function storeInteraction(Request $request, GoogleSession $googleSession)
    {
        $highboard = Auth::guard('highboard')->user();
        $committeeIds = $this->getCommitteeIds();
        
        if (!$committeeIds->contains($googleSession->committee_id)) {
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
                    'evaluator_type' => get_class($highboard),
                    'evaluator_id' => $highboard->id,
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

    public function participation(Request $request)
    {
        $committeeIds = $this->getCommitteeIds();
        $committees = Committee::whereIn('id', $committeeIds)->get();
        
        // Get selected committee or default to first
        $selectedCommitteeId = $request->get('committee_id', $committees->first()?->id);
        
        $users = collect();
        if ($selectedCommitteeId) {
            $users = User::whereHas('committees', function($q) use ($selectedCommitteeId) {
                $q->where('committees.id', $selectedCommitteeId);
            })->get();
        }
        
        return view('highboard.evaluations.participation', compact('users', 'committees', 'selectedCommitteeId'));
    }

    public function getParticipants(GoogleSession $googleSession)
    {
        $committeeIds = $this->getCommitteeIds();
        
        if (!$committeeIds->contains($googleSession->committee_id)) {
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
        $highboard = Auth::guard('highboard')->user();
        $committeeIds = $this->getCommitteeIds();

        $data = $request->validate([
            'committee_id' => 'required|exists:committees,id',
            'evaluation_date' => 'required|date',
            'event_name' => 'required|string|max:255',
            'evaluations' => 'required|array',
            'evaluations.*.user_id' => 'required|exists:users,id',
            'evaluations.*.score' => 'required|numeric|min:1|max:10',
        ]);

        // Verify highboard has access to this committee
        if (!$committeeIds->contains($data['committee_id'])) {
            abort(403);
        }

        foreach ($data['evaluations'] as $eval) {
            UserEvaluation::create([
                'user_id' => $eval['user_id'],
                'evaluator_type' => get_class($highboard),
                'evaluator_id' => $highboard->id,
                'committee_id' => $data['committee_id'],
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
