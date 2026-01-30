<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Stripe\StripeClient;

class ItemController extends Controller
{
    /**
     * 商品一覧（PG01：おすすめ / PG02：マイリスト）
     * GET /items?tab=recommend|mylist&q=keyword
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'recommend');
        if (!in_array($tab, ['recommend', 'mylist'], true)) {
            $tab = 'recommend';
        }

        $userId = Auth::id();

        // マイリストはログイン必須
        if ($tab === 'mylist' && !$userId) {
            return redirect()->route('login');
        }

        $query = Item::query()
            ->with('user')
            ->orderByDesc('id');

        if ($tab === 'recommend') {
            // 仕様：おすすめは「自分が出品した商品は表示しない」
            if ($userId) {
                $query->where('user_id', '!=', $userId);
            }
        }

        if ($tab === 'mylist') {
            // 仕様：マイリストは「いいねした商品」
            $query->whereIn('id', function ($q) use ($userId) {
                $q->select('item_id')
                    ->from('likes')
                    ->where('user_id', $userId);
            });
        }

        // ===== 検索（FN016：商品名で部分一致）=====
        $word = trim((string) $request->query('q', ''));
        if ($word !== '') {
            $query->where('title', 'like', "%{$word}%");
        }

        $items = $query->get();

        return view('items.index', compact('items', 'tab'));
    }

    /**
     * 商品詳細（PG05）
     * GET /item/{item_id}
     * ★コメントは最新順で表示する
     */
    public function show(int $item_id)
    {
        $item = Item::query()
            ->with('user')
            ->withCount(['likes', 'comments'])
            ->with(['comments' => function ($q) {
                $q->with('user')->orderByDesc('created_at');
            }])
            ->findOrFail($item_id);

        $comments     = $item->comments;
        $commentCount = (int) $item->comments_count;
        $likeCount    = (int) $item->likes_count;

        return view('items.show', compact('item', 'comments', 'commentCount', 'likeCount'));
    }

    /**
     * 商品削除
     * DELETE /item/{item_id}
     */
    public function destroy(int $item_id)
    {
        $item = Item::findOrFail($item_id);

        if ((int) $item->user_id !== (int) Auth::id()) {
            return redirect()
                ->route('items.index')
                ->with('error', '削除できません');
        }

        if (!empty($item->image_path)) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()
            ->route('items.index', ['tab' => 'mylist'])
            ->with('message', '商品を削除しました！');
    }

    /**
     * 商品購入画面（PG06）
     * GET /purchase/{item_id}
     */
    public function purchase(int $item_id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $item = Item::with('user')->findOrFail($item_id);

        // 自分の商品は購入不可
        if ((int) $item->user_id === (int) Auth::id()) {
            return redirect()
                ->route('items.show', ['item_id' => $item->id])
                ->with('error', '自分の商品は購入できません。');
        }

        // 売り切れは購入不可
        if (($item->status ?? '') === 'sold') {
            return redirect()
                ->route('items.show', ['item_id' => $item->id])
                ->with('error', 'この商品は売り切れです。');
        }

        $user = Auth::user();
        $addressData = session()->get("purchase.address.{$item_id}", []);

        return view('items.purchase', compact('item', 'user', 'addressData'));
    }

