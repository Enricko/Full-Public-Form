<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'bio',
        'email',
        'password',
        'display_name',
        'avatar_url',
        'role',
        'email_notifications',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_notifications' => 'boolean',
    ];

    // Relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function savedPosts()
    {
        return $this->belongsToMany(Post::class, 'saved_posts');
    }

    // Following relationships
    public function following()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id');
    }

    public function followingHashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'hashtag_follows');
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function hasLikedPost($postId)
    {
        return $this->likes()->where('post_id', $postId)->exists();
    }

    public function hasLikedComment($commentId)
    {
        return $this->likes()->where('comment_id', $commentId)->exists();
    }

    public function hasSavedPost($postId)
    {
        return $this->savedPosts()->where('post_id', $postId)->exists();
    }

    public function isFollowing($userId)
    {
        return $this->following()->where('following_id', $userId)->exists();
    }

    public function isFollowingHashtag($hashtagId)
    {
        return $this->followingHashtags()->where('hashtag_id', $hashtagId)->exists();
    }

    // ADD THESE NEW METHODS:

    /**
     * Follow a user
     */
    public function follow($userId)
    {
        if ($this->id === $userId) {
            return false; // Can't follow yourself
        }

        if ($this->isFollowing($userId)) {
            return false; // Already following
        }

        return $this->following()->attach($userId);
    }

    /**
     * Unfollow a user
     */
    public function unfollow($userId)
    {
        return $this->following()->detach($userId);
    }

    /**
     * Get follower count
     */
    public function getFollowerCountAttribute()
    {
        return $this->followers()->count();
    }

    /**
     * Get following count
     */
    public function getFollowingCountAttribute()
    {
        return $this->following()->count();
    }

    /**
     * Check if user is followed by another user
     */
    public function isFollowedBy($userId)
    {
        return $this->followers()->where('follower_id', $userId)->exists();
    }
}
