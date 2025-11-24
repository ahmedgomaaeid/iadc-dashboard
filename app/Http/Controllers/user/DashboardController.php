<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $committees = $user->committees;
        
        $selectedCommitteeId = $request->input('committee_id');
        
        $lessonsQuery = Lesson::active()->with('committee');
        $tasksQuery = Task::where('is_active', true)->with('committee');

        if ($selectedCommitteeId) {
            $lessonsQuery->where('committee_id', $selectedCommitteeId);
            $tasksQuery->where('committee_id', $selectedCommitteeId);
        } else {
            $committeeIds = $committees->pluck('id');
            $lessonsQuery->whereIn('committee_id', $committeeIds);
            $tasksQuery->whereIn('committee_id', $committeeIds);
        }

        // Exclude tasks already submitted by the user
        $tasksQuery->whereDoesntHave('submissions', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });

        $recentLessons = $lessonsQuery->latest()->take(5)->get();
        $recentTasks = $tasksQuery->latest()->take(5)->get();

        return view('user.dashboard', compact('committees', 'recentLessons', 'recentTasks', 'selectedCommitteeId'));
    }
}
