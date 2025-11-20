<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HighBoard;
use App\Models\Board;
use App\Models\User;
use App\Models\Committee;

class DashboardController extends Controller
{
    public function index()
    {
        $highBoardCount = HighBoard::where('is_active', true)->count();
        $boardCount = Board::where('is_active', true)->count();
        $userCount = User::where('is_active', true)->count();
        $committeeCount = Committee::where('is_active', true)->count();
        return view('admin.dashboard.index', compact('highBoardCount', 'boardCount', 'userCount', 'committeeCount'));
    }

}
