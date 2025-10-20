<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProfileCompleted
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !$user->profile_completed) {
            // Onboarding系や認証・検証系は除外してループ回避
            if (
                !$request->routeIs('onboarding.*') &&
                !$request->routeIs('verification.*') &&
                !$request->routeIs('password.*') &&
                !$request->routeIs('logout')
            ) {
                return redirect()->route('onboarding.create');
            }
        }

        return $next($request);
    }
}
