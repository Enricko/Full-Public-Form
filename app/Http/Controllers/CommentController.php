<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use App\Models\Like;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $postId = $request->get('id');

        if (!$postId) {
            return redirect()->route('home')->with('error', 'Post not found');
        }

        // Get the post with all relationships
        $post = Post::with([
            'user',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            },
            'hashtags'
        ])->withCount(['likes', 'comments'])
            ->find($postId);

        if (!$post) {
            return redirect()->route('home')->with('error', 'Post not found');
        }

        // Check if current user has liked/saved the post (using user ID 6 for testing)
        $currentUserId = 6;
        $post->is_liked = $post->likes()->where('user_id', $currentUserId)->exists();
        $post->is_saved = $post->savedByUsers()->where('user_id', $currentUserId)->exists();

        // Get comments with pagination
        $comments = Comment::with(['user', 'replies.user'])
            ->where('post_id', $postId)
            ->whereNull('parent_comment_id') // Only top-level comments
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Add liked status for each comment and reply
        foreach ($comments as $comment) {
            $comment->is_liked = $comment->likes()->where('user_id', $currentUserId)->exists();

            // Check replies too
            foreach ($comment->replies as $reply) {
                $reply->is_liked = $reply->likes()->where('user_id', $currentUserId)->exists();
            }
        }

        // Get current user for form
        $currentUser = User::find($currentUserId);

        return view('pages.comment', compact('post', 'comments', 'currentUser'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string|max:1000',
            'parent_comment_id' => 'nullable|exists:comments,id'
        ]);

        $comment = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => 6, // Hardcoded for testing
            'parent_comment_id' => $request->parent_comment_id,
            'content' => $request->content,
            'like_count' => 0
        ]);

        // Update post comment count
        $post = Post::find($request->post_id);
        $post->increment('comment_count');

        if ($request->ajax()) {
            $comment->load('user');
            return response()->json([
                'success' => true,
                'comment' => $comment,
                'message' => 'Comment posted successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Comment posted successfully');
    }

    public function like(Request $request, Comment $comment)
    {
        $userId = 6; // Hardcoded for testing

        $existingLike = Like::where([
            'user_id' => $userId,
            'comment_id' => $comment->id
        ])->first();

        if ($existingLike) {
            $existingLike->delete();
            $comment->decrement('like_count');
            $liked = false;
        } else {
            Like::create([
                'user_id' => $userId,
                'comment_id' => $comment->id
            ]);
            $comment->increment('like_count');
            $liked = true;
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'liked' => $liked,
                'like_count' => $comment->like_count
            ]);
        }

        return redirect()->back();
    }

    public function loadMore(Request $request)
    {
        $postId = $request->get('post_id');
        $page = $request->get('page', 2);

        $comments = Comment::with(['user', 'replies.user'])
            ->where('post_id', $postId)
            ->whereNull('parent_comment_id')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page);

        // Add liked status
        $currentUserId = 6;
        foreach ($comments as $comment) {
            $comment->is_liked = $comment->likes()->where('user_id', $currentUserId)->exists();

            foreach ($comment->replies as $reply) {
                $reply->is_liked = $reply->likes()->where('user_id', $currentUserId)->exists();
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'comments' => $comments->items(),
                'has_more' => $comments->hasMorePages(),
                'next_page' => $comments->currentPage() + 1
            ]);
        }

        return redirect()->back();
    }
}
