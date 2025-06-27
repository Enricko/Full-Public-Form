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
    private $dummyUserId = 6;

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
            ->latest()
            ->paginate(5);

        // Add additional data for each post
        $posts->getCollection()->transform(function ($post) {
            $currentUser = Auth::user() ?? User::find($this->dummyUserId);
            
            if ($currentUser) {
                $post->is_liked = $post->isLikedBy($currentUser);
                $post->is_saved = $post->isSavedBy($currentUser);
            } else {
                $post->is_liked = false;
                $post->is_saved = false;
            }

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

        // For regular requests, return view without sidebar data
        return view('pages.home', compact('posts'));
    }

    /**
     * Get trending hashtags with follow status (JSON endpoint)
     */
    public function getTrendingHashtags()
    {
        $currentUser = Auth::user() ?? User::find($this->dummyUserId);
        
        $hashtags = Cache::remember('trending_hashtags_with_follow_' . ($currentUser ? $currentUser->id : 'guest'), 300, function () use ($currentUser) {
            $hashtags = Hashtag::trending(9)->get();
            
            if ($currentUser) {
                $hashtags->each(function ($hashtag) use ($currentUser) {
                    $hashtag->is_following = $currentUser->isFollowingHashtag($hashtag->id);
                });
            } else {
                $hashtags->each(function ($hashtag) {
                    $hashtag->is_following = false;
                });
            }
            
            return $hashtags;
        });

        return response()->json([
            'success' => true,
            'hashtags' => $hashtags
        ]);
    }

    /**
     * Get suggested users with follow status (JSON endpoint)
     */
    public function getSuggestedUsers()
    {
        $currentUser = Auth::user() ?? User::find($this->dummyUserId);
        $userId = $currentUser ? $currentUser->id : $this->dummyUserId;
        
        $users = Cache::remember('suggested_users_with_follow_' . $userId, 600, function () use ($userId, $currentUser) {
            $users = User::where('id', '!=', $userId)
                ->withCount('posts')
                ->has('posts', '>=', 1)
                ->inRandomOrder()
                ->limit(4)
                ->get();
                
            if ($currentUser) {
                $users->each(function ($user) use ($currentUser) {
                    $user->is_following = $currentUser->isFollowing($user->id);
                });
            } else {
                $users->each(function ($user) {
                    $user->is_following = false;
                });
            }
            
            return $users;
        });

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Follow/Unfollow a hashtag
     */
    public function toggleHashtagFollow(Request $request, $hashtagId)
    {
        $currentUser = Auth::user() ?? User::find($this->dummyUserId);
        $hashtag = Hashtag::find($hashtagId);

        if (!$currentUser || !$hashtag) {
            return response()->json([
                'success' => false,
                'message' => 'User or hashtag not found'
            ], 404);
        }

        $isFollowing = $currentUser->isFollowingHashtag($hashtag->id);

        if ($isFollowing) {
            $currentUser->followingHashtags()->detach($hashtag->id);
            $followed = false;
            $message = 'Hashtag #' . $hashtag->name . ' unfollowed';
        } else {
            $currentUser->followingHashtags()->attach($hashtag->id);
            $followed = true;
            $message = 'Following #' . $hashtag->name;
        }

        // Clear relevant caches
        Cache::forget('trending_hashtags_with_follow_' . $currentUser->id);

        return response()->json([
            'success' => true,
            'following' => $followed,
            'message' => $message
        ]);
    }

    /**
     * Follow/Unfollow a user
     */
    public function toggleUserFollow(Request $request, $userId)
    {
        $currentUser = Auth::user() ?? User::find($this->dummyUserId);
        $targetUser = User::find($userId);

        if (!$currentUser || !$targetUser) {
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

        // Clear relevant caches
        Cache::forget('suggested_users_with_follow_' . $currentUser->id);

        return response()->json([
            'success' => true,
            'following' => $followed,
            'message' => $message
        ]);
    }

    // Keep your existing methods...
    public function loadMore(Request $request)
    {
        $request->validate([
            'page' => 'integer|min:1',
        ]);

        return $this->index($request);
    }

    public function refresh(Request $request)
    {
        // Clear relevant caches
        Cache::forget('trending_hashtags');
        
        $userId = Auth::id() ?? $this->dummyUserId;
        Cache::forget('suggested_users_' . $userId);

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

        $posts->getCollection()->transform(function ($post) {
            $currentUser = Auth::user() ?? User::find($this->dummyUserId);
            
            if ($currentUser) {
                $post->is_liked = $post->isLikedBy($currentUser);
                $post->is_saved = $post->isSavedBy($currentUser);
            } else {
                $post->is_liked = false;
                $post->is_saved = false;
            }

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