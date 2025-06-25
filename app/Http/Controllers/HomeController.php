<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Hashtag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    private $dummyUserId = 6; // Dummy user ID for testing

    public function index(Request $request)
    {
        // Debug logging
        if ($request->ajax()) {
            Log::info('AJAX request for page: ' . $request->get('page', 1));
        }

        // Get posts with all related data
        $posts = Post::with([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            },
            'hashtags:id,name'
        ])
            ->withCount(['likes', 'comments'])
            ->latest() // Order by newest first instead of random for better UX
            ->paginate(5); // Start with smaller number for testing

        // Add additional data for each post
        $posts->getCollection()->transform(function ($post) {
            // Add interaction status for authenticated users or dummy user
            $currentUser = Auth::user() ?? User::find($this->dummyUserId);
            
            if ($currentUser) {
                $post->is_liked = $post->isLikedBy($currentUser);
                $post->is_saved = $post->isSavedBy($currentUser);
            } else {
                $post->is_liked = false;
                $post->is_saved = false;
            }

            // Ensure share_count is available
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
                    'to' => $posts->lastItem()
                ]);
            } catch (\Exception $e) {
                Log::error('Error loading posts: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load posts. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
        }

        // For regular requests, return view with additional data
        $trendingHashtags = $this->getTrendingHashtags();
        $suggestedUsers = $this->getSuggestedUsers();

        return view('pages.home', compact('posts', 'trendingHashtags', 'suggestedUsers'));
    }

    /**
     * Get trending hashtags with caching
     */
    private function getTrendingHashtags()
    {
        return Cache::remember('trending_hashtags', 300, function () { // Cache for 5 minutes
            return Hashtag::trending(9)->get();
        });
    }

    /**
     * Get suggested users with caching
     */
    private function getSuggestedUsers()
    {
        $userId = Auth::id() ?? $this->dummyUserId;
        $cacheKey = 'suggested_users_' . $userId;

        return Cache::remember($cacheKey, 600, function () use ($userId) { // Cache for 10 minutes
            return User::where('id', '!=', $userId)
                ->withCount('posts')
                ->has('posts', '>=', 1) // Only suggest users who have posted
                ->inRandomOrder()
                ->limit(4)
                ->get();
        });
    }

    /**
     * Load more posts for infinite scroll (alternative endpoint)
     */
    public function loadMore(Request $request)
    {
        $request->validate([
            'page' => 'integer|min:1',
        ]);

        return $this->index($request);
    }

    /**
     * Refresh posts (for pull-to-refresh functionality)
     */
    public function refresh(Request $request)
    {
        // Clear relevant caches
        Cache::forget('trending_hashtags');
        
        $userId = Auth::id() ?? $this->dummyUserId;
        Cache::forget('suggested_users_' . $userId);

        // Get fresh posts
        $posts = Post::with([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            },
            'hashtags:id,name'
        ])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(10);

        // Add interaction status for each post
        $posts->getCollection()->transform(function ($post) {
            $currentUser = Auth::user() ?? User::find($this->dummyUserId);
            
            if ($currentUser) {
                $post->is_liked = $post->isLikedBy($currentUser);
                $post->is_saved = $post->isSavedBy($currentUser);
            } else {
                $post->is_liked = false;
                $post->is_saved = false;
            }

            // Ensure share_count is available
            if (!isset($post->share_count)) {
                $post->share_count = $post->share_count ?? 0;
            }

            return $post;
        });

        if ($request->ajax()) {
            $html = view('components.post-list', compact('posts'))->render();

            return response()->json([
                'success' => true,
                'posts' => $html,
                'hasMore' => $posts->hasMorePages(),
                'currentPage' => $posts->currentPage(),
                'lastPage' => $posts->lastPage(),
                'total' => $posts->total(),
                'refreshed' => true
            ]);
        }

        return redirect()->route('home');
    }
}