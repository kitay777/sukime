<?php

namespace App\Http\Controllers;

use App\Http\Requests\CrushStoreRequest;
use App\Models\Crush;
use App\Models\User;
use App\Models\Conversation;
use App\Support\CrushMatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CrushController extends Controller
{
    public function store(CrushStoreRequest $request): RedirectResponse
    {
        $user = $request->user();
        $now  = now();

        // 既存レコード取得
        $crush = Crush::firstOrNew(['user_id' => $user->id]);

        // ---- 週1回変更制限 ----
        $last = $crush->last_changed_at ?? $crush->updated_at;
        if ($last && $last->gt($now->copy()->subWeek())) {
            $next = $last->copy()->addWeek()->format('Y-m-d H:i');
            return back(303)->withErrors([
                'limit' => "好きな人は週1回だけ変更できます。次回は {$next} 以降に変更可能です。",
            ])->withInput();
        }

        // ---- 入力反映＆保存 ----
        $crush->fill([
            'name'    => $request->string('name'),
            'school'  => $request->input('school'),
            'faculty' => $request->input('faculty'),
            'grade'   => $request->input('grade'),
            'gender'  => $request->input('gender'),
        ]);

        // 後方互換：UIで指定があれば保持（使わない運用でもOK）
        if ($request->filled('crush_user_id')) {
            $crush->crush_user_id = (int) $request->input('crush_user_id');
        }

        $crush->user_id         = $user->id;
        $crush->last_changed_at = $now;
        $crush->save();

        // ==========================
        // 相思相愛チェック & 会話作成
        // ==========================

        // 1) まず「ID相互参照」での成立（後方互換）
        $matchedUserIds = [];

        if (!empty($crush->crush_user_id)) {
            $otherId = (int) $crush->crush_user_id;
            if ($otherId !== $user->id) {
                $recipro = Crush::where('user_id', $otherId)
                    ->where('crush_user_id', $user->id)
                    ->first();
                if ($recipro) {
                    $matchedUserIds[] = $otherId;
                }
            }
        }

        // 2) テキスト項目一致での成立（あなたの要件）
        //    A.crush == B.profile かつ B.crush == A.profile の両方成立
        //    まず A.crush に一致する B を候補として DB から絞る
        $candidates = User::query()
            ->where('id', '!=', $user->id)
            ->where('name',    $crush->name)
            ->where('school',  $crush->school)
            ->where('faculty', $crush->faculty)
            ->where('grade',   $crush->grade)
            ->where('gender',  $crush->gender)
            ->get(['id','name','school','faculty','grade','gender']);

        foreach ($candidates as $other) {
            // B の crush が A のプロフィールに一致するか
            $othersCrush = Crush::where('user_id', $other->id)->first();
            if (!$othersCrush) {
                continue;
            }
            if (CrushMatcher::crushEqualsProfile($othersCrush, $user)) {
                $matchedUserIds[] = $other->id;
            }
        }

        // 重複除去
        $matchedUserIds = array_values(array_unique($matchedUserIds));

        // 成立した相手それぞれに 1:1 会話を用意（既存があれば使う）
        foreach ($matchedUserIds as $otherId) {
            $low  = min($user->id, $otherId);
            $high = max($user->id, $otherId);

            DB::transaction(function () use ($low, $high, $user, $otherId) {
                $conv = Conversation::firstOrCreate(
                    ['type' => 'private', 'user_low_id' => $low, 'user_high_id' => $high],
                    []
                );
                $conv->users()->syncWithoutDetaching([$user->id, $otherId]);
            });
        }

        return back(303)->with('success', '好きな人を登録/更新しました。');
    }
}
