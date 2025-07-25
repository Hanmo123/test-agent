<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyPhoto;
use Illuminate\Http\Request;

class DailyPhotoController extends Controller
{
    public function today()
    {
        $dailyPhoto = DailyPhoto::getTodaysFeatured();
        
        if (!$dailyPhoto) {
            return response()->json(['message' => 'No daily photo selected'], 404);
        }
        
        $dailyPhoto->load([
            'work.user',
            'work.coverResource',
            'work.resources',
            'selectedBy'
        ]);
        
        return response()->json($dailyPhoto);
    }
}