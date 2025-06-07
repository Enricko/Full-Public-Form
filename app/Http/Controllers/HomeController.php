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
            // Add interaction status for authenticated users
            if (Auth::check()) {
                $post->is_liked = $post->isLikedBy(Auth::user());
                $post->is_saved = $post->isSavedBy(Auth::user());
            } else {
                $post->is_liked = false;
                $post->is_saved = false;
            }

            // Add share count if not already loaded
            if (!isset($post->share_count)) {
                $post->share_count = $post->shares()->count();
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
        $cacheKey = 'suggested_users_' . (Auth::id() ?? 'guest');

        return Cache::remember($cacheKey, 600, function () { // Cache for 10 minutes
            return User::where('id', '!=', Auth::id() ?? 0)
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
        if (Auth::check()) {
            Cache::forget('suggested_users_' . Auth::id());
        }

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
