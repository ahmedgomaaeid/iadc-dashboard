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
        $response = $this->zoomService->handleCallback($code);

        if (isset($response['access_token'])) {
            $user = null;
            if (Auth::guard('board')->check()) {
                $user = Auth::guard('board')->user();
            } elseif (Auth::guard('highboard')->check()) {
                $user = Auth::guard('highboard')->user();
            }

            if ($user) {
                $user->update([
                    'zoom_access_token' => $response['access_token'],
                    'zoom_refresh_token' => $response['refresh_token'],
                    'zoom_token_expires_at' => Carbon::now()->addSeconds($response['expires_in']),
                ]);

                return redirect()->back()->with('success', 'Zoom account connected successfully!');
            }
        }

        return redirect()->route('home')->with('error', 'Failed to connect Zoom account.');
    }
}
