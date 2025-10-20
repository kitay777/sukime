<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Oshi;
use App\Models\UserGacha;
use App\Models\PointTransaction;
use App\Models\Question;
use App\Models\UserQuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Support\CrushMatcher;
use App\Notifications\MutualLoveNotification;

class GachaController extends Controller
{
    public function play(Request $request)
    {
        $user    = $request->user();
        $isPaid  = $request->boolean('paid');
        $oshiId  = $request->input('oshi_id'); // ★ 推しごとのガチャ用
        $scope   = $request->input('scope');   // 任意（ログ用途）

        // 残高
        $balance = (int) PointTransaction::where('user_id', $user->id)->sum('amount');

        // 無料（日次1回）
        if (!$isPaid) {
            $todayPlayed = UserGacha::where('user_id', $user->id)
                ->whereDate('gacha_date', today())
                ->exists();
            if ($todayPlayed) {
                return redirect()->route('dashboard')->with('error', '今日はもうガチャしました！');
            }
        }

        // 有料（100pt）
        if ($isPaid) {
            $cost = 100;
            if ($balance < $cost) {
                return redirect()->route('points.dashboard')
                    ->with('error', 'ポイントが不足しています（100pt必要です）');
            }
            DB::transaction(function () use ($user, $cost) {
                PointTransaction::create([
                    'user_id' => $user->id,
                    'amount'  => -$cost,
                    'type'    => 'spend',
                    'reason'  => 'gacha_paid',
                ]);
            });
            $balance -= $cost;
        }

        // ===== ガチャ抽選 =====
        $rarity   = $this->pickRarity();
        $isWin    = false;
        $favorite = null;

        if ($rarity === 'secret' && $user->favorite_user_id) {
            $favorite = User::find($user->favorite_user_id);
            if ($favorite && $favorite->favorite_user_id === $user->id) {
                $isWin = true;
                $favorite->notify(new MutualLoveNotification($user));
            }
        }

        UserGacha::create([
            'user_id'    => $user->id,
            'gacha_date' => today(),
            'rarity'     => $rarity,
            'is_win'     => $isWin,
        ]);

        // ===== 対象ユーザー特定（推しごとガチャ）=====
        $targetUser = null;
        if ($oshiId) {
            $oshi = Oshi::where('user_id', $user->id)->find($oshiId); // 権限ガード
            if ($oshi) {
                // 自分以外でプロフィール一致する候補から1人（複数ならランダム）
                $candidates = User::query()
                    ->where('id', '!=', $user->id)
                    ->get(['id','name','school','faculty','grade','gender'])
                    ->filter(fn(User $u) => CrushMatcher::crushEqualsProfile($oshi, $u));

                if ($candidates->count() > 0) {
                    $targetUser = $candidates->random(); // ランダム1人
                }
            }
        }

        // ===== レア度に応じた質問 → 回答抽選 =====
        $question   = $this->pickQuestionByRarity($rarity);
        $answerText = null;
        $answerFrom = null;

        if ($question) {
            // ① 推しターゲットの回答を最優先
            if ($targetUser) {
                $ans = UserQuestionAnswer::where('user_id', $targetUser->id)
                    ->where('question_id', $question->id)
                    ->first();
                if ($ans) {
                    $answerText = $ans->answer;
                    $answerFrom = $targetUser;
                }
            }
            // ② favorite の回答
            if (!$answerText && $user->favorite_user_id) {
                $favAns = UserQuestionAnswer::where('user_id', $user->favorite_user_id)
                    ->where('question_id', $question->id)
                    ->first();
                if ($favAns) {
                    $answerText = $favAns->answer;
                    $answerFrom = User::find($favAns->user_id);
                }
            }
            // ③ ランダム他ユーザー回答
            if (!$answerText) {
                $randAns = UserQuestionAnswer::where('question_id', $question->id)
                    ->where('user_id', '!=', $user->id)
                    ->inRandomOrder()
                    ->first();
                if ($randAns) {
                    $answerText = $randAns->answer;
                    $answerFrom = User::find($randAns->user_id);
                }
            }
        }

        return Inertia::render('Gacha/Result', [
            'rarity'   => $rarity,
            'isWin'    => $isWin,
            'favorite' => $favorite ? ['id'=>$favorite->id,'name'=>$favorite->name] : null,
            'balance'  => $balance,
            // Q&A
            'qa'       => $question ? [
                'question_id'   => $question->id,
                'question_text' => $question->content,  // ← columns: content
                'answer'        => $answerText,         // ← columns: answer
                'answer_user'   => $answerFrom ? ['id'=>$answerFrom->id,'name'=>$answerFrom->name] : null,
            ] : null,
            // 画面表示用：対象推し/ユーザー
            'target'   => $targetUser ? ['id'=>$targetUser->id,'name'=>$targetUser->name] : null,
        ]);
    }

    private function pickRarity(): string
    {
        $rarities = ['normal'=>60,'rare'=>25,'super_rare'=>10,'ultra_rare'=>4,'secret'=>1];
        $total = array_sum($rarities);
        $rand  = mt_rand(1, $total);
        $cum   = 0;
        foreach ($rarities as $r => $w) { $cum += $w; if ($rand <= $cum) return $r; }
        return 'normal';
    }

    private function pickQuestionByRarity(string $rarity): ?Question
    {
        $tiers = match ($rarity) {
            'secret'     => ['secret','ultra_rare','super_rare','rare','normal'],
            'ultra_rare' => ['ultra_rare','super_rare','rare','normal'],
            'super_rare' => ['super_rare','rare','normal'],
            'rare'       => ['rare','normal'],
            default      => ['normal'],
        };

        foreach ($tiers as $tier) {
            $q = Question::where('is_active', 1)
                ->where('rarity', $tier)
                ->inRandomOrder()
                ->first();
            if ($q) return $q;
        }
        return null;
    }
}
