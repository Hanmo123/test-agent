<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Work extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'draft_id',
        'user_id',
        'relationship_id',
        'status',
        'title',
        'content',
        'tags',
        'cover_resource_id',
        'likes_count',
        'views_count',
        'moderation_result',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'moderation_result' => 'array',
        'published_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relationship()
    {
        return $this->belongsTo(Relationship::class);
    }

    public function draft()
    {
        return $this->belongsTo(Draft::class);
    }

    public function coverResource()
    {
        return $this->belongsTo(Resource::class, 'cover_resource_id');
    }

    public function resources()
    {
        return $this->belongsToMany(Resource::class, 'work_resource_pivots')
                    ->withPivot('sort_order')
                    ->withTimestamps()
                    ->orderBy('pivot_sort_order');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_work_pivots')
                    ->withPivot('sort_order')
                    ->withTimestamps();
    }

    public function dailyPhotos()
    {
        return $this->hasMany(DailyPhoto::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function canBeViewedBy(?User $user): bool
    {
        if (!$this->isPublished()) {
            return $user && $user->id === $this->user_id;
        }

        $owner = $this->user;
        
        if ($owner->visibility === 'public') {
            return true;
        }
        
        if (!$user) {
            return false;
        }
        
        if ($user->id === $this->user_id) {
            return true;
        }
        
        if ($owner->visibility === 'followers_only') {
            return $user->following()->where('followed_user_id', $this->user_id)->exists();
        }
        
        return false; // private
    }
}