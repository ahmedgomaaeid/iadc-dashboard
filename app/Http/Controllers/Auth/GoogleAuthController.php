<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    /**
     * Handle Google Callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Determine which user is authenticated and update them
            if (Auth::guard('highboard')->check()) {
                $user = Auth::guard('highboard')->user();
                $redirectTo = 'highboard.sessions.index';
            } elseif (Auth::guard('board')->check()) {
                $user = Auth::guard('board')->user();
                $redirectTo = 'board.sessions.index';
            } else {
                return redirect()->route('login')->with('error', 'Please login before connecting Google Calendar.');
            }

            // Update user tokens
            $user->update([
                'google_id' => $googleUser->id,
                'google_access_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
                'google_token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                'google_avatar' => $googleUser->avatar,
            ]);

            return redirect()->route($redirectTo)->with('success', 'Connected to Google Calendar successfully!');

        } catch (\Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage());
            
            // Determine redirect back in case of error
            if (Auth::guard('highboard')->check()) {
                return redirect()->route('highboard.sessions.index')->with('error', 'Failed to connect to Google Calendar.');
            } elseif (Auth::guard('board')->check()) {
                return redirect()->route('board.sessions.index')->with('error', 'Failed to connect to Google Calendar.');
            }
            
            return redirect()->route('login')->with('error', 'Google authentication failed.');
        }
    }
}
