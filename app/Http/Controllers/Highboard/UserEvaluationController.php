<?php

namespace App\Http\Controllers\Highboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Committee;
use App\Models\UserEvaluation;
use App\Models\CommitteeQuizStat;
use App\Models\GoogleSession; // Using GoogleSession for meetings
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;
        
        // Get committees for filter
        $committees = Committee::where('field_id', $fieldId)->get();
        
        // Set default committee if not provided, or use request
        $committeeId = $request->input('committee_id');
        
        // Start query
        $query = User::whereHas('committees', function($q) use ($fieldId) {
            $q->where('field_id', $fieldId);
        })->with('committees');

        if ($committeeId) {
            $query->whereHas('committees', function($q) use ($committeeId) {
                $q->where('committees.id', $committeeId);
            });
        }
        
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->paginate(20)->withQueryString();
        
        // Calculate Scores for each user
        // We will process this for the current page of users
        // Note: Ideally this should be optimized with subqueries or a separate stats table if data is large.
        // For now, we calculate on the fly as requested.
        
        // Pre-fetch constants for committees involved to avoid N+1 queries per user row if possible
        // But users can be in different committees.
        // Let's assume we calculate based on the Filtered Committee if present, OR their main committee?
        // Requirement: "show table with user name ... and committee with his percentage"
        // If user is in multiple committees, "his name will show for every committee".
        // This implies rows are User-Committee pairs?
        // "if user join multible committe his name will show for every committee" -> unique row per user-committee combination.
        
        // Refactoring strategy:
        // Instead of querying Users, we should query CommitteeMember? (User-Committee pivot)
        // Or simply: If committee_id is selected, we show users in that committee.
        // If NO committee is selected, we show all users in the Field.
        // If a user is in Committee A and Committee B (both in Field), how to show?
        // "his name will show for every committee" -> This suggests the view should ideally list items as (User, Committee).
        
        // New Approach:
        // Get all committees in the field.
        // For each committee, get users.
        // Flatten list? Pagination becomes hard.
        
        // Easier approach:
        // Query Users.
        // In the view, loop through user->committees (that are in the highboard's field).
        // If committee filter is applied, only show that committee row.
        
        // Let's prepare a data structure: $rows = []
        // But with pagination on Users, we might get partial pages if we expand rows.
        // Let's stick to: Paginate Users.
        // In the view, iterate user committees.
        
        // We need to calculate stats per Committee.
        $committeeStats = [];
        foreach ($committees as $committee) {
            $cId = $committee->id;
            
            // 1. Meetings Denominator: Num sessions * 10
            $totalSessions = GoogleSession::where('committee_id', $cId)->count();
            $maxMeetingScore = $totalSessions * 10;
            
            // 2. Tasks Denominator: Num tasks * 10
            $totalTasks = Task::where('committee_id', $cId)->count();
            $maxTaskScore = $totalTasks * 10;
            
            // 3. Quiz Denominator: Total Questions
            $quizStat = CommitteeQuizStat::where('committee_id', $cId)->first();
            $maxQuizScore = $quizStat ? $quizStat->total_questions : 0;
            
            $committeeStats[$cId] = [
                'max_meeting_score' => $maxMeetingScore,
                'max_task_score' => $maxTaskScore,
                'max_quiz_score' => $maxQuizScore, // This is total questions
            ];
        }
        
        return view('highboard.users.evaluations.index', compact('users', 'committees', 'committeeStats', 'committeeId'));
    }
}
