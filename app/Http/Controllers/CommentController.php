<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use App\Models\Like;
use App\Models\UserSession;

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

        // Check if user is authenticated using existing auth system
        $authData = $this->checkAuthentication($request);
        $isAuthenticated = $authData['authenticated'];
        $currentUser = $authData['user'];

        // Set default interaction states
        $post->is_liked = false;
        $post->is_saved = false;

        // Only check interactions if user is authenticated
        if ($isAuthenticated && $currentUser) {
            $post->is_liked = $post->likes()->where('user_id', $currentUser->id)->exists();
            $post->is_saved = $post->savedByUsers()->where('user_id', $currentUser->id)->exists();
        }

        // Get comments with pagination
        $comments = Comment::with(['user', 'replies.user'])
            ->where('post_id', $postId)
            ->whereNull('parent_comment_id') // Only top-level comments
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Add liked status for each comment and reply (only if authenticated)
        foreach ($comments as $comment) {
            $comment->is_liked = ($isAuthenticated && $currentUser) ? 
                $comment->likes()->where('user_id', $currentUser->id)->exists() : false;

            // Check replies too
            foreach ($comment->replies as $reply) {
                $reply->is_liked = ($isAuthenticated && $currentUser) ? 
                    $reply->likes()->where('user_id', $currentUser->id)->exists() : false;
            }
        }

        return view('pages.comment', compact('post', 'comments', 'currentUser', 'isAuthenticated'));
    }

    public function store(Request $request)
    {
        // Check authentication using existing system
        $authData = $this->checkAuthentication($request);
        
        if (!$authData['authenticated'] || !$authData['user']) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to comment'
                ], 401);
            }
            return redirect()->back()->with('error', 'You must be logged in to comment');
        }

        $currentUser = $authData['user'];

        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string|max:1000',
            'parent_comment_id' => 'nullable|exists:comments,id'
        ]);

        $comment = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => $currentUser->id,
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
        // Check authentication using existing system
        $authData = $this->checkAuthentication($request);
        
        if (!$authData['authenticated'] || !$authData['user']) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to like comments'
                ], 401);
            }
            return redirect()->back()->with('error', 'You must be logged in to like comments');
        }

        $currentUser = $authData['user'];

        $existingLike = Like::where([
            'user_id' => $currentUser->id,
            'comment_id' => $comment->id
        ])->first();

        if ($existingLike) {
            $existingLike->delete();
            $comment->decrement('like_count');
            $liked = false;
        } else {
            Like::create([
                'user_id' => $currentUser->id,
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

        // Check authentication using existing system
        $authData = $this->checkAuthentication($request);
        $isAuthenticated = $authData['authenticated'];
        $currentUser = $authData['user'];

        // Add liked status (only if authenticated)
        foreach ($comments as $comment) {
            $comment->is_liked = ($isAuthenticated && $currentUser) ? 
                $comment->likes()->where('user_id', $currentUser->id)->exists() : false;

            foreach ($comment->replies as $reply) {
                $reply->is_liked = ($isAuthenticated && $currentUser) ? 
                    $reply->likes()->where('user_id', $currentUser->id)->exists() : false;
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
     * Check authentication using the same logic as LoginController::checkAuth
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

        // Find active session (same logic as LoginController)
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