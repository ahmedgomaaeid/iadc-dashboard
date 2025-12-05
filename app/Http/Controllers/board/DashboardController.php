<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TaskSubmission;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the board dashboard.
     */
    public function index()
    {
        $board = Auth::guard('board')->user();
        
        // Get total members in this board's committee
        $totalMembers = User::whereHas('committees', function ($query) use ($board) {
            $query->where('committees.id', $board->committee_id);
        })->count();
        
        // Get active members count
        $activeMembers = User::whereHas('committees', function ($query) use ($board) {
            $query->where('committees.id', $board->committee_id);
        })->where('is_active', true)->count();
        
        // Get inactive members count
        $inactiveMembers = $totalMembers - $activeMembers;
        
        // get id of my committee tasks and get submited unreived submitted tasks
        $tasks = Task::where('committee_id', $board->committee_id)->pluck('id');
        $submissions = TaskSubmission::whereIn('task_id', $tasks)->where('status', 'pending')->latest()->take(5)->get();
        
        
        return view('board.dashboard.index', compact(
            'board',
            'totalMembers',
            'activeMembers',
            'inactiveMembers',
            'submissions'
        ));
    }
}
