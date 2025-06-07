<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Like;
use App\Models\SavedPost;

class PostController extends Controller
{
    public function like(Post $post)
    {
        $user = Auth::user();

        $existingLike = Like::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $post->decrement('like_count');
            $liked = false;
        } else {
            Like::create([
                'user_id' => $user->id,
                'post_id' => $post->id
            ]);
            $post->increment('like_count');
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'like_count' => $post->fresh()->like_count
        ]);
    }

    public function save(Post $post)
    {
        $user = Auth::user();

        $existingSave = SavedPost::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existingSave) {
            $existingSave->delete();
            $saved = false;
        } else {
            SavedPost::create([
                'user_id' => $user->id,
                'post_id' => $post->id
            ]);
            $saved = true;
        }

        return response()->json([
            'success' => true,
            'saved' => $saved
        ]);
    }

    public function share(Post $post)
    {
        $post->increment('share_count');

        return response()->json([
            'success' => true,
            'share_count' => $post->fresh()->share_count
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

        return view('posts.show', compact('post'));
    }
}
