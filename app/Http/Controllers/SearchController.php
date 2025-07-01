<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Hashtag;
use Carbon\Carbon;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $sort = $request->get('sort', 'relevance');
        $time = $request->get('time', 'anytime');
        $contentType = $request->get('content_type', []);
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Initialize results
        $posts = [];
        $users = collect();
        $hashtags = collect();
        $totalResults = 0;

        if (!empty($query)) {
            // Search Posts
            $postsQuery = Post::with(['user', 'attachments', 'hashtags'])
                ->where(function($q) use ($query) {
                    $q->where('content', 'like', "%{$query}%")
                      ->orWhereHas('hashtags', function($hashtagQuery) use ($query) {
                          $hashtagQuery->where('name', 'like', "%{$query}%");
                      });
                });

            // Apply time filter
            $this->applyTimeFilter($postsQuery, $time, $dateFrom, $dateTo);

            // Apply content type filter
            if (!empty($contentType)) {
                $this->applyContentTypeFilter($postsQuery, $contentType);
            }

            // Apply sorting
            $this->applySorting($postsQuery, $sort);

            // Get posts as array to avoid collection issues
            $postsCollection = $postsQuery->limit(20)->get();
            $posts = $postsCollection->toArray();

            // Add interaction data for posts
            foreach ($postsCollection as $index => $post) {
                $posts[$index]['is_liked'] = $this->checkIfLiked($post->id);
                $posts[$index]['is_saved'] = $this->checkIfSaved($post->id);
                
                // Convert back to object for blade compatibility
                $posts[$index] = (object) $posts[$index];
                if (isset($post->user)) {
                    $posts[$index]->user = $post->user;
                }
                if (isset($post->attachments)) {
                    $posts[$index]->attachments = $post->attachments;
                }
                if (isset($post->hashtags)) {
                    $posts[$index]->hashtags = $post->hashtags;
                }
                $posts[$index]->created_at = $post->created_at;
                $posts[$index]->like_count = $post->like_count ?? 0;
                $posts[$index]->comment_count = $post->comment_count ?? 0;
                $posts[$index]->share_count = $post->share_count ?? 0;
            }

            // Search Users
            $users = User::where('username', 'like', "%{$query}%")
                ->orWhere('display_name', 'like', "%{$query}%")
                ->orWhere('bio', 'like', "%{$query}%")
                ->with('posts')
                ->limit(10)
                ->get();

            // Search Hashtags
            $hashtags = Hashtag::where('name', 'like', "%{$query}%")
                ->orderBy('post_count', 'desc')
                ->limit(10)
                ->get();

            $totalResults = count($posts) + $users->count() + $hashtags->count();
        }

        return view('pages.search', compact(
            'posts', 'users', 'hashtags', 'query', 'totalResults',
            'sort', 'time', 'contentType', 'dateFrom', 'dateTo'
        ));
    }

    private function applyTimeFilter($query, $time, $dateFrom = null, $dateTo = null)
    {
        switch ($time) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->where('created_at', '>=', Carbon::now()->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', Carbon::now()->subMonth());
                break;
            case 'year':
                $query->where('created_at', '>=', Carbon::now()->subYear());
                break;
            default:
                if ($dateFrom) {
                    $query->whereDate('created_at', '>=', Carbon::parse($dateFrom));
                }
                if ($dateTo) {
                    $query->whereDate('created_at', '<=', Carbon::parse($dateTo));
                }
                break;
        }
    }

    private function applyContentTypeFilter($query, $contentTypes)
    {
        $query->where(function($q) use ($contentTypes) {
            foreach ($contentTypes as $type) {
                switch ($type) {
                    case 'image':
                        $q->orWhereHas('attachments', function($attachmentQuery) {
                            $attachmentQuery->where('file_type', 'like', 'image/%');
                        });
                        break;
                    case 'video':
                        $q->orWhereHas('attachments', function($attachmentQuery) {
                            $attachmentQuery->where('file_type', 'like', 'video/%');
                        });
                        break;
                    case 'links':
                        $q->orWhere('content', 'like', '%http%');
                        break;
                }
            }
        });
    }

    private function applySorting($query, $sort)
    {
        switch ($sort) {
            case 'recent':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $query->orderBy('like_count', 'desc');
                break;
            case 'commented':
                $query->orderBy('comment_count', 'desc');
                break;
            case 'relevance':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    private function checkIfLiked($postId)
    {
        // For testing with user ID 6 (no auth required)
        $userId = 6;
        
        // Check if Like model exists
        if (class_exists('App\Models\Like')) {
            return \App\Models\Like::where('post_id', $postId)
                ->where('user_id', $userId)
                ->exists();
        }
        
        return false;
    }

    private function checkIfSaved($postId)
    {
        // For testing with user ID 6 (no auth required)
        $userId = 6;
        
        // Check if SavedPost model exists
        if (class_exists('App\Models\SavedPost')) {
            return \App\Models\SavedPost::where('post_id', $postId)
                ->where('user_id', $userId)
                ->exists();
        }
        
        return false;
    }
}