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

        // Skip security headers for pages that require cross-origin resources
        // - Zoom session join pages (Zoom Web SDK)
        // - Lesson show pages (YouTube embeds)
        $routeName = $request->route() ? $request->route()->getName() : '';
        $isSessionJoinPage = str_contains($routeName, 'sessions.join');
        $isLessonShowPage = str_contains($routeName, 'lessons.show');
        
        if (!$isSessionJoinPage && !$isLessonShowPage) {
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
            $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
        }

        return $response;
    }
}
