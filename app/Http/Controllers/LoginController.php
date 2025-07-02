<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        \Log::info('Login attempt', ['data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by username or email
        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username/email atau password salah'
            ], 401);
        }

        // Create session token
        $sessionToken = Str::random(60);
        $expiresAt = $request->remember_me ? now()->addDays(30) : now()->addHours(24);

        \Log::info('Creating session', [
            'user_id' => $user->id,
            'session_token_hash' => hash('sha256', $sessionToken),
            'expires_at' => $expiresAt
        ]);

        // Save session to database
        $session = UserSession::create([
            'user_id' => $user->id,
            'session_token' => hash('sha256', $sessionToken),
            'expires_at' => $expiresAt,
        ]);

        \Log::info('Session created', ['session_id' => $session->id]);

        // Fix: Calculate minutes properly
        $cookieMinutes = $request->remember_me ? (30 * 24 * 60) : (24 * 60); // 30 days or 24 hours in minutes

        // Set cookie with correct parameters
        $cookie = cookie(
            'session_token',
            $sessionToken,
            $cookieMinutes, // Use calculated minutes, not diff
            '/', // path
            null, // domain (null = current domain)
            false, // secure (false for localhost)
            true // httpOnly
        );

        \Log::info('Cookie being set', [
            'name' => 'session_token',
            'value' => substr($sessionToken, 0, 10) . '...', // partial token for security
            'expires_minutes' => $cookieMinutes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil! Selamat datang kembali, ' . $user->display_name,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'display_name' => $user->display_name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'role' => $user->role,
            ]
        ])->withCookie($cookie);
    }

    /**
     * Handle register request
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:password',
            'terms' => 'accepted',
        ], [
            'username.unique' => 'Username sudah digunakan',
            'email.unique' => 'Email sudah digunakan',
            'password.min' => 'Password minimal 6 karakter',
            'confirm_password.same' => 'Konfirmasi password tidak cocok',
            'terms.accepted' => 'Anda harus menyetujui syarat dan ketentuan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create user
        $user = User::create([
            'display_name' => $request->fullname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'email_notifications' => true,
        ]);

        // Create session token for auto-login after registration
        $sessionToken = Str::random(60);
        $expiresAt = now()->addHours(24);

        UserSession::create([
            'user_id' => $user->id,
            'session_token' => hash('sha256', $sessionToken),
            'expires_at' => $expiresAt,
        ]);

        // Set cookie
        $cookie = cookie('session_token', $sessionToken, $expiresAt->diffInMinutes(now()), '/', null, false, true);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil! Selamat datang, ' . $user->display_name,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'display_name' => $user->display_name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'role' => $user->role,
            ]
        ])->withCookie($cookie);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $sessionToken = $request->cookie('session_token');

        if ($sessionToken) {
            // Delete session from database
            UserSession::where('session_token', hash('sha256', $sessionToken))->delete();
        }

        // Clear cookie properly
        $cookie = cookie(
            'session_token',
            '',
            -1, // Negative value to expire immediately
            '/',
            null,
            false,
            true
        );

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ])->withCookie($cookie);
    }

    /**
     * Get current user data
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'display_name' => $user->display_name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'role' => $user->role,
                'bio' => $user->bio,
                'followers_count' => $user->follower_count,
                'following_count' => $user->following_count,
            ]
        ]);
    }

    /**
     * Check if user is authenticated
     */
    public function checkAuth(Request $request)
    {
        $sessionToken = $request->cookie('session_token');

        \Log::info('Auth check', [
            'has_session_token' => $sessionToken ? 'yes' : 'no',
            'session_token_preview' => $sessionToken ? substr($sessionToken, 0, 10) . '...' : null,
            'all_cookies' => $request->cookies->all()
        ]);

        if (!$sessionToken) {
            return response()->json([
                'authenticated' => false,
                'user' => null,
                'debug' => 'No session token found'
            ]);
        }

        // Find active session
        $session = UserSession::with('user')
            ->where('session_token', hash('sha256', $sessionToken))
            ->where('expires_at', '>', now())
            ->first();

        \Log::info('Session lookup', [
            'session_found' => $session ? 'yes' : 'no',
            'session_id' => $session ? $session->id : null,
            'user_id' => $session && $session->user ? $session->user->id : null
        ]);

        if (!$session || !$session->user) {
            return response()->json([
                'authenticated' => false,
                'user' => null,
                'debug' => 'Session not found or expired'
            ]);
        }

        $user = $session->user;

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'display_name' => $user->display_name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'role' => $user->role,
            ]
        ]);
    }
}