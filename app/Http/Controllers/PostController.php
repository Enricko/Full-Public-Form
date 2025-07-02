<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\Like;
use App\Models\SavedPost;
use App\Models\User;
use App\Models\Attachment;
use App\Models\Hashtag;

class PostController extends Controller
{
    /**
     * Show the form for creating a new post
     */
    public function create()
    {
        return view('pages.tambah');
    }

    /**
     * Store a newly created post
     */
    public function store(Request $request)
    {
        // Check if user is authenticated
        $user = $request->user();
        if (!$user) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            return redirect()->route('home')->with('error', 'Please login to create posts');
        }

        // Validate the request
        $request->validate([
            'content' => 'required|string|max:500',
            'tags' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max per image
            'videos.*' => 'nullable|mimes:mp4,avi,mov,wmv|max:51200', // 50MB max per video
        ]);

        try {
            DB::beginTransaction();

            // Create the post
            $post = Post::create([
                'user_id' => $user->id,
                'content' => $request->input('content'),
                'like_count' => 0,
                'comment_count' => 0,
                'share_count' => 0,
            ]);

            // Handle hashtags
            if ($request->filled('tags')) {
                $this->processHashtags($post, $request->input('tags'));
            }

            // Handle file uploads
            $uploadOrder = 1;
            
            // Process images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $this->uploadFile($post, $image, $uploadOrder);
                    $uploadOrder++;
                }
            }

            // Process videos
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $this->uploadFile($post, $video, $uploadOrder);
                    $uploadOrder++;
                }
            }

            DB::commit();

            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Post created successfully!',
                    'redirect' => route('home'),
                    'post' => [
                        'id' => $post->id,
                        'content' => $post->content,
                        'created_at' => $post->created_at->format('Y-m-d H:i:s')
                    ]
                ]);
            }

            // For regular form submission, redirect to home with success message
            return redirect()->route('home')->with('success', 'Post created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            
            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating post: ' . $e->getMessage()
                ], 500);
            }

            // For regular form submission, redirect back with error
            return redirect()->back()->withErrors(['error' => 'Error creating post: ' . $e->getMessage()])->withInput();
        }
    }

    public function like(Request $request, Post $post)
    {
        // Check if user is authenticated
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to like posts'
            ], 401);
        }

        // Check for existing like (only for posts, not comments)
        $existingLike = Like::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->whereNull('comment_id') // Important: only post likes
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $post->decrement('like_count');
            $liked = false;
            $message = 'Post unliked';
        } else {
            Like::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'comment_id' => null // Important: set comment_id to null for post likes
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

    public function save(Request $request, Post $post)
    {
        // Check if user is authenticated
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to save posts'
            ], 401);
        }

        // Use your many-to-many relationship
        $isSaved = $user->savedPosts()->where('post_id', $post->id)->exists();

        if ($isSaved) {
            $user->savedPosts()->detach($post->id);
            $saved = false;
            $message = 'Post removed from saved';
        } else {
            $user->savedPosts()->attach($post->id, [
                'created_at' => now(),
                'updated_at' => now()
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

    public function share(Request $request, Post $post)
    {
        // Allow sharing for both authenticated and guest users
        // But you might want to track who shared it if authenticated
        $user = $request->user();
        
        $post->increment('share_count');

        $message = $user ? 'Post shared' : 'Post shared';

        return response()->json([
            'success' => true,
            'share_count' => $post->fresh()->share_count,
            'message' => $message
        ]);
    }

    public function show(Request $request, Post $post)
    {
        $post->load([
            'user:id,username,display_name,avatar_url',
            'attachments' => function ($query) {
                $query->orderBy('upload_order');
            },
            'hashtags'
        ]);

        // Add interaction status based on authentication
        $user = $request->user();
        if ($user) {
            $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
            $post->is_saved = $user->savedPosts()->where('post_id', $post->id)->exists();
        } else {
            $post->is_liked = false;
            $post->is_saved = false;
        }

        return view('posts.show', compact('post'));
    }

    /**
     * Process hashtags from the tags string
     */
    private function processHashtags(Post $post, $tagsString)
    {
        // Extract hashtags from the content as well
        $contentHashtags = $this->extractHashtagsFromText($post->content);
        
        // Extract hashtags from the tags input
        $inputTags = array_filter(
            array_map('trim', explode(' ', $tagsString)),
            function($tag) {
                return !empty($tag);
            }
        );

        // Combine and clean hashtags
        $allTags = array_merge($contentHashtags, $inputTags);
        $cleanTags = array_unique(
            array_map(function($tag) {
                // Remove # if present and convert to lowercase
                return strtolower(ltrim($tag, '#'));
            }, $allTags)
        );

        foreach ($cleanTags as $tagName) {
            if (!empty($tagName)) {
                // Find or create hashtag
                $hashtag = Hashtag::firstOrCreate(
                    ['name' => $tagName],
                    ['post_count' => 0]
                );

                // Attach to post (if not already attached)
                if (!$post->hashtags()->where('hashtag_id', $hashtag->id)->exists()) {
                    $post->hashtags()->attach($hashtag->id);
                    
                    // Increment post count
                    $hashtag->increment('post_count');
                }
            }
        }
    }

    /**
     * Extract hashtags from text content
     */
    private function extractHashtagsFromText($text)
    {
        preg_match_all('/#([a-zA-Z0-9_]+)/', $text, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Upload and store file attachment
     */
    private function uploadFile(Post $post, $file, $uploadOrder)
    {
        // Generate unique filename
        $fileName = time() . '_' . $uploadOrder . '_' . $file->getClientOriginalName();
        
        // Determine storage path based on file type
        $isImage = str_starts_with($file->getMimeType(), 'image/');
        $storagePath = $isImage ? 'posts/images' : 'posts/videos';
        
        // Store the file
        $filePath = $file->storeAs($storagePath, $fileName, 'public');

        // Create attachment record
        Attachment::create([
            'post_id' => $post->id,
            'user_id' => $post->user_id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'upload_order' => $uploadOrder,
        ]);
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

        // Add interaction status based on authentication
        $user = $request->user();
        if ($user) {
            $posts->getCollection()->transform(function ($post) use ($user) {
                $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
                $post->is_saved = $user->savedPosts()->where('post_id', $post->id)->exists();
                return $post;
            });
        } else {
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

        return view('posts.index', compact('posts'));
    }

    /**
     * Toggle like (alternative method name for compatibility)
     */
    public function toggleLike(Request $request, Post $post)
    {
        return $this->like($request, $post);
    }

    /**
     * Toggle save (alternative method name for compatibility)
     */
    public function toggleSave(Request $request, Post $post)
    {
        return $this->save($request, $post);
    }
}