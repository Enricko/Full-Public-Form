<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Display the settings page
     */
    public function index(Request $request)
    {
        // Check authentication using existing system
        $authData = $this->checkAuthentication($request);
        
        if (!$authData['authenticated'] || !$authData['user']) {
            return redirect()->route('login')->with('error', 'Please log in to access settings');
        }

        $currentUser = $authData['user'];

        return view('pages.settings', compact('currentUser'));
    }

    /**
     * Update email notifications setting
     */
    public function updateEmailNotifications(Request $request)
    {
        // Check authentication
        $authData = $this->checkAuthentication($request);
        
        if (!$authData['authenticated'] || !$authData['user']) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to update settings'
            ], 401);
        }

        $request->validate([
            'email_notifications' => 'required|boolean'
        ]);

        $user = $authData['user'];
        
        $user->update([
            'email_notifications' => $request->email_notifications
        ]);

        // Clear user cache
        Cache::forget('user_stats_' . $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Email notification settings updated successfully',
            'email_notifications' => $user->email_notifications
        ]);
    }

    /**
     * Update user's email address
     */
    public function updateEmail(Request $request)
    {
        try {
            // Check authentication
            $authData = $this->checkAuthentication($request);
            
            if (!$authData['authenticated'] || !$authData['user']) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to update your email'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'new_email' => 'required|email|max:255|unique:users,email,' . $authData['user']->id,
                'current_password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $authData['user'];

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            // Update email
            $user->update([
                'email' => $request->new_email
            ]);

            Log::info('User email updated', [
                'user_id' => $user->id,
                'old_email' => $user->getOriginal('email'),
                'new_email' => $request->new_email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email address updated successfully',
                'new_email' => $user->email
            ]);

        } catch (\Exception $e) {
            Log::error('Email update error: ' . $e->getMessage(), [
                'user_id' => $authData['user']->id ?? 'unknown',
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating email'
            ], 500);
        }
    }

    /**
     * Update user's password
     */
    public function updatePassword(Request $request)
    {
        try {
            // Check authentication
            $authData = $this->checkAuthentication($request);
            
            if (!$authData['authenticated'] || !$authData['user']) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to update your password'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $authData['user'];

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            // Check if new password is different from current
            if (Hash::check($request->new_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password must be different from current password'
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            Log::info('User password updated', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Password update error: ' . $e->getMessage(), [
                'user_id' => $authData['user']->id ?? 'unknown',
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating password'
            ], 500);
        }
    }

    /**
     * Get current user settings data
     */
    public function getUserSettings(Request $request)
    {
        // Check authentication
        $authData = $this->checkAuthentication($request);
        
        if (!$authData['authenticated'] || !$authData['user']) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to view settings'
            ], 401);
        }

        $user = $authData['user'];

        return response()->json([
            'success' => true,
            'settings' => [
                'email' => $user->email,
                'email_notifications' => $user->email_notifications ?? true,
                'username' => $user->username,
                'display_name' => $user->display_name,
                'bio' => $user->bio,
                'avatar_url' => $user->avatar_url ? asset('storage/' . $user->avatar_url) : null,
                'created_at' => $user->created_at->format('F j, Y')
            ]
        ]);
    }

    /**
     * Update general profile settings
     */
    public function updateProfileSettings(Request $request)
    {
        // Check authentication
        $authData = $this->checkAuthentication($request);
        
        if (!$authData['authenticated'] || !$authData['user']) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to update settings'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'You must be logged in to update settings');
        }

        $user = $authData['user'];

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'display_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'email_notifications' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $updateData = $request->only(['username', 'display_name', 'bio']);
        
        if ($request->has('email_notifications')) {
            $updateData['email_notifications'] = $request->boolean('email_notifications');
        }

        $user->update($updateData);

        // Clear user cache
        Cache::forget('user_stats_' . $user->id);

        Log::info('User profile settings updated', [
            'user_id' => $user->id,
            'updated_fields' => array_keys($updateData)
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile settings updated successfully',
                'user' => [
                    'username' => $user->username,
                    'display_name' => $user->display_name,
                    'bio' => $user->bio,
                    'email_notifications' => $user->email_notifications
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Profile settings updated successfully');
    }

    /**
     * Delete user account (with password confirmation)
     */
    public function deleteAccount(Request $request)
    {
        // Check authentication
        $authData = $this->checkAuthentication($request);
        
        if (!$authData['authenticated'] || !$authData['user']) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to delete your account'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'You must be logged in to delete your account');
        }

        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE'
        ]);

        $user = $authData['user'];

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is incorrect'
                ], 422);
            }
            return redirect()->back()->with('error', 'Password is incorrect');
        }

        // Log the account deletion
        Log::info('User account deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
            'username' => $user->username
        ]);

        // Delete all user sessions
        UserSession::where('user_id', $user->id)->delete();

        // Clear user cache
        Cache::forget('user_stats_' . $user->id);

        // Soft delete or hard delete the user (depending on your requirements)
        $user->delete();

        // Clear the session cookie
        $response = response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);

        return $response->withCookie(cookie()->forget('session_token'));
    }

    /**
     * Check authentication using the same logic as other controllers
     */
    private function checkAuthentication(Request $request)
    {
        $sessionToken = $request->cookie('session_token');

        if (!$sessionToken) {
            return [
                'authenticated' => false,
                'user' => null
            ];
        }

        // Find active session
        $session = UserSession::with('user')
            ->where('session_token', hash('sha256', $sessionToken))
            ->where('expires_at', '>', now())
            ->first();

        if (!$session || !$session->user) {
            return [
                'authenticated' => false,
                'user' => null
            ];
        }

        return [
            'authenticated' => true,
            'user' => $session->user
        ];
    }
}