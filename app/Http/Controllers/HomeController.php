<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Hashtag;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Get posts with all necessary relationships
        $posts = Post::with([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            },
            'hashtags'
        ])
        ->withCount(['likes', 'comments'])
        ->latest()
        ->paginate(10);

        // Check if user is authenticated and add interaction status
        $user = $request->user();
        if ($user) {
            $posts->getCollection()->transform(function ($post) use ($user) {
                $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
                $post->is_saved = $user->savedPosts()->where('post_id', $post->id)->exists();
                return $post;
            });
        } else {
            // For guests, set interaction status to false
            $posts->getCollection()->transform(function ($post) {
                $post->is_liked = false;
                $post->is_saved = false;
                return $post;
            });
        }

        if ($request->ajax()) {
            $html = view('components.post-list', compact('posts'))->render();
            
            return response()->json([
                'success' => true,
                'posts' => $html,
                'hasMore' => $posts->hasMorePages(),
                'currentPage' => $posts->currentPage(),
                'lastPage' => $posts->lastPage(),
                'total' => $posts->total()
            ]);
        }

        return view('pages.home', compact('posts'));
    }

    public function loadMore(Request $request)
    {
        $page = $request->get('page', 2);
        
        $posts = Post::with([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            },
            'hashtags'
        ])
        ->withCount(['likes', 'comments'])
        ->latest()
        ->paginate(10, ['*'], 'page', $page);

        // Check if user is authenticated and add interaction status
        $user = $request->user();
        if ($user) {
            $posts->getCollection()->transform(function ($post) use ($user) {
                $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
                $post->is_saved = $user->savedPosts()->where('post_id', $post->id)->exists();
                return $post;
            });
        } else {
            // For guests, set interaction status to false
            $posts->getCollection()->transform(function ($post) {
                $post->is_liked = false;
                $post->is_saved = false;
                return $post;
            });
        }

        if ($request->ajax()) {
            $html = view('components.post-list', compact('posts'))->render();
            
            return response()->json([
                'success' => true,
                'posts' => $html,
                'hasMore' => $posts->hasMorePages(),
                'currentPage' => $posts->currentPage(),
                'lastPage' => $posts->lastPage(),
                'total' => $posts->total()
            ]);
        }

        return redirect()->back();
    }

    public function refresh(Request $request)
    {
        return $this->index($request);
    }

    public function getTrendingHashtags(Request $request)
    {
        try {
            // Get current user if authenticated
            $user = $request->user();
            
            // Get trending hashtags
            $hashtags = Hashtag::withCount('posts')
                ->orderBy('posts_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($hashtag) use ($user) {
                    return [
                        'id' => $hashtag->id,
                        'name' => $hashtag->name,
                        'post_count' => $hashtag->posts_count,
                        'is_following' => $user ? $user->isFollowingHashtag($hashtag->id) : false
                    ];
                });
            
            return response()->json([
                'success' => true,
                'hashtags' => $hashtags
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading trending hashtags'
            ], 500);
        }
    }

    public function getSuggestedUsers(Request $request)
    {
        try {
            // Get current user if authenticated
            $user = $request->user();
            
            $query = User::withCount('posts')
                ->where('role', '!=', 'admin')
                ->orderBy('posts_count', 'desc')
                ->limit(5);
                
            // Exclude current user if authenticated
            if ($user) {
                $query->where('id', '!=', $user->id);
            }
            
            $users = $query->get()->map(function ($suggestedUser) use ($user) {
                return [
                    'id' => $suggestedUser->id,
                    'username' => $suggestedUser->username,
                    'display_name' => $suggestedUser->display_name,
                    'avatar_url' => $suggestedUser->avatar_url,
                    'posts_count' => $suggestedUser->posts_count,
                    'created_at' => $suggestedUser->created_at,
                    'is_following' => $user ? $user->isFollowing($suggestedUser->id) : false
                ];
            });
            
            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading suggested users'
            ], 500);
        }
    }

    public function toggleHashtagFollow(Request $request, $hashtagId)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $hashtag = Hashtag::find($hashtagId);
        
        if (!$hashtag) {
            return response()->json([
                'success' => false,
                'message' => 'Hashtag not found'
            ], 404);
        }

        $isFollowing = $user->isFollowingHashtag($hashtagId);
        
        if ($isFollowing) {
            $user->followingHashtags()->detach($hashtagId);
            $message = 'Hashtag unfollowed';
            $following = false;
        } else {
            $user->followingHashtags()->attach($hashtagId);
            $message = 'Hashtag followed';
            $following = true;
        }

        return response()->json([
            'success' => true,
            'following' => $following,
            'message' => $message
        ]);
    }

    public function toggleUserFollow(Request $request, $userId)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        if ($user->id == $userId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot follow yourself'
            ], 400);
        }

        $targetUser = User::find($userId);
        
        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $isFollowing = $user->isFollowing($userId);
        
        if ($isFollowing) {
            $user->unfollow($userId);
            $message = 'User unfollowed';
            $following = false;
        } else {
            $user->follow($userId);
            $message = 'User followed';
            $following = true;
        }

        return response()->json([
            'success' => true,
            'following' => $following,
            'message' => $message
        ]);
    }
}