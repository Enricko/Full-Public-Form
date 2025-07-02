<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $sessionToken = $request->cookie('session_token');

        \Log::info('AuthMiddleware: Checking authentication', [
            'url' => $request->url(),
            'has_session_token' => $sessionToken ? 'yes' : 'no',
            'is_ajax' => $request->ajax() ? 'yes' : 'no'
        ]);

        if (!$sessionToken) {
            \Log::info('AuthMiddleware: No session token found');
            return $this->unauthorized($request);
        }

        // Find active session
        $session = UserSession::with('user')
            ->where('session_token', hash('sha256', $sessionToken))
            ->where('expires_at', '>', now())
            ->first();

        \Log::info('AuthMiddleware: Session lookup', [
            'session_found' => $session ? 'yes' : 'no',
            'user_found' => $session && $session->user ? 'yes' : 'no'
        ]);

        if (!$session || !$session->user) {
            // Clean up expired session
            if ($sessionToken) {
                UserSession::where('session_token', hash('sha256', $sessionToken))->delete();
            }
            \Log::info('AuthMiddleware: Session not found or expired');
            return $this->unauthorized($request);
        }

        // Extend session if it's close to expiring (less than 2 hours left)
        if ($session->expires_at->diffInHours(now()) < 2) {
            $session->extend();
            \Log::info('AuthMiddleware: Session extended');
        }

        // THIS IS THE KEY FIX: Set the user in Laravel's auth system
        Auth::setUser($session->user);
        
        // Also set user resolver for request
        $request->setUserResolver(function () use ($session) {
            return $session->user;
        });

        \Log::info('AuthMiddleware: Authentication successful', [
            'user_id' => $session->user->id,
            'username' => $session->user->username,
            'auth_check' => Auth::check() // This should now be true
        ]);

        return $next($request);
    }

    private function unauthorized(Request $request)
    {
        // Always return JSON for AJAX requests or API endpoints
        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'authenticated' => false,
                'message' => 'Unauthorized. Please login first.',
                'redirect' => route('home')
            ], 401);
        }

        // For regular web requests, redirect to home with error message
        return redirect()->route('home')->with('error', 'Silakan login terlebih dahulu');
    }
}