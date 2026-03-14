<?php

namespace App\Http\Middleware;

use App\Models\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $userId = auth()->id();
            $cacheKey = 'user-activity-log:' . $userId;

            if (! Cache::has($cacheKey)) {
                UserActivity::create([
                    'user_id' => $userId,
                    'path' => '/' . ltrim($request->path(), '/'),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                    'occurred_at' => now(),
                ]);

                Cache::put($cacheKey, true, now()->addMinutes(5));
            }
        }

        return $next($request);
    }
}
