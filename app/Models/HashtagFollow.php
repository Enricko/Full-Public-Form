<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class HashtagFollow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hashtag_id',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class);
    }
}
