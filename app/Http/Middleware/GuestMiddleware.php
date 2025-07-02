<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;

class GuestMiddleware
{
    /**
     * Handle an incoming request.
     * Redirect authenticated users away from guest-only pages
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $sessionToken = $request->cookie('session_token');

        if ($sessionToken) {
            // Check if session is valid
            $session = UserSession::where('session_token', hash('sha256', $sessionToken))
                ->where('expires_at', '>', now())
                ->first();

            if ($session) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Already authenticated',
                        'redirect' => route('home')
                    ], 302);
                }

                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}