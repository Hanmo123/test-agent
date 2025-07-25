<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelationshipInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'relationship_id',
        'invitee_email',
        'invitee_user_id',
        'role',
        'status',
        'token',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function relationship()
    {
        return $this->belongsTo(Relationship::class);
    }

    public function inviteeUser()
    {
        return $this->belongsTo(User::class, 'invitee_user_id');
    }

    public function isExpired(): bool
    {
        return $this->expired_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }
}