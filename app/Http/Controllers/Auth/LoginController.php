<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // User Login
    public function userLogin()
    {
        return view('user.auth.login');
    }

    public function userAuthenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('user')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Board Login
    public function boardLogin()
    {
        return view('board.auth.login');
    }

    public function boardAuthenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('board')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/board');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Highboard Login
    public function highboardLogin()
    {
        return view('highboard.auth.login');
    }

    public function highboardAuthenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('highboard')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/highboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Admin Login
    public function adminLogin()
    {
        return view('admin.auth.login');
    }

    public function adminAuthenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/admin');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Logout
    public function logout(Request $request)
    {
        if (Auth::guard('user')->check()) {
            Auth::guard('user')->logout();
        }

        return redirect('/');
    }

    public function boardlogout(Request $request)
    {
        if (Auth::guard('board')->check()) {
            Auth::guard('board')->logout();
        }
        if(Auth::guard('user')->check()) {
            Auth::guard('user')->logout();
        }

        return redirect('/board');
    }

    public function highboardlogout(Request $request)
    {
        if (Auth::guard('highboard')->check()) {
            Auth::guard('highboard')->logout();
        }

        if(Auth::guard('user')->check()) {
            Auth::guard('user')->logout();
        }

        if(Auth::guard('board')->check()) {
            Auth::guard('board')->logout();
        }
        return redirect('/highboard');
    }

    public function adminlogout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        if(Auth::guard('user')->check()) {
            Auth::guard('user')->logout();
        }

        if(Auth::guard('board')->check()) {
            Auth::guard('board')->logout();
        }

        if(Auth::guard('highboard')->check()) {
            Auth::guard('highboard')->logout();
        }

        return redirect('/admin');
    }

    // Admin Impersonation - Login as Highboard
    public function loginAsHighboard(Request $request, $id)
    {
        // Verify admin is authenticated
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        // Find the highboard member
        $highboard = \App\Models\Highboard::findOrFail($id);

        // Login as highboard member
        Auth::guard('highboard')->login($highboard);

        return redirect()->route('highboard.dashboard')
            ->with('success', 'You are now logged in as ' . $highboard->name);
    }

    // Admin Impersonation - Login as Board
    public function loginAsBoard(Request $request, $id)
    {
        // Verify admin is authenticated
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        // Find the board member
        $board = \App\Models\Board::findOrFail($id);

        // Login as board member
        Auth::guard('board')->login($board);

        return redirect()->route('board.dashboard')
            ->with('success', 'You are now logged in as ' . $board->name);
    }
}
