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

        // Check if user is authenticated (from localStorage via session/cookie)
        $currentUserId = $this->getCurrentUserId($request);
        $isAuthenticated = !is_null($currentUserId);

        // Set default interaction states
        $post->is_liked = false;
        $post->is_saved = false;

        // Only check interactions if user is authenticated
        if ($isAuthenticated) {
            $post->is_liked = $post->likes()->where('user_id', $currentUserId)->exists();
            $post->is_saved = $post->savedByUsers()->where('user_id', $currentUserId)->exists();
        }

        // Get comments with pagination
        $comments = Comment::with(['user', 'replies.user'])
            ->where('post_id', $postId)
            ->whereNull('parent_comment_id') // Only top-level comments
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Add liked status for each comment and reply (only if authenticated)
        foreach ($comments as $comment) {
            $comment->is_liked = $isAuthenticated ? 
                $comment->likes()->where('user_id', $currentUserId)->exists() : false;

            // Check replies too
            foreach ($comment->replies as $reply) {
                $reply->is_liked = $isAuthenticated ? 
                    $reply->likes()->where('user_id', $currentUserId)->exists() : false;
            }
        }

        // Get current user for form (null if guest)
        $currentUser = $isAuthenticated ? User::find($currentUserId) : null;

        return view('pages.comment', compact('post', 'comments', 'currentUser', 'isAuthenticated'));
    }

    public function store(Request $request)
    {
        // Check if user is authenticated
        $currentUserId = $this->getCurrentUserId($request);
        
        if (!$currentUserId) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to comment'
                ], 401);
            }
            return redirect()->back()->with('error', 'You must be logged in to comment');
        }

        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string|max:1000',
            'parent_comment_id' => 'nullable|exists:comments,id'
        ]);

        $comment = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => $currentUserId,
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
        // Check if user is authenticated
        $currentUserId = $this->getCurrentUserId($request);
        
        if (!$currentUserId) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to like comments'
                ], 401);
            }
            return redirect()->back()->with('error', 'You must be logged in to like comments');
        }

        $existingLike = Like::where([
            'user_id' => $currentUserId,
            'comment_id' => $comment->id
        ])->first();

        if ($existingLike) {
            $existingLike->delete();
            $comment->decrement('like_count');
            $liked = false;
        } else {
            Like::create([
                'user_id' => $currentUserId,
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

        // Check if user is authenticated
        $currentUserId = $this->getCurrentUserId($request);
        $isAuthenticated = !is_null($currentUserId);

        // Add liked status (only if authenticated)
        foreach ($comments as $comment) {
            $comment->is_liked = $isAuthenticated ? 
                $comment->likes()->where('user_id', $currentUserId)->exists() : false;

            foreach ($comment->replies as $reply) {
                $reply->is_liked = $isAuthenticated ? 
                    $reply->likes()->where('user_id', $currentUserId)->exists() : false;
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'comments' => $comments->items(),
                'has_more' => $comments->hasMorePages(),
                'next_page' => $comments->currentPage() + 1,
                'is_authenticated' => $isAuthenticated
            ]);
        }

        return redirect()->back();
    }

    /**
     * Get current user ID from request (checking various sources)
     */
    private function getCurrentUserId(Request $request)
    {
        // Method 1: Check if user_id is passed in request (from frontend)
        if ($request->has('user_id') && $request->user_id) {
            return $request->user_id;
        }

        // Method 2: Check session for stored user ID
        if (session('user_id')) {
            return session('user_id');
        }

        // Method 3: Check for Authorization header or token
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            // Here you would decode/validate the token and get user ID
            // For now, return null if no token validation is implemented
        }

        // Method 4: Check cookie (if you store user info in cookies)
        if ($request->cookie('user_id')) {
            return $request->cookie('user_id');
        }

        // Method 5: Fallback to hardcoded for testing (remove in production)
        // You can remove this in production
        if (config('app.env') === 'local') {
            // Only return hardcoded ID if explicitly testing
            // return 6;
        }

        return null; // Guest user
    }
}