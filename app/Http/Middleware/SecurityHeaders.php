<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip security headers for Zoom session join pages
        // The Zoom Web SDK requires loading cross-origin resources
        $routeName = $request->route() ? $request->route()->getName() : '';
        $isSessionJoinPage = str_contains($routeName, 'sessions.join');
        
        if (!$isSessionJoinPage) {
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
            $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
        }

        return $response;
    }
}
