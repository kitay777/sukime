<?php

namespace App\Http\Controllers;

use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Stripe;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, PointService $svc)
    {
\Log::info('WHSEC_IN_USE', ['prefix' => substr(config('services.stripe.webhook_secret'),0,12)]);
        @file_put_contents(
            storage_path('logs/webhook_tap.log'),
            now()->toDateTimeString() . " HIT len=" . strlen($request->getContent() ?? '') . PHP_EOL,
            FILE_APPEND
        );

        \Log::info('Stripe webhook hit: start');
        $sig = $request->header('Stripe-Signature');
        $secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($request->getContent(), $sig, $secret);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook invalid', ['e' => $e->getMessage()]);
            return response('invalid', 400);
        }
        \Log::info('Stripe webhook hit', ['type' => $event->type]);

        // StripeWebhookController::__invoke

        if ($event->type === 'payment_intent.succeeded') {
            /** @var \Stripe\PaymentIntent $pi */
            $pi = $event->data->object;

            // StripeObject → 配列化して安全に読む
            $meta = $pi->metadata instanceof \Stripe\StripeObject
                ? $pi->metadata->toArray()
                : (array) $pi->metadata;

            \Log::info('PI metadata debug', ['meta' => $meta]); // ← 今はもう見えてる

            $userId = (int)($meta['user_id'] ?? 0);
            $points = (int)($meta['points']  ?? 0);

            if ($userId && $points) {
                \Log::info('PI credit start', ['userId' => $userId, 'points' => $points, 'pi' => $pi->id]);
                $svc->creditOnceByPaymentIntent($userId, $pi->id, $points, ['src' => 'pi']);
                \Log::info('PI credit done', ['pi' => $pi->id]);
            } else {
                \Log::warning('PI succeeded but no metadata', ['pi' => $pi->id, 'meta' => $meta]);
            }
        }


        return response('ok', 200);
    }
}
