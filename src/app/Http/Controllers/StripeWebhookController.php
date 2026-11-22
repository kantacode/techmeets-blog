<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            // 署名を検証して、本当に Stripe から来たリクエストか確かめる
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            // 署名が不正 → 偽のリクエストを拒否
            Log::warning('Stripe Webhook 署名不正: ' . $e->getMessage());
            return response('Invalid signature', 400);
        } catch (UnexpectedValueException $e) {
            // JSON として読めない
            return response('Invalid payload', 400);
        }

        // イベントの種類に応じて処理を分岐
        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            Log::info('決済完了: ' . $paymentIntent->id . '（' . $paymentIntent->amount . ' ' . $paymentIntent->currency . '）');
        }

        return response('OK', 200);
    }
}
