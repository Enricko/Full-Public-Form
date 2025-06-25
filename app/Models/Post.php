<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'like_count',
        'comment_count',
        'share_count',
    ];

    protected $casts = [
        'like_count' => 'integer',
        'comment_count' => 'integer',
        'share_count' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
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
        return $this->hasMany(Attachment::class)->orderBy('upload_order');
    }

    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'post_hashtags');
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_posts');
    }

    // Scopes
    public function scopeWithUserAndCounts($query)
    {
        return $query->with(['user', 'attachments'])
            ->withCount(['comments', 'likes']);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeSavedByUser($query, $userId)
    {
        return $query->whereHas('savedByUsers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    // Helper methods
    public function isLikedBy($user)
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isSavedBy($user)
    {
        if (!$user) return false;
        return $this->savedByUsers()->where('user_id', $user->id)->exists();
    }

    // Get hashtags as a formatted string
    public function getHashtagsString()
    {
        return $this->hashtags->pluck('name')->map(function($tag) {
            return '#' . $tag;
        })->implode(' ');
    }

    // Get image attachments only
    public function getImageAttachments()
    {
        return $this->attachments()->where('file_type', 'like', 'image/%')->get();
    }

    // Get video attachments only
    public function getVideoAttachments()
    {
        return $this->attachments()->where('file_type', 'like', 'video/%')->get();
    }
}