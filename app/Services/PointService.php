<?php

namespace App\Services;

use App\Models\PointCredit;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;

class PointService
{
    public function getBalance(int $userId): int
    {
        return (int) PointTransaction::where('user_id', $userId)->sum('amount');
    }

    /** StripeのPIに対して一度だけ加算（冪等） */
    public function creditOnceByPaymentIntent(int $userId, string $pi, int $points, array $meta = []): void
    {
        DB::transaction(function () use ($userId, $pi, $points, $meta) {
            // 既に処理済みなら何もしない
            if (PointCredit::where('payment_intent_id', $pi)->exists()) {
                return;
            }
            PointCredit::create([
                'payment_intent_id' => $pi,
                'user_id' => $userId,
                'amount_points' => $points,
            ]);
            PointTransaction::create([
                'user_id' => $userId,
                'amount'  => $points, // 加算
                'type'    => 'purchase',
                'reason'  => 'Stripe purchase',
                'meta'    => array_merge($meta, ['payment_intent_id' => $pi]),
            ]);
        });
    }

    /** 消費（足りなければ false） */
    public function spend(int $userId, int $points, string $reason, array $meta = []): bool
    {
        return DB::transaction(function () use ($userId, $points, $reason, $meta) {
            $balance = $this->getBalance($userId);
            if ($balance < $points) return false;

            PointTransaction::create([
                'user_id' => $userId,
                'amount'  => -$points,
                'type'    => 'spend',
                'reason'  => $reason,
                'meta'    => $meta,
            ]);
            return true;
        });
    }
}
