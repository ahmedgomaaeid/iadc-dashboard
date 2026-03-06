<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\User;
use App\Models\Committee;
use App\Models\Highboard;

class DashboardController extends Controller
{
    public function index()
    {
        $highBoardCount = Highboard::where('is_active', true)->count();
        $boardCount = Board::where('is_active', true)->count();
        $userCount = User::where('is_active', true)->count();
        $committeeCount = Committee::where('is_active', true)->count();
        return view('supervisor.dashboard.index', compact('highBoardCount', 'boardCount', 'userCount', 'committeeCount'));
    }
}
