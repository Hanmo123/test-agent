<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'logto_id',
        'nickname',
        'email',
        'avatar',
        'level',
        'visibility',
        'is_student',
        'student_verified_at',
        'is_member',
        'member_expires_at',
        'preferences',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'student_verified_at' => 'datetime',
        'member_expires_at' => 'datetime',
        'preferences' => 'array',
        'is_student' => 'boolean',
        'is_member' => 'boolean',
    ];

    protected $hidden = [
        'logto_id',
    ];

    public function relationships()
    {
        return $this->hasMany(Relationship::class, 'owner_user_id');
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function works()
    {
        return $this->hasMany(Work::class);
    }

    public function drafts()
    {
        return $this->hasMany(Draft::class);
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'followed_user_id');
    }

    public function following()
    {
        return $this->hasMany(Follow::class, 'follower_user_id');
    }

    public function shutterTime()
    {
        return $this->hasOne(ShutterTime::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function canDownloadOriginal(): bool
    {
        return $this->level >= 1;
    }

    public function canLike(): bool
    {
        return $this->level >= 1;
    }

    public function isActiveMember(): bool
    {
        return $this->is_member && 
               $this->member_expires_at && 
               $this->member_expires_at->isFuture();
    }

    public function getDailyOriginalDownloadLimit(): int
    {
        return $this->isActiveMember() ? PHP_INT_MAX : config('onlyshots.limits.daily_original_downloads');
    }
}