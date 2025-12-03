<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckActiveStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (empty($guards)) {
            $guards = ['web', 'board', 'highboard', 'user'];
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                if (!$user->is_active) {
                    Auth::guard($guard)->logout();
                    
                    // We don't invalidate the session here because it would log out other guards as well.
                    // Auth::guard($guard)->logout() is sufficient to remove the authentication for this specific guard.

                    $route = 'login';
                    if ($guard === 'board') {
                        $route = 'board.login';
                    } elseif ($guard === 'highboard') {
                        $route = 'highboard.login';
                    } elseif ($guard === 'admin') {
                        $route = 'admin.login';
                    }

                    return redirect()->route($route)->with('error', 'Your account is inactive. Please contact the administrator.');
                }
            }
        }

        return $next($request);
    }
}
