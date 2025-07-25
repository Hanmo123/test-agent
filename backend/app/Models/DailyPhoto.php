<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'featured_date',
        'selected_by_user_id',
    ];

    protected $casts = [
        'featured_date' => 'date',
    ];

    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function selectedBy()
    {
        return $this->belongsTo(User::class, 'selected_by_user_id');
    }

    public static function getTodaysFeatured(): ?self
    {
        return static::where('featured_date', today())->first();
    }
}