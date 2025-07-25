<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'type',
        'name',
        'avatar',
        'description',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function works()
    {
        return $this->hasMany(Work::class);
    }

    public function invitations()
    {
        return $this->hasMany(RelationshipInvitation::class);
    }
}