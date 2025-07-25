<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserLevelMiddleware
{
    public function handle(Request $request, Closure $next, int $minLevel = 1)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        if ($user->level < $minLevel) {
            return response()->json([
                'message' => "Level {$minLevel}+ required",
                'current_level' => $user->level,
                'required_level' => $minLevel,
            ], 403);
        }

        return $next($request);
    }
}