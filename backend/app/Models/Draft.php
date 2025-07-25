<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content_json',
        'cover_resource_id',
        'tags',
        'history_json',
    ];

    protected $casts = [
        'content_json' => 'array',
        'tags' => 'array',
        'history_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coverResource()
    {
        return $this->belongsTo(Resource::class, 'cover_resource_id');
    }

    public function work()
    {
        return $this->hasOne(Work::class);
    }

    public function addToHistory(array $content): void
    {
        $history = $this->history_json ?? [];
        
        $history[] = [
            'content' => $content,
            'timestamp' => now()->toISOString(),
        ];
        
        // Keep only last 20 versions
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }
        
        $this->history_json = $history;
        $this->save();
    }
}