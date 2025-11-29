<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZoomService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ZoomController extends Controller
{
    protected $zoomService;

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    public function oauth()
    {
        return redirect($this->zoomService->getOAuthUrl());
    }

    public function callback(Request $request)
    {
        $code = $request->input('code');
        
        try {
            $response = $this->zoomService->handleCallback($code);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Zoom OAuth Error: ' . $e->getMessage());
            return redirect()->route('index')->with('error', 'Zoom connection failed: ' . $e->getMessage());
        }

        if (isset($response['access_token'])) {
            $user = null;
            $redirectRoute = 'index';

            if (Auth::guard('board')->check()) {
                $user = Auth::guard('board')->user();
                $redirectRoute = 'board.sessions.create';
            } elseif (Auth::guard('highboard')->check()) {
                $user = Auth::guard('highboard')->user();
                $redirectRoute = 'highboard.sessions.create';
            }

            if ($user) {
                $user->update([
                    'zoom_access_token' => $response['access_token'],
                    'zoom_refresh_token' => $response['refresh_token'],
                    'zoom_token_expires_at' => Carbon::now()->addSeconds($response['expires_in']),
                ]);

                return redirect()->route($redirectRoute)->with('success', 'Zoom account connected successfully!');
            } else {
                \Illuminate\Support\Facades\Log::error('Zoom OAuth: No authenticated user found in callback.');
                return redirect()->route('index')->with('error', 'Authentication lost during Zoom connection. Please login and try again.');
            }
        }

        \Illuminate\Support\Facades\Log::error('Zoom OAuth: No access token in response.', ['response' => $response]);
        return redirect()->route('index')->with('error', 'Failed to connect Zoom account. No access token received.');
    }
}
