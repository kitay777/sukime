<?php

namespace App\Http\Controllers;

use App\Services\PointService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PointController extends Controller
{
    public function dashboard(Request $req, PointService $svc)
    {
        $user = $req->user();

        $balance = $svc->getBalance($user->id);
        $history = \App\Models\PointTransaction::where('user_id',$user->id)
                    ->latest()->limit(30)->get(['id','amount','type','reason','created_at']);

        return Inertia::render('Points/Dashboard', [
            'balance' => $balance,
            'history' => $history,
            'publishable_key' => config('services.stripe.key'),
        ]);
    }

    /** Checkout セッション生成（プリセット額 or 入力額をポイント化） */
// PointController@createCheckoutSession
    public function createCheckoutSession(Request $req)
    {
        $req->validate([
            'amount_yen' => ['required','integer','min:100','max:50000'],
        ]);

        try {
            $user = $req->user();
            $amountYen = (int) $req->input('amount_yen');
            $points    = (int) ($amountYen * (int)env('POINT_YEN_TO_POINTS',1));

            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            // PointController@createCheckoutSession

            // PointController@createCheckoutSession
            $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                'currency' => 'jpy',
                'product_data' => ['name' => 'ポイントチャージ'],
                'unit_amount' => $amountYen,
                ],
                'quantity' => 1,
            ]],
            'success_url' => config('app.url').'/points/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => config('app.url').'/points/cancel',

            // ★ PI にメタを付与（これが要）
            'payment_intent_data' => [
                'metadata' => [
                'user_id' => (string) $user->id,
                'points'  => (string) $points,
                ],
            ],

            // （保険）セッション側にも入れておく
            'metadata' => [
                'user_id' => (string) $user->id,
                'points'  => (string) $points,
            ],
            'customer_email' => $user->email,
            ]);
\Stripe\Stripe::setApiKey(config('services.stripe.secret'));
$acct = \Stripe\Account::retrieve();

\Log::info('STRIPE_RUNTIME', [
    'account_id' => $acct->id,             // 例: acct_123
    'livemode'   => (bool)$session->livemode, // falseならテストモード
    'session_id' => $session->id,          // cs_test_...
]);
            return response()->json(['url' => $session->url]);
        } catch (\Throwable $e) {
            \Log::error('Stripe checkout create error', ['e' => $e->getMessage()]);
            return response()->json(['error' => 'checkout_failed'], 500);
        }
    }


    public function success()
    {
        // UIだけ（実付与は webhook で行う）
        return Inertia::render('Points/Success');
    }

    public function cancel()
    {
        return Inertia::render('Points/Cancel');
    }
}
