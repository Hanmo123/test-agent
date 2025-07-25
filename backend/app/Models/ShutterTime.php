<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShutterTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'last_daily_claimed_at',
    ];

    protected $casts = [
        'last_daily_claimed_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canClaimDaily(): bool
    {
        return !$this->last_daily_claimed_at || 
               $this->last_daily_claimed_at->lt(today());
    }

    public function claimDaily(): bool
    {
        if (!$this->canClaimDaily()) {
            return false;
        }

        $reward = config('onlyshots.shutter_time.daily_login_reward');
        
        if ($this->user->isActiveMember()) {
            $reward *= config('onlyshots.shutter_time.member_multiplier');
        }

        $this->balance += $reward;
        $this->last_daily_claimed_at = today();
        $this->save();

        return true;
    }

    public function deduct(int $amount): bool
    {
        if ($this->balance < $amount) {
            return false;
        }

        $this->balance -= $amount;
        $this->save();

        return true;
    }
}