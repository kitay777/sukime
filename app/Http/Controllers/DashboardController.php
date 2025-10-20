<?php

namespace App\Http\Controllers;

use App\Models\Crush;
use App\Models\Oshi;
use App\Models\Conversation;
use App\Models\User;
use App\Support\CrushMatcher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // 旧方式カラムの存在チェック
use App\Support\CrushMatcher as M;
use App\Http\Controllers\Controller;
use App\Models\PointTransaction;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        // ===== 存在判定用インデックス（name/real_name + school + faculty + grade + gender を正規化してキー化）=====
        $allUsers = User::get(['id','name','real_name','school','faculty','grade','gender']);

        $existIndex = [];
        foreach ($allUsers as $u) {
            foreach (array_filter([$u->name, $u->real_name]) as $nm) {
                $key = implode('|', [
                    M::norm($nm),
                    M::norm($u->school),
                    M::norm($u->faculty),
                    M::norm($u->grade),
                    M::normGender($u->gender),
                ]);
                $existIndex[$key] = true;
            }
        }

        // ===== 好きな人（週1回変更制限）=====
        $crush = Crush::where('user_id', $user->id)->first();
        $canChangeCrush = true;
        $nextChangeDate = null;

        if ($crush && ($crush->last_changed_at ?? $crush->updated_at)) {
            $last = $crush->last_changed_at ?? $crush->updated_at;
            if ($last->gt(now()->subWeek())) {
                $canChangeCrush = false;
                $nextChangeDate = $last->copy()->addWeek()->format('Y-m-d H:i');
            }
        }

        // ===== 相互マッチ時の会話ID（あれば）=====
        $matchConversationId = null;

        if ($crush) {
            // ① 項目一致（推奨）
            $candidates = User::query()
                ->where('id', '!=', $user->id)
                ->get(['id','name','school','faculty','grade','gender'])
                ->filter(fn(User $u) => CrushMatcher::crushEqualsProfile($crush, $u));

            foreach ($candidates as $other) {
                $othersCrush = Crush::where('user_id', $other->id)->first();
                if ($othersCrush && CrushMatcher::crushEqualsProfile($othersCrush, $user)) {
                    $low  = min($user->id, $other->id);
                    $high = max($user->id, $other->id);

                    $conv = Conversation::where('type','private')
                        ->where('user_low_id',$low)
                        ->where('user_high_id',$high)
                        ->first();

                    if (!$conv) {
                        DB::transaction(function () use (&$conv, $low, $high, $user, $other) {
                            $conv = Conversation::firstOrCreate(
                                ['type'=>'private','user_low_id'=>$low,'user_high_id'=>$high],
                                []
                            );
                            $conv->users()->syncWithoutDetaching([$user->id, $other->id]);
                        });
                    }

                    $matchConversationId = $conv?->id;
                    break;
                }
            }

            // ② 後方互換（crush_user_id 相互）
            if (!$matchConversationId && $crush->crush_user_id) {
                $otherId = (int) $crush->crush_user_id;

                $recipro = Crush::where('user_id', $otherId)
                    ->where('crush_user_id', $user->id)
                    ->first();

                if ($recipro) {
                    $low  = min($user->id, $otherId);
                    $high = max($user->id, $otherId);

                    $conv = Conversation::where('type', 'private')
                        ->where('user_low_id', $low)
                        ->where('user_high_id', $high)
                        ->first();

                    $matchConversationId = $conv?->id;
                }
            }
        }

        // ===== 通知（軽量）=====
        $notifications = $user->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($n) => [
                'id'   => $n->id,
                'data' => $n->data,
            ]);

        // ===== 推し一覧 =====
        $oshiList = Oshi::where('user_id', $user->id)
            ->latest()
            ->get(['id','name','school','faculty','grade','gender'])
            ->map(function ($o) use ($existIndex) {
                $key = implode('|', [
                    M::norm($o->name),
                    M::norm($o->school),
                    M::norm($o->faculty),
                    M::norm($o->grade),
                    M::normGender($o->gender),
                ]);
                return array_merge($o->toArray(), [
                    'exists' => isset($existIndex[$key]),
                ]);
            });

        // ===== 好きな人の表示用ペイロード =====
        $crushPayload = $crush ? [
            'name'          => $crush->name,
            'school'        => $crush->school,
            'faculty'       => $crush->faculty,
            'grade'         => $crush->grade,
            'gender'        => $crush->gender,
            'crush_user_id' => $crush->crush_user_id, // 後方互換用
        ] : null;

        // ===== カウント：自分を「好き」と言ってくれてる人数 =====
        $othersCrushes = Crush::where('user_id', '!=', $user->id)
            ->get(['user_id','crush_user_id','name','school','faculty','grade','gender']);

        // ① プロフィール一致
        $fansByProfile = $othersCrushes
            ->filter(fn ($c) => CrushMatcher::crushEqualsProfile($c, $user))
            ->pluck('user_id');

        // ② 旧方式（crush_user_id）
        $fansByLegacy = $othersCrushes
            ->where('crush_user_id', $user->id)
            ->pluck('user_id');

        $fansCount = $fansByProfile->merge($fansByLegacy)->unique()->count();

        // ===== カウント：自分を「推し」にしてくれてる人数 =====
        $othersOshis = Oshi::where('user_id', '!=', $user->id)
            ->get(['user_id','name','school','faculty','grade','gender'] + (Schema::hasColumn('oshis','oshi_user_id') ? ['oshi_user_id'] : []));

        // ① プロフィール一致
        $oshiMeByProfile = $othersOshis
            ->filter(fn ($o) => CrushMatcher::crushEqualsProfile($o, $user))
            ->pluck('user_id');

        // ② 旧方式（oshi_user_id）がある場合のみ
        $oshiMeByLegacy = collect();
        if (Schema::hasColumn('oshis','oshi_user_id')) {
            $oshiMeByLegacy = $othersOshis
                ->where('oshi_user_id', $user->id)
                ->pluck('user_id');
        }

        $oshiMeCount = $oshiMeByProfile->merge($oshiMeByLegacy)->unique()->count();
        $balance = PointTransaction::where('user_id', $user->id)->sum('amount');

        // ===== レンダリング =====
        return Inertia::render('Dashboard', [
            'notifications'         => $notifications,
            'crush'                 => $crushPayload,
            'can_change_crush'      => $canChangeCrush,
            'next_change_date'      => $nextChangeDate,
            'oshi_list'             => $oshiList,
            'match_conversation_id' => $matchConversationId,

            // 追加：人数
            'fans_count'            => $fansCount,    // 自分を好きと言ってくれてる人
            'oshi_me_count'         => $oshiMeCount,  // 自分を推しにしてくれてる人
            'balance'               => $balance,      // 残高
        ]);
    }
}
