<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'relationship_id',
        'filename',
        'oss_key',
        'type',
        'exif_json',
        'width',
        'height',
        'size',
        'mime_type',
        'processed_sizes',
        'processing_complete',
        'hide_exif',
    ];

    protected $casts = [
        'exif_json' => 'array',
        'processed_sizes' => 'array',
        'processing_complete' => 'boolean',
        'hide_exif' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relationship()
    {
        return $this->belongsTo(Relationship::class);
    }

    public function works()
    {
        return $this->belongsToMany(Work::class, 'work_resource_pivots')
                    ->withPivot('sort_order')
                    ->withTimestamps();
    }

    public function getThumbnailUrl(): ?string
    {
        $sizes = $this->processed_sizes;
        return $sizes['thumbnail'] ?? null;
    }

    public function getDisplayUrl(): ?string
    {
        $sizes = $this->processed_sizes;
        return $sizes['display'] ?? null;
    }

    public function getHdUrl(): ?string
    {
        $sizes = $this->processed_sizes;
        return $sizes['hd'] ?? null;
    }

    public function getOriginalUrl(): ?string
    {
        $sizes = $this->processed_sizes;
        return $sizes['original'] ?? null;
    }

    public function getExifData(): array
    {
        if ($this->hide_exif) {
            return [];
        }
        
        return $this->exif_json ?? [];
    }
}