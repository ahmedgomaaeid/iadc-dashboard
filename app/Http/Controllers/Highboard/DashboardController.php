<?php

namespace App\Http\Controllers\Highboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Board;
use App\Models\Committee;
use App\Models\GoogleSession;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $highboard = Auth::guard('highboard')->user();

        // Statistics
        $totalUsers = User::whereHas('committees', function($query) use ($highboard) {
            $query->where('field_id', $highboard->field_id);
        })->count();

        $totalBoards = Board::whereHas('committee', function($query) use ($highboard) {
            $query->where('field_id', $highboard->field_id);
        })->count();

        $totalCommittees = Committee::where('field_id', $highboard->field_id)->count();

        // Committees with counts
        $committees = Committee::where('field_id', $highboard->field_id)
            ->withCount(['users', 'boards'])
            ->get();
        
        // Check for active meeting (happening right now)
        $activeMeeting = GoogleSession::whereHas('committee', function($query) use ($highboard) {
                $query->where('field_id', $highboard->field_id);
            })
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->with('committee')
            ->first();

        return view('highboard.dashboard.index', compact(
            'highboard',
            'totalUsers',
            'totalBoards',
            'totalCommittees',
            'committees',
            'activeMeeting'
        ));
    }
}
