<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeCheckoutController extends Controller
{
    /**
     * Stripe Checkout Session 作成 → Stripe決済画面へリダイレクト
     * POST /purchase/{item_id}/stripe
     */
    public function checkout(Request $request, int $item_id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 支払い方法（画面のselectと合わせる）
        $validated = $request->validate([
            'payment_method' => ['required', 'in:credit,convenience'],
        ]);

        $secret = config('services.stripe.secret');
        if (!$secret) {
            return redirect()
                ->route('items.purchase', ['item_id' => $item_id])
                ->with('error', 'Stripe設定（STRIPE_SECRET）が未設定です。');
        }

        $stripe = new StripeClient($secret);

        try {
            $sessionUrl = DB::transaction(function () use ($request, $item_id, $stripe, $validated) {

                $item = Item::lockForUpdate()->findOrFail($item_id);

                // 自分の商品は購入不可
                if ((int) $item->user_id === (int) Auth::id()) {
                    throw new \RuntimeException('自分の商品は購入できません。');
                }

                // 売り切れは購入不可
                if (($item->status ?? '') === 'sold') {
                    throw new \RuntimeException('この商品は売り切れです。');
                }

                // 住所：PG07セッション優先 → プロフィール fallback
                $addressData = $request->session()->get("purchase.address.{$item_id}", []);
                $user = Auth::user();

                $postalCode = $addressData['postal_code'] ?? ($user->postal_code ?? null);

                // address + building（あれば結合）
                $baseAddress = $addressData['address'] ?? ($user->address ?? null);
                $building    = $user->building ?? null;

                $address = $baseAddress;
                if ($address && $building) {
                    $address = $address . ' ' . $building;
                }

                // 宛名は name（セッション）→ user->name
                $name = $addressData['name'] ?? ($user->name ?? null);

                if (!$postalCode || !$address || !$name) {
                    throw new \RuntimeException('送付先住所が未設定です。住所を登録してください。');
                }

                // 支払い方法の確定
                $paymentMethod = $validated['payment_method']; // 'credit' or 'convenience'

                // Stripe Checkoutの支払い方法（Stripe側で konbini 有効化が必要）
                $paymentMethodTypes = ($paymentMethod === Purchase::METHOD_CREDIT)
                    ? ['card']
                    : ['konbini'];

                // まず Purchase を pending で作る（Webhookで paid に更新する）
                $purchase = Purchase::create([
                    'user_id'        => Auth::id(),
                    'item_id'        => $item->id,
                    'price'          => (int) $item->price,
                    'postal_code'    => (string) $postalCode,
                    'address'        => (string) $address,
                    'name'           => (string) $name,
                    'payment_method' => $paymentMethod,           // ✅ 選択値を保存
                    'payment_status' => Purchase::STATUS_PENDING,
                ]);

                // success/cancel 戻り先（絶対URL）
                $successUrl = route('purchase.success', ['item_id' => $item->id], true);
                $cancelUrl  = route('purchase.cancel',  ['item_id' => $item->id], true);

                // Checkout Session 作成（JPYは円単位）
                $session = $stripe->checkout->sessions->create([
                    'mode' => 'payment',
                    'payment_method_types' => $paymentMethodTypes,

                    'line_items' => [[
                        'quantity' => 1,
                        'price_data' => [
                            'currency' => 'jpy',
                            'unit_amount' => (int) $item->price,
                            'product_data' => [
                                'name' => $item->title,
                            ],
                        ],
                    ]],

                    'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'  => $cancelUrl,

                    // Webhook側で Purchase を特定するキー（超重要）
                    'metadata' => [
                        'purchase_id'    => (string) $purchase->id,
                        'item_id'        => (string) $item->id,
                        'buyer_user_id'  => (string) Auth::id(),
                        'payment_method' => (string) $paymentMethod, // ✅ webhookでも使える
                        'postal_code'    => (string) $postalCode,
                        'address'        => (string) $address,
                        'name'           => (string) $name,
                    ],
                ]);

                // StripeセッションIDを保存（照合用）
                $purchase->update([
                    'stripe_session_id' => $session->id,
                ]);

                Log::info('[StripeCheckout] Checkout session created', [
                    'purchase_id'     => $purchase->id,
                    'session_id'      => $session->id,
                    'item_id'         => $item->id,
                    'user_id'         => Auth::id(),
                    'payment_method'  => $paymentMethod,
                    'payment_methods' => $paymentMethodTypes,
                ]);

                // （任意）住所セッションはCheckout作成できたら消してOK
                $request->session()->forget("purchase.address.{$item_id}");

                return $session->url;
            });

        } catch (\RuntimeException $e) {
            return redirect()
                ->route('items.purchase', ['item_id' => $item_id])
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[StripeCheckout] failed to create checkout session', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('items.purchase', ['item_id' => $item_id])
                ->with('error', '決済の準備に失敗しました。もう一度お試しください。');
        }

        return redirect()->away($sessionUrl);
    }

    /**
     * 決済成功（Stripeから戻ってきた画面）
     * GET /purchase/{item_id}/success
     *
     * ※ DB確定は webhook で行う
     */
    public function success(Request $request, int $item_id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()
            ->route('mypage', ['page' => 'buy'])
            ->with('message', '決済が完了しました！（反映まで少し時間がかかる場合があります）');
    }

    /**
     * 決済キャンセル
     * GET /purchase/{item_id}/cancel
     */
    public function cancel(Request $request, int $item_id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()
            ->route('items.purchase', ['item_id' => $item_id])
            ->with('error', '決済をキャンセルしました。');
    }
}