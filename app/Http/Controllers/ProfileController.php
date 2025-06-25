<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use App\Models\SavedPost;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    // Our beloved user ID 6
    private $userId = 4;

    public function index(Request $request)
    {
        $user = User::where('id', $this->userId)->first();
        
        if (!$user) {
            abort(404, 'User not found');
        }

        // Determine which tab we're showing
        $tab = $request->get('tab', 'posts');
        
        // Debug logging for AJAX requests
        if ($request->ajax()) {
            Log::info('AJAX request for profile tab: ' . $tab . ', page: ' . $request->get('page', 1));
        }

        // Get posts based on tab type
        $posts = $this->getPostsByTab($user, $tab, $request->get('page', 1));

        // Add interaction status for the user (compatible with your PostController structure)
        $posts->getCollection()->transform(function ($post) use ($user) {
            $post->is_liked = $this->isPostLikedByUser($post, $user);
            $post->is_saved = $this->isPostSavedByUser($post, $user);
            
            // Ensure share_count exists (your PostController uses this)
            if (!isset($post->share_count)) {
                $post->share_count = $post->share_count ?? 0;
            }
            
            return $post;
        });

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

        return view("pages.profile", compact('user', 'posts', 'stats', 'tab'));
    }

    /**
     * Check if post is liked by user (compatible with your PostController)
     */
    private function isPostLikedByUser(Post $post, User $user)
    {
        return Like::where('user_id', $user->id)
                   ->where('post_id', $post->id)
                   ->exists();
    }

    /**
     * Check if post is saved by user (compatible with your PostController)
     */
    private function isPostSavedByUser(Post $post, User $user)
    {
        return SavedPost::where('user_id', $user->id)
                       ->where('post_id', $post->id)
                       ->exists();
    }

    /**
     * Get posts based on tab type
     */
    private function getPostsByTab(User $user, string $tab, int $page = 1)
    {
        $perPage = 10;
        
        switch ($tab) {
            case 'posts':
                return $this->getUserPosts($user, $perPage);
                
            case 'comments':
                return $this->getUserCommentedPosts($user, $perPage);
                
            case 'likes':
                return $this->getUserLikedPosts($user, $perPage);
                
            case 'saved':
                return $this->getUserSavedPosts($user, $perPage);
                
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
        ->select('*') // Get all columns including like_count, share_count
        ->where('user_id', $user->id)
        ->latest()
        ->paginate($perPage);
    }

    /**
     * Get posts where user has commented
     */
    private function getUserCommentedPosts(User $user, int $perPage)
    {
        // Get post IDs where user has commented
        $postIds = Comment::where('user_id', $user->id)
            ->distinct()
            ->pluck('post_id');

        if ($postIds->isEmpty()) {
            // Return empty paginated collection
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), 0, $perPage, 1, ['path' => request()->url()]
            );
        }

        return Post::with([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            }
        ])
        ->select('*') // Get all columns including like_count, share_count
        ->whereIn('id', $postIds)
        ->latest()
        ->paginate($perPage);
    }

    /**
     * Get posts liked by user
     */
    private function getUserLikedPosts(User $user, int $perPage)
    {
        // Get post IDs that user has liked
        $postIds = Like::where('user_id', $user->id)
            ->pluck('post_id');

        if ($postIds->isEmpty()) {
            // Return empty paginated collection
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), 0, $perPage, 1, ['path' => request()->url()]
            );
        }

        return Post::with([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            }
        ])
        ->select('*') // Get all columns including like_count, share_count
        ->whereIn('id', $postIds)
        ->latest()
        ->paginate($perPage);
    }

    /**
     * Get posts saved by user
     */
    private function getUserSavedPosts(User $user, int $perPage)
    {
        // Get post IDs that user has saved
        $postIds = SavedPost::where('user_id', $user->id)
            ->pluck('post_id');

        if ($postIds->isEmpty()) {
            // Return empty paginated collection
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), 0, $perPage, 1, ['path' => request()->url()]
            );
        }

        return Post::with([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            }
        ])
        ->select('*') // Get all columns including like_count, share_count
        ->whereIn('id', $postIds)
        ->latest()
        ->paginate($perPage);
    }

    /**
     * Get user statistics with caching
     */
    private function getUserStats(User $user)
    {
        $cacheKey = 'user_stats_' . $user->id;
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            $stats = [
                'posts_count' => Post::where('user_id', $user->id)->count(),
                'comments_count' => Comment::where('user_id', $user->id)->count(),
                'likes_count' => Like::where('user_id', $user->id)->count(),
                'saved_count' => SavedPost::where('user_id', $user->id)->count(),
                'followers_count' => 0, // You can implement followers later
                'following_count' => 0  // You can implement following later
            ];
            
            return $stats;
        });
    }

    /**
     * Switch tabs (for AJAX tab switching)
     */
    public function switchTab(Request $request)
    {
        $request->validate([
            'tab' => 'required|string|in:posts,comments,likes,saved'
        ]);

        // Clear page parameter when switching tabs
        $request->merge(['page' => 1]);

        return $this->index($request);
    }

    /**
     * Load more posts for infinite scroll
     */
    public function loadMore(Request $request)
    {
        $request->validate([
            'page' => 'integer|min:1',
            'tab' => 'string|in:posts,comments,likes,saved'
        ]);

        return $this->index($request);
    }

    /**
     * Your existing editProfile method (updated for better validation)
     */
    public function editProfile(Request $request)
    {
        $validated = $request->validate([
            'avatar_url' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,gif',
            'username' => 'required|max:255',
            'bio' => 'nullable|max:500',
        ]);

        $user = User::find($this->userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

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
     * Refresh profile data
     */
    public function refresh(Request $request)
    {
        $user = User::find($this->userId);
        if (!$user) {
            abort(404, 'User not found');
        }

        // Clear relevant caches
        Cache::forget('user_stats_' . $user->id);

        $tab = $request->get('tab', 'posts');
        
        // Get fresh posts
        $posts = $this->getPostsByTab($user, $tab, 1);

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

        return redirect()->route('profile');
    }
}