<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;

class SupportController extends Controller
{
    private const AMOUNT = 500;  // 応援の金額（円）。今週は定額にする

    // 「応援する」ボタン → Stripe の決済ページ（Checkout）へ移動する
    public function create(Post $post)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = Session::create([
                // ※ 教材の 'payment_method_types' => ['card'] は、新しい Stripe API では使えない
                //   （支払い方法はダッシュボードの設定で管理する方式に変わった）ので書かない
                'line_items' => [[
                    'price_data' => [
                        'currency'     => 'jpy',
                        'product_data' => ['name' => '「' . $post->title . '」の書き手を応援'],
                        'unit_amount'  => self::AMOUNT,
                    ],
                    'quantity' => 1,
                ]],
                'mode'        => 'payment',
                // 決済後に「どの記事を・誰が応援したか」が分かるように Stripe に預けておく
                'metadata'    => ['post_id' => $post->id, 'user_id' => auth()->id()],
                'success_url' => route('supports.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('posts.show', $post),
            ]);
        } catch (AuthenticationException $e) {
            // APIキーが不正（.env の STRIPE_SECRET を確認）
            Log::error('Stripe Authentication: ' . $e->getMessage());
            return back()->withErrors(['payment' => '決済の設定に問題があります']);
        } catch (InvalidRequestException $e) {
            // パラメータ不正（コード側のバグの可能性）
            Log::error('Stripe InvalidRequest: ' . $e->getMessage());
            return back()->withErrors(['payment' => '決済リクエストが不正です']);
        } catch (ApiConnectionException $e) {
            // Stripe への接続失敗（一時的なネットワーク障害）
            Log::error('Stripe ApiConnection: ' . $e->getMessage());
            return back()->withErrors(['payment' => '通信エラーが発生しました。時間をおいて再試行してください']);
        } catch (ApiErrorException $e) {
            // その他の Stripe のエラー
            Log::error('Stripe Error: ' . $e->getMessage());
            return back()->withErrors(['payment' => '決済の準備中にエラーが発生しました']);
        }

        return redirect($session->url);
    }

    // 決済が終わったら Stripe からここに戻ってくる
    public function success(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // URL の session_id から、Stripe に決済の結果を問い合わせる
            $session = Session::retrieve((string) $request->query('session_id'));
        } catch (ApiErrorException $e) {
            Log::error('Stripe Retrieve: ' . $e->getMessage());
            return redirect()->route('posts.index')->withErrors(['payment' => '決済の確認に失敗しました']);
        }

        // 決済が終わっていない、または別の人の決済なら保存しない
        if ($session->payment_status !== 'paid' || (int) $session->metadata->user_id !== auth()->id()) {
            return redirect()->route('posts.index')->withErrors(['payment' => '決済が完了していません']);
        }

        // 応援した履歴を保存する（同じ session_id なら二重に保存しない）
        $support = Support::firstOrCreate(
            ['stripe_session_id' => $session->id],
            [
                'user_id' => $session->metadata->user_id,
                'post_id' => $session->metadata->post_id,
                'amount'  => $session->amount_total,  // 円の場合は、この値がそのまま金額（円）
            ]
        );

        return view('supports.success', ['support' => $support->load('post')]);
    }
}
