<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function profile(User $user, Request $request)
    {
        $currentUser = $request->user();
        
        // Check if profile can be viewed
        if ($user->visibility === 'private' && (!$currentUser || $currentUser->id !== $user->id)) {
            return response()->json(['message' => 'Profile is private'], 403);
        }
        
        if ($user->visibility === 'followers_only' && $currentUser && $currentUser->id !== $user->id) {
            $isFollowing = $currentUser->following()
                ->where('followed_user_id', $user->id)
                ->exists();
                
            if (!$isFollowing) {
                return response()->json(['message' => 'Profile is for followers only'], 403);
            }
        }
        
        // Load relationships and stats
        $user->load(['relationships']);
        
        // Add follow status if current user is logged in
        if ($currentUser && $currentUser->id !== $user->id) {
            $user->is_following = $currentUser->following()
                ->where('followed_user_id', $user->id)
                ->exists();
        }
        
        // Get recent works
        $recentWorks = $user->works()
            ->published()
            ->with(['coverResource', 'resources'])
            ->orderBy('published_at', 'desc')
            ->limit(12)
            ->get();
        
        return response()->json([
            'user' => $user,
            'recent_works' => $recentWorks,
            'stats' => [
                'works_count' => $user->works()->published()->count(),
                'followers_count' => $user->followers_count,
                'following_count' => $user->following_count,
                'likes_received' => $user->likes_received,
            ]
        ]);
    }
    
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'nickname' => 'sometimes|required|string|max:50',
            'avatar' => 'sometimes|nullable|url',
            'visibility' => ['sometimes', 'required', Rule::in(['public', 'followers_only', 'private'])],
        ]);
        
        $user->update($request->only(['nickname', 'avatar', 'visibility']));
        
        return response()->json([
            'user' => $user,
            'message' => 'Profile updated successfully'
        ]);
    }
    
    public function updatePreferences(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'preferences' => 'required|array',
            'preferences.default_watermark' => 'boolean',
            'preferences.theme' => 'in:light,dark',
            'preferences.notifications' => 'in:realtime,daily,disabled',
        ]);
        
        $preferences = array_merge($user->preferences ?? [], $request->preferences);
        $user->update(['preferences' => $preferences]);
        
        return response()->json([
            'preferences' => $preferences,
            'message' => 'Preferences updated successfully'
        ]);
    }
    
    public function follow(User $user, Request $request)
    {
        $currentUser = $request->user();
        
        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'Cannot follow yourself'], 400);
        }
        
        // Check follow limit
        $followingCount = $currentUser->following()->count();
        if ($followingCount >= config('onlyshots.limits.max_follows')) {
            return response()->json(['message' => 'Follow limit reached'], 429);
        }
        
        $follow = Follow::firstOrCreate([
            'follower_user_id' => $currentUser->id,
            'followed_user_id' => $user->id,
        ]);
        
        if ($follow->wasRecentlyCreated) {
            $user->increment('followers_count');
            $currentUser->increment('following_count');
        }
        
        return response()->json(['message' => 'Followed successfully']);
    }
    
    public function unfollow(User $user, Request $request)
    {
        $currentUser = $request->user();
        
        $follow = Follow::where('follower_user_id', $currentUser->id)
            ->where('followed_user_id', $user->id)
            ->first();
        
        if ($follow) {
            $follow->delete();
            $user->decrement('followers_count');
            $currentUser->decrement('following_count');
        }
        
        return response()->json(['message' => 'Unfollowed successfully']);
    }
    
    public function claimDailyShutterTime(Request $request)
    {
        $user = $request->user();
        $shutterTime = $user->shutterTime;
        
        if (!$shutterTime) {
            return response()->json(['message' => 'Shutter time record not found'], 404);
        }
        
        if ($shutterTime->claimDaily()) {
            return response()->json([
                'message' => 'Daily shutter time claimed successfully',
                'balance' => $shutterTime->balance,
                'claimed_amount' => $user->isActiveMember() ? 
                    config('onlyshots.shutter_time.daily_login_reward') * config('onlyshots.shutter_time.member_multiplier') :
                    config('onlyshots.shutter_time.daily_login_reward')
            ]);
        } else {
            return response()->json(['message' => 'Already claimed today'], 400);
        }
    }
}