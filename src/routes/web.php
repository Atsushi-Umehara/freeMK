<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;

use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\StripeCheckoutController;

/*
|--------------------------------------------------------------------------
| トップページ
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('items.index', ['tab' => 'recommend']);
});

/*
|--------------------------------------------------------------------------
| Stripe Webhook（認証不要）
|--------------------------------------------------------------------------
| Stripeサーバー → Laravelへ POST されるため auth は付けない
| ✅ VerifyCsrfToken の $except に 'stripe/webhook' を追加するのが一般的
*/
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

/*
|--------------------------------------------------------------------------
| 認証不要（誰でも見られる）
|--------------------------------------------------------------------------
*/

// PG01 / PG02 商品一覧（おすすめ / マイリスト）
Route::get('/items', [ItemController::class, 'index'])
    ->name('items.index');

// PG05 商品詳細
Route::get('/item/{item_id}', [ItemController::class, 'show'])
    ->name('items.show');

/*
|--------------------------------------------------------------------------
| 認証必須
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | いいね（Like）
    |--------------------------------------------------------------------------
    */
    Route::post('/items/{item_id}/like', [LikeController::class, 'store'])
        ->name('likes.store');

    Route::delete('/items/{item_id}/like', [LikeController::class, 'destroy'])
        ->name('likes.destroy');

    /*
    |--------------------------------------------------------------------------
    | コメント（FN020）
    |--------------------------------------------------------------------------
    */
    Route::post('/items/{item_id}/comments', [CommentController::class, 'store'])
        ->name('comments.store');

    // コメント削除（自分のコメントだけ削除可：Controller側で判定）
    Route::delete('/comments/{comment_id}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    /*
    |--------------------------------------------------------------------------
    | 商品出品
    |--------------------------------------------------------------------------
    */
    Route::get('/sell', [ItemController::class, 'sell'])
        ->name('items.sell');

    Route::post('/sell', [ItemController::class, 'sellStore'])
        ->name('items.sell.store');

    /*
    |--------------------------------------------------------------------------
    | 商品削除
    |--------------------------------------------------------------------------
    */
    Route::delete('/item/{item_id}', [ItemController::class, 'destroy'])
        ->name('items.destroy');

    /*
    |--------------------------------------------------------------------------
    | PG06 商品購入
    |--------------------------------------------------------------------------
    */
    Route::get('/purchase/{item_id}', [ItemController::class, 'purchase'])
        ->name('items.purchase');

    /*
    |--------------------------------------------------------------------------
    | Stripe Checkout（クレカ決済）
    |--------------------------------------------------------------------------
    | POST: Checkout Session作成 → Stripeへリダイレクト
    | GET : success/cancel は Stripe から戻ってくるURL
    */
    Route::post('/purchase/{item_id}/stripe', [StripeCheckoutController::class, 'checkout'])
        ->name('purchase.stripe');

    Route::get('/purchase/{item_id}/success', [StripeCheckoutController::class, 'success'])
        ->name('purchase.success');

    Route::get('/purchase/{item_id}/cancel', [StripeCheckoutController::class, 'cancel'])
        ->name('purchase.cancel');

    /*
    |--------------------------------------------------------------------------
    | （現状の実装が “擬似購入” / コンビニ決済などなら残してOK）
    |--------------------------------------------------------------------------
    */
    Route::post('/purchase/{item_id}', [ItemController::class, 'purchaseStore'])
        ->name('purchase.store');

    /*
    |--------------------------------------------------------------------------
    | PG07 送付先住所変更
    |--------------------------------------------------------------------------
    */
    Route::get('/purchase/address/{item_id}', [ItemController::class, 'editAddress'])
        ->name('purchase.address');

    Route::post('/purchase/address/{item_id}', [ItemController::class, 'updateAddress'])
        ->name('purchase.address.store');

    /*
    |--------------------------------------------------------------------------
    | マイページ
    |--------------------------------------------------------------------------
    */
    Route::get('/mypage', [ProfileController::class, 'show'])
        ->name('mypage');

    /*
    |--------------------------------------------------------------------------
    | プロフィール編集
    |--------------------------------------------------------------------------
    */
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])
        ->name('mypage.profile.edit');

    Route::post('/mypage/profile', [ProfileController::class, 'update'])
        ->name('mypage.profile.update');
});