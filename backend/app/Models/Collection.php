<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'cover_resource_id',
        'is_public',
        'subscribers_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coverResource()
    {
        return $this->belongsTo(Resource::class, 'cover_resource_id');
    }

    public function works()
    {
        return $this->belongsToMany(Work::class, 'collection_work_pivots')
                    ->withPivot('sort_order')
                    ->withTimestamps()
                    ->orderBy('pivot_sort_order');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function canBeViewedBy(?User $user): bool
    {
        if ($this->is_public) {
            return true;
        }
        
        return $user && $user->id === $this->user_id;
    }
}