<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Stripe Webhook 受信
     * POST /stripe/webhook
     */
    public function handle(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');
        if (!$secret) {
            Log::error('[StripeWebhook] STRIPE_WEBHOOK_SECRET is missing');
            return response('STRIPE_WEBHOOK_SECRET is missing', 500);
        }

        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        if (!$sigHeader) {
            Log::warning('[StripeWebhook] Missing Stripe-Signature header');
            return response('Missing signature header', 400);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('[StripeWebhook] Invalid signature', ['message' => $e->getMessage()]);
            return response('Invalid signature', 400);
        } catch (\UnexpectedValueException $e) {
            Log::warning('[StripeWebhook] Invalid payload', ['message' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (\Throwable $e) {
            Log::error('[StripeWebhook] Unexpected error while constructing event', ['message' => $e->getMessage()]);
            return response('Webhook error', 500);
        }

        // ✅ 対象イベントだけ処理（必要なら増やしてOK）
        if ($event->type !== 'checkout.session.completed') {
            return response('ok', 200);
        }

        $session = $event->data->object;

        $sessionId       = $session->id ?? null;
        $paymentIntentId = $session->payment_intent ?? null;

        // ✅ 重要：metadata は StripeObject なので toArray() で取り出す
        // (array) キャストすると内部構造が混ざって item_id が取れないことがある
        $meta = [];
        if (!empty($session->metadata)) {
            // Stripe\StripeObject の場合
            if (method_exists($session->metadata, 'toArray')) {
                $meta = $session->metadata->toArray();
            } else {
                // 念のため（配列だった場合）
                $meta = (array) $session->metadata;
            }
        }

        $itemId = (int) ($meta['item_id'] ?? 0);

        // ★ buyer_user_id / user_id の両対応
        $buyerUserId = (int) ($meta['buyer_user_id'] ?? ($meta['user_id'] ?? 0));

        // ★ purchase_id が入ってるなら最優先で更新すると安全
        $purchaseId = (int) ($meta['purchase_id'] ?? 0);

        $postalCode    = $meta['postal_code'] ?? null;
        $address       = $meta['address'] ?? null;
        $name          = $meta['name'] ?? null;
        $paymentMethod = $meta['payment_method'] ?? Purchase::METHOD_CREDIT;

        Log::info('[StripeWebhook] checkout.session.completed received', [
            'session_id'        => $sessionId,
            'item_id'           => $itemId,
            'buyer_user_id'     => $buyerUserId,
            'purchase_id'       => $purchaseId,
            'payment_intent_id' => $paymentIntentId,
            'payment_method'    => $paymentMethod,
        ]);

        if (!$sessionId || !$itemId || !$buyerUserId) {
            Log::warning('[StripeWebhook] Missing metadata', [
                'session_id' => $sessionId,
                'metadata'   => $meta,
            ]);
            return response('Missing metadata', 400);
        }

        try {
            DB::transaction(function () use (
                $sessionId,
                $paymentIntentId,
                $itemId,
                $buyerUserId,
                $purchaseId,
                $postalCode,
                $address,
                $name,
                $paymentMethod
            ) {
                // ✅ 対象商品をロックして二重購入を防ぐ
                $item = Item::lockForUpdate()->findOrFail($itemId);

                // ✅ Purchase を特定（purchase_id 優先 → session_id）
                $purchase = null;

                if ($purchaseId) {
                    $purchase = Purchase::lockForUpdate()->find($purchaseId);
                }

                if (!$purchase) {
                    $purchase = Purchase::where('stripe_session_id', $sessionId)
                        ->lockForUpdate()
                        ->first();
                }

                if ($purchase) {
                    // すでに paid なら冪等で終了
                    if (($purchase->payment_status ?? '') !== Purchase::STATUS_PAID) {
                        $purchase->update([
                            'stripe_payment_intent_id' => $paymentIntentId,
                            'payment_status'           => Purchase::STATUS_PAID,
                        ]);

                        Log::info('[StripeWebhook] Purchase updated to PAID.', [
                            'purchase_id' => $purchase->id,
                            'session_id'  => $sessionId,
                        ]);
                    } else {
                        Log::info('[StripeWebhook] Purchase already paid. Skip.', [
                            'purchase_id' => $purchase->id,
                            'session_id'  => $sessionId,
                        ]);
                    }
                } else {
                    // ✅ 無ければ新規作成（metadataの住所を優先）
                    $buyer = User::findOrFail($buyerUserId);

                    $created = Purchase::create([
                        'user_id' => $buyerUserId,
                        'item_id' => $item->id,
                        'price'   => (int) $item->price,

                        'postal_code' => $postalCode ?? ($buyer->postal_code ?? null),
                        'address'     => $address ?? ($buyer->address ?? null),
                        'name'        => $name ?? ($buyer->name ?? null),

                        'payment_method'           => $paymentMethod,
                        'stripe_session_id'        => $sessionId,
                        'stripe_payment_intent_id' => $paymentIntentId,
                        'payment_status'           => Purchase::STATUS_PAID,
                    ]);

                    Log::info('[StripeWebhook] Purchase created as PAID.', [
                        'purchase_id'   => $created->id,
                        'session_id'    => $sessionId,
                        'item_id'       => $item->id,
                        'buyer_user_id' => $buyerUserId,
                    ]);
                }

                // ✅ Item を sold に（すでに sold でもOK）
                if (($item->status ?? '') !== 'sold') {
                    $item->update(['status' => 'sold']);

                    Log::info('[StripeWebhook] Item updated to SOLD.', [
                        'item_id'    => $item->id,
                        'session_id' => $sessionId,
                    ]);
                } else {
                    Log::info('[StripeWebhook] Item already sold. Skip.', [
                        'item_id'    => $item->id,
                        'session_id' => $sessionId,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('[StripeWebhook] failed to finalize purchase', [
                'message'       => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
                'session_id'    => $sessionId,
                'item_id'       => $itemId,
                'buyer_user_id' => $buyerUserId,
            ]);

            // Stripeに失敗を返す → リトライされる（正しい挙動）
            return response('Webhook handling failed', 500);
        }

        return response('ok', 200);
    }
}