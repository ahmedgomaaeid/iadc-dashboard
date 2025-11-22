<?php

namespace App\Http\Controllers\board;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        
        // Get recent members (last 5)
        $recentMembers = User::whereHas('committees', function ($query) use ($board) {
            $query->where('committees.id', $board->committee_id);
        })
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
        
        return view('board.dashboard.index', compact(
            'board',
            'totalMembers',
            'activeMembers',
            'inactiveMembers',
            'recentMembers'
        ));
    }
}
