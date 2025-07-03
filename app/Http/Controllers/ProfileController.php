<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use App\Models\SavedPost;
use App\Models\Comment;
use App\Models\UserSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function index(Request $request, $userId = null)
    {
        // Check authentication using existing system
        $authData = $this->checkAuthentication($request);
        $isAuthenticated = $authData['authenticated'];
        $currentUser = $authData['user'];

        // If no userId provided, show current user's profile (if authenticated)
        if (!$userId) {
            if (!$isAuthenticated || !$currentUser) {
                return redirect()->route('login')->with('error', 'Please log in to view your profile');
            }
            $targetUserId = $currentUser->id;
        } else {
            $targetUserId = $userId;
        }

        $user = User::where('id', $targetUserId)->first();

        if (!$user) {
            abort(404, 'User not found');
        }

        // Check if this is the current user's own profile (for edit permissions)
        $isOwnProfile = $isAuthenticated && $currentUser && $targetUserId == $currentUser->id;

        // Check follow status (only if authenticated and viewing someone else's profile)
        $isFollowing = false;
        if ($isAuthenticated && $currentUser && !$isOwnProfile) {
            $isFollowing = $currentUser->isFollowing($targetUserId);
        }

        // Determine which tab we're showing
        $tab = $request->get('tab', 'posts');

        // Debug logging for AJAX requests
        if ($request->ajax()) {
            Log::info('AJAX request for profile tab: ' . $tab . ', page: ' . $request->get('page', 1) . ', user: ' . $targetUserId);
        }

        // Handle comments tab differently
        if ($tab === 'comments') {
            $comments = $this->getUserComments($user, 10);

            // For AJAX requests, return JSON with HTML
            if ($request->ajax()) {
                try {
                    $html = view('components.comments-list', compact('comments'))->render();

                    return response()->json([
                        'success' => true,
                        'posts' => $html,
                        'hasMore' => $comments->hasMorePages(),
                        'currentPage' => $comments->currentPage(),
                        'lastPage' => $comments->lastPage(),
                        'total' => $comments->total(),
                        'perPage' => $comments->perPage(),
                        'from' => $comments->firstItem(),
                        'to' => $comments->lastItem(),
                        'tab' => $tab
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to load comments. Please try again.',
                        'error' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                }
            }

            // Get user statistics
            $stats = $this->getUserStats($user);
            $posts = collect(); // Empty for comments tab

            return view("pages.profile", compact('user', 'posts', 'stats', 'tab', 'comments', 'isOwnProfile', 'isFollowing', 'currentUser', 'isAuthenticated'));
        }

        // Get posts based on tab type (for non-comments tabs)
        $posts = $this->getPostsByTab($user, $tab, $request->get('page', 1), $isOwnProfile);

        // Add interaction status for the current user (only if authenticated)
        if ($isAuthenticated && $currentUser) {
            $posts->getCollection()->transform(function ($post) use ($currentUser) {
                $post->is_liked = $post->isLikedBy($currentUser);
                $post->is_saved = $post->isSavedBy($currentUser);

                return $post;
            });
        }

        // For AJAX requests, return JSON with HTML
        if ($request->ajax()) {
            try {
                $html = view('components.post-list', compact('posts'))->render();

                return response()->json([
                    'success' => true,
                    'posts' => $html,
                    'hasMore' => $posts->hasMorePages(),
                    'currentPage' => $posts->currentPage(),
                    'lastPage' => $posts->lastPage(),
                    'total' => $posts->total(),
                    'perPage' => $posts->perPage(),
                    'from' => $posts->firstItem(),
                    'to' => $posts->lastItem(),
                    'tab' => $tab
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load posts. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
        }

        // Get user statistics
        $stats = $this->getUserStats($user);

        $comments = collect(); // Empty for non-comments tabs

        return view("pages.profile", compact('user', 'posts', 'stats', 'tab', 'comments', 'isOwnProfile', 'isFollowing', 'currentUser', 'isAuthenticated'));
    }

    /**
     * Get posts based on tab type
     */
    private function getPostsByTab(User $user, string $tab, int $page = 1, bool $isOwnProfile = false)
    {
        $perPage = 10;

        switch ($tab) {
            case 'posts':
                return $this->getUserPosts($user, $perPage);

            case 'likes':
                // Only show liked posts if it's the user's own profile
                if ($isOwnProfile) {
                    return $this->getUserLikedPosts($user, $perPage);
                } else {
                    // Return empty collection for other users' liked posts (privacy)
                    return new \Illuminate\Pagination\LengthAwarePaginator(
                        collect([]),
                        0,
                        $perPage,
                        1,
                        ['path' => request()->url()]
                    );
                }

            case 'saved':
                // Only show saved posts if it's the user's own profile
                if ($isOwnProfile) {
                    return $this->getUserSavedPosts($user, $perPage);
                } else {
                    // Return empty collection for other users' saved posts (privacy)
                    return new \Illuminate\Pagination\LengthAwarePaginator(
                        collect([]),
                        0,
                        $perPage,
                        1,
                        ['path' => request()->url()]
                    );
                }

            default:
                return $this->getUserPosts($user, $perPage);
        }
    }

    /**
     * Get user's own posts
     */
    private function getUserPosts(User $user, int $perPage)
    {
        return Post::with([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            }
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get posts liked by user (using your model structure)
     */
    private function getUserLikedPosts(User $user, int $perPage)
    {
        // Get post IDs that user has liked (only posts, not comments)
        $postIds = Like::where('user_id', $user->id)
            ->whereNotNull('post_id')
            ->whereNull('comment_id')
            ->pluck('post_id');

        if ($postIds->isEmpty()) {
            // Return empty paginated collection
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                $perPage,
                1,
                ['path' => request()->url()]
            );
        }

        return Post::with([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            }
        ])
            ->whereIn('id', $postIds)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get posts saved by user (using your many-to-many relationship)
     */
    private function getUserSavedPosts(User $user, int $perPage)
    {
        return $user->savedPosts()
            ->with([
                'user:id,username,display_name,avatar_url',
                'attachments' => function ($query) {
                    $query->orderBy('upload_order');
                }
            ])
            ->latest('saved_posts.created_at')
            ->paginate($perPage);
    }

    /**
     * Get user's comments with post data (for comments tab)
     */
    private function getUserComments(User $user, int $perPage)
    {
        return Comment::with([
            'post.user:id,username,display_name,avatar_url',
            'post.attachments' => function ($query) {
                $query->where('file_type', 'like', 'image/%')->orderBy('upload_order')->limit(1);
            }
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get user statistics with caching (updated for your model structure)
     */
    private function getUserStats(User $user)
    {
        $cacheKey = 'user_stats_' . $user->id;

        return Cache::remember($cacheKey, 300, function () use ($user) {
            $stats = [
                'posts_count' => $user->posts()->count(),
                'comments_count' => $user->comments()->count(),
                'likes_count' => Like::where('user_id', $user->id)
                    ->whereNotNull('post_id')
                    ->whereNull('comment_id')
                    ->count(),
                'saved_count' => $user->savedPosts()->count(),
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count()
            ];

            return $stats;
        });
    }

    public function toggleFollow(Request $request, $userId)
    {
        // Check authentication using existing system
        $authData = $this->checkAuthentication($request);
        
        if (!$authData['authenticated'] || !$authData['user']) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to follow users'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'You must be logged in to follow users');
        }

        $currentUser = $authData['user'];
        $targetUser = User::find($userId);

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($currentUser->id === $targetUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot follow yourself'
            ], 400);
        }

        $isFollowing = $currentUser->isFollowing($targetUser->id);

        if ($isFollowing) {
            $currentUser->unfollow($targetUser->id);
            $followed = false;
            $message = 'Unfollowed ' . $targetUser->username;
        } else {
            $currentUser->follow($targetUser->id);
            $followed = true;
            $message = 'Following ' . $targetUser->username;
        }

        // Clear cache for both users
        Cache::forget('user_stats_' . $currentUser->id);
        Cache::forget('user_stats_' . $targetUser->id);

        return response()->json([
            'success' => true,
            'following' => $followed,
            'followers_count' => $targetUser->fresh()->followers()->count(),
            'following_count' => $currentUser->fresh()->following()->count(),
            'message' => $message
        ]);
    }

    /**
     * Switch tabs (for AJAX tab switching) - now supports user ID
     */
    public function switchTab(Request $request, $userId = null)
    {
        $request->validate([
            'tab' => 'required|string|in:posts,comments,likes,saved'
        ]);

        // Clear page parameter when switching tabs
        $request->merge(['page' => 1]);

        return $this->index($request, $userId);
    }

    /**
     * Load more posts for infinite scroll - now supports user ID
     */
    public function loadMore(Request $request, $userId = null)
    {
        $request->validate([
            'page' => 'integer|min:1',
            'tab' => 'string|in:posts,comments,likes,saved'
        ]);

        return $this->index($request, $userId);
    }

    /**
     * Edit profile method (only works for authenticated user's own profile)
     */
    public function editProfile(Request $request)
    {
        // Check authentication using existing system
        $authData = $this->checkAuthentication($request);
        
        if (!$authData['authenticated'] || !$authData['user']) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to edit your profile'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'You must be logged in to edit your profile');
        }

        $validated = $request->validate([
            'avatar_url' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,gif',
            'username' => 'required|max:255',
            'bio' => 'nullable|max:500',
        ]);

        $user = $authData['user'];

        $data = $validated;

        if ($request->hasFile('avatar_url')) {
            // Delete old avatar if exists
            if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            // Save new image
            $filePath = $request->file('avatar_url')->store('images/users/avatar', 'public');
            $data['avatar_url'] = $filePath;
        }

        // Update user
        $user->update($data);

        // Clear user stats cache
        Cache::forget('user_stats_' . $user->id);

        // For AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => [
                    'username' => $user->username,
                    'display_name' => $user->display_name,
                    'bio' => $user->bio,
                    'avatar_url' => $user->avatar_url ? asset('storage/' . $user->avatar_url) : null
                ]
            ]);
        }

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Refresh profile data - now supports user ID
     */
    public function refresh(Request $request, $userId = null)
    {
        // Check authentication using existing system
        $authData = $this->checkAuthentication($request);
        $isAuthenticated = $authData['authenticated'];
        $currentUser = $authData['user'];

        // If no userId provided, show current user's profile (if authenticated)
        if (!$userId) {
            if (!$isAuthenticated || !$currentUser) {
                return redirect()->route('login')->with('error', 'Please log in to view your profile');
            }
            $targetUserId = $currentUser->id;
        } else {
            $targetUserId = $userId;
        }

        $user = User::find($targetUserId);

        if (!$user) {
            abort(404, 'User not found');
        }

        // Clear relevant caches
        Cache::forget('user_stats_' . $user->id);

        $tab = $request->get('tab', 'posts');
        $isOwnProfile = $isAuthenticated && $currentUser && $targetUserId == $currentUser->id;

        if ($tab === 'comments') {
            $comments = $this->getUserComments($user, 10);

            if ($request->ajax()) {
                $html = view('components.comments-list', compact('comments'))->render();

                return response()->json([
                    'success' => true,
                    'posts' => $html,
                    'hasMore' => $comments->hasMorePages(),
                    'currentPage' => $comments->currentPage(),
                    'lastPage' => $comments->lastPage(),
                    'total' => $comments->total(),
                    'refreshed' => true,
                    'tab' => $tab
                ]);
            }
        } else {
            // Get fresh posts
            $posts = $this->getPostsByTab($user, $tab, 1, $isOwnProfile);

            if ($request->ajax()) {
                $html = view('components.post-list', compact('posts'))->render();

                return response()->json([
                    'success' => true,
                    'posts' => $html,
                    'hasMore' => $posts->hasMorePages(),
                    'currentPage' => $posts->currentPage(),
                    'lastPage' => $posts->lastPage(),
                    'total' => $posts->total(),
                    'refreshed' => true,
                    'tab' => $tab
                ]);
            }
        }

        return redirect()->route('profile', ['userId' => $userId]);
    }

    /**
     * Get all users for listing/search
     */
    public function users(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 20);

        $query = User::select('id', 'username', 'display_name', 'avatar_url', 'bio', 'created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('display_name', 'like', '%' . $search . '%');
            });
        }

        $users = $query->latest()->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]);
        }

        return view('pages.users', compact('users', 'search'));
    }

    /**
     * Check authentication using the same logic as CommentController
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

        // Find active session (same logic as CommentController)
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