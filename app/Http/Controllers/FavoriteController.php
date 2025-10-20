<?php

// app/Http/Controllers/FavoriteController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\User;

class FavoriteController extends Controller
{
    public function create(Request $request)
    {
        return Inertia::render('Favorite/Create', [
            // 全ユーザー一覧（自分自身は除外）
            'users' => User::where('id', '!=', $request->user()->id)
                ->select('id','name','real_name')
                ->get(),
            'favorite_user_id' => $request->user()->favorite_user_id,
            'favorite_set_at'  => $request->user()->favorite_set_at,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // すでに設定済み & 1週間経ってないなら拒否
        if ($user->favorite_user_id && $user->favorite_set_at && $user->favorite_set_at->gt(now()->subWeek())) {
            return back()->withErrors([
                'favorite_user_id' => '好きな人は1週間に1回しか変更できません。',
            ]);
        }

        $validated = $request->validate([
            'favorite_user_id' => ['required','exists:users,id','not_in:'.$user->id],
        ]);

        $user->update([
            'favorite_user_id' => $validated['favorite_user_id'],
            'favorite_set_at'  => now(),
        ]);

        return redirect()->intended(route('dashboard'));
    }
}
