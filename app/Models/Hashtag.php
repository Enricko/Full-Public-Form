<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'post_count',
    ];

    protected $casts = [
        'post_count' => 'integer',
    ];

    // Relationships
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_hashtags');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'hashtag_follows');
    }

    // Scopes
    public function scopeTrending($query, $limit = 10)
    {
        return $query->orderBy('post_count', 'desc')->limit($limit);
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    public function scopeFollowedByUser($query, $userId)
    {
        return $query->whereHas('followers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}
