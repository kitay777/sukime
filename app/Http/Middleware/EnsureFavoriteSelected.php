<?php

// app/Http/Middleware/EnsureFavoriteSelected.php
// app/Http/Middleware/EnsureFavoriteSelected.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureFavoriteSelected
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->profile_completed && !$user->favorite_user_id) {
            if (
                !$request->routeIs('favorite.*') &&
                !$request->routeIs('logout')
            ) {
                return redirect()->route('favorite.create');
            }
        }

        return $next($request);
    }
}


