<?php

namespace App\Http\Controllers\highboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Board;
use App\Models\Committee;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Statistics
        $totalUsers = User::where('field_id', $fieldId)->where('is_active', true)->count();
        $totalBoards = Board::where('field_id', $fieldId)->where('is_active', true)->count();
        $totalCommittees = Committee::where('field_id', $fieldId)->where('is_active', true)->count();

        // Committees with member counts
        $committees = Committee::where('field_id', $fieldId)
            ->withCount(['users' => function($query) {
                $query->where('is_active', true);
            }])
            ->withCount(['boards' => function($query) {
                $query->where('is_active', true);
            }])
            ->get();

        return view('highboard.dashboard.index', compact(
            'highboard',
            'totalUsers',
            'totalBoards',
            'totalCommittees',
            'committees'
        ));
    }
}
