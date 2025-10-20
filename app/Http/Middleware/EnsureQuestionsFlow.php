<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserQuestionAnswer;
use Illuminate\Support\Facades\Auth;

class EnsureQuestionsFlow
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            // まだ1問も答えていない → 初回10問へ
            $answeredCount = UserQuestionAnswer::where('user_id', $user->id)->count();
            if ($answeredCount === 0 && !$request->routeIs('questions.initial')) {
                return redirect()->route('questions.initial');
            }

            // 初回が終わっている場合 → 日次チェックも可能
            // 今日は答えてないなら daily に誘導したい場合ここに条件追加
        }

        return $next($request);
    }
}
