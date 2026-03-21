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
     * Actualiza last_seen_at en la sesión abierta cada 5 minutos.
     * Sirve para detectar sesiones abandonadas (browser cerrado sin logout).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $userId = auth()->id();
            $cacheKey = 'user-heartbeat:' . $userId;

            if (! Cache::has($cacheKey)) {
                UserActivity::where('user_id', $userId)
                    ->whereNull('logged_out_at')
                    ->latest('logged_in_at')
                    ->first()
                    ?->update(['last_seen_at' => now()]);

                Cache::put($cacheKey, true, now()->addMinutes(5));
            }
        }

        return $next($request);
    }
}
