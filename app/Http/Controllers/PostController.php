<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Like;
use App\Models\SavedPost;
use App\Models\User;

class PostController extends Controller
{
    private $userId = 4;  

    public function like(Post $post)
    {
        // Use dummy user ID 6 instead of Auth::user()
        $user = User::find($this->userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $existingLike = Like::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $post->decrement('like_count');
            $liked = false;
            $message = 'Post unliked';
        } else {
            Like::create([
                'user_id' => $user->id,
                'post_id' => $post->id
            ]);
            $post->increment('like_count');
            $liked = true;
            $message = 'Post liked';
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'like_count' => $post->fresh()->like_count,
            'message' => $message
        ]);
    }

    public function save(Post $post)
    {
        // Use dummy user ID 6 instead of Auth::user()
        $user = User::find($this->userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $existingSave = SavedPost::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existingSave) {
            $existingSave->delete();
            $saved = false;
            $message = 'Post removed from saved';
        } else {
            SavedPost::create([
                'user_id' => $user->id,
                'post_id' => $post->id
            ]);
            $saved = true;
            $message = 'Post saved';
        }

        return response()->json([
            'success' => true,
            'saved' => $saved,
            'message' => $message
        ]);
    }

    public function share(Post $post)
    {
        $post->increment('share_count');

        return response()->json([
            'success' => true,
            'share_count' => $post->fresh()->share_count,
            'message' => 'Post shared'
        ]);
    }

    public function show(Post $post)
    {
        $post->load([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            },
            'comments.user:id,username,display_name,avatar_url',
            'comments.replies.user:id,username,display_name,avatar_url',
            'hashtags:id,name'
        ]);

        // Add interaction status for dummy user
        $user = User::find($this->userId);
        if ($user) {
            $post->is_liked = Like::where('user_id', $user->id)
                                 ->where('post_id', $post->id)
                                 ->exists();
            
            $post->is_saved = SavedPost::where('user_id', $user->id)
                                     ->where('post_id', $post->id)
                                     ->exists();
        }

        return view('posts.show', compact('post'));
    }

    /**
     * Get posts for home feed (if you need this)
     */
    public function index(Request $request)
    {
        $posts = Post::with([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            }
        ])
        ->latest()
        ->paginate(10);

        // Add interaction status for dummy user
        $user = User::find($this->userId);
        if ($user) {
            $posts->getCollection()->transform(function ($post) use ($user) {
                $post->is_liked = Like::where('user_id', $user->id)
                                     ->where('post_id', $post->id)
                                     ->exists();
                
                $post->is_saved = SavedPost::where('user_id', $user->id)
                                         ->where('post_id', $post->id)
                                         ->exists();
                
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

        return view('posts.index', compact('posts'));
    }

    /**
     * Toggle like (alternative method name for compatibility)
     */
    public function toggleLike(Post $post)
    {
        return $this->like($post);
    }

    /**
     * Toggle save (alternative method name for compatibility)
     */
    public function toggleSave(Post $post)
    {
        return $this->save($post);
    }
}