    /**
     * FN023：支払い方法を選択して「購入する」→ Stripe 決済画面へ
     * POST /purchase/{item_id}
     */
    public function purchaseStore(Request $request, int $item_id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:credit,convenience'],
        ]);

        try {
            return DB::transaction(function () use ($request, $validated, $item_id) {

                // ★トランザクション内でロック（重要）
                $item = Item::where('id', $item_id)->lockForUpdate()->firstOrFail();

                // 自分の商品は購入不可
                if ((int) $item->user_id === (int) Auth::id()) {
                    return redirect()
                        ->route('items.show', ['item_id' => $item->id])
                        ->with('error', '自分の商品は購入できません。');
                }

                // 売り切れは購入不可
                if (($item->status ?? '') === 'sold') {
                    return redirect()
                        ->route('items.show', ['item_id' => $item->id])
                        ->with('error', 'この商品は売り切れです。');
                }

                // 住所：PG07セッション優先 → プロフィール fallback
                $addressData = $request->session()->get("purchase.address.{$item_id}", []);
                $user = Auth::user();

                $postalCode = $addressData['postal_code'] ?? ($user->postal_code ?? null);
                $address    = $addressData['address']     ?? ($user->address ?? null);
                $name       = $addressData['name']        ?? ($user->name ?? null);

                if (!$postalCode || !$address || !$name) {
                    return redirect()
                        ->route('items.purchase', ['item_id' => $item_id])
                        ->with('error', '送付先住所が未設定です。住所を登録してください。');
                }

                // Stripe Checkout を作る
                $stripe = new StripeClient(config('services.stripe.secret'));

                $paymentTypes = $validated['payment_method'] === 'credit'
                    ? ['card']
                    : ['konbini'];

                $successUrl = route('mypage', ['page' => 'buy']) . '?paid=1';
                $cancelUrl  = route('items.purchase', ['item_id' => $item_id]);

                // Checkout作成
                $session = $stripe->checkout->sessions->create([
                    'mode' => 'payment',
                    'payment_method_types' => $paymentTypes,

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

                    'success_url' => $successUrl,
                    'cancel_url'  => $cancelUrl,

                    'metadata' => [
                        'user_id'        => (string) Auth::id(),
                        'item_id'        => (string) $item->id,
                        'price'          => (string) $item->price,
                        'postal_code'    => (string) $postalCode,
                        'address'        => (string) $address,
                        'name'           => (string) $name,
                        'payment_method' => (string) $validated['payment_method'],
                    ],
                ]);

                // ★ここが肝：purchases を “pending” で作って session_id を保存
                // （カラム名はあなたの purchases テーブルに合わせてね）
                Purchase::create([
                    'user_id'            => Auth::id(),
                    'item_id'            => $item->id,
                    'price'              => (int) $item->price,
                    'payment_method'     => $validated['payment_method'],
                    'payment_status'     => 'pending', // 例：pending/paid
                    'stripe_session_id'  => $session->id,
                    // 'stripe_payment_intent_id' => null, // あれば
                ]);

                // 住所セッションはCheckout遷移できた時点で消してOK
                $request->session()->forget("purchase.address.{$item_id}");

                return redirect()->away($session->url);
            });
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('items.purchase', ['item_id' => $item_id])
                ->with('error', '決済の準備に失敗しました。もう一度お試しください。');
        }
    }

    /**
     * 送付先住所変更（PG07）
     * GET /purchase/address/{item_id}
     */
    public function editAddress(int $item_id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $item = Item::findOrFail($item_id);

        $user = Auth::user();
        $addressData = session()->get("purchase.address.{$item_id}", [
            'postal_code' => $user->postal_code ?? '',
            'address'     => $user->address ?? '',
            'name'        => $user->name ?? '',
        ]);

        return view('items.purchase_address', compact('item', 'addressData'));
    }

    /**
     * 送付先住所変更（PG07 保存：セッション）
     * POST /purchase/address/{item_id}
     */
    public function updateAddress(Request $request, int $item_id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'postal_code' => ['required', 'string', 'max:20'],
            'address'     => ['required', 'string', 'max:255'],
            'name'        => ['required', 'string', 'max:100'],
        ]);

        $request->session()->put("purchase.address.{$item_id}", $data);

        return redirect()
            ->route('items.purchase', ['item_id' => $item_id])
            ->with('message', '送付先住所を変更しました。');
    }

    /**
     * 商品出品画面
     * GET /sell
     */
    public function sell()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('items.sell');
    }

    /**
     * 商品出品登録
     * POST /sell
     */
    public function sellStore(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'image'       => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category'    => ['required', 'string', 'max:255'],
            'condition'   => ['required', 'string', 'max:255'],
            'title'       => ['required', 'string', 'max:100'],
            'brand'       => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'price'       => ['required', 'integer', 'min:1'],
        ]);

        $imagePath = $request->file('image')->store('items', 'public');

        Item::create([
            'user_id'     => Auth::id(),
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'price'       => $validated['price'],
            'status'      => 'on_sale',
            'image_path'  => $imagePath,
            'category'    => $validated['category'],
            'condition'   => $validated['condition'],
            'brand'       => $validated['brand'] ?? null,
        ]);

        return redirect()
            ->route('items.index', ['tab' => 'recommend'])
            ->with('message', '商品を出品しました！');
    }
}