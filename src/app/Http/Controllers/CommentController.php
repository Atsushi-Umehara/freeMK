<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * コメント投稿（FN020）
     * POST /items/{item_id}/comments
     */
    public function store(CommentRequest $request, int $item_id)
    {
        // auth ミドルウェア前提だが保険
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 商品の存在確認
        $item = Item::findOrFail($item_id);

        // 売り切れはコメント不可（要件に合わせてON）
        if (($item->status ?? '') === 'sold') {
            return redirect()
                ->route('items.show', ['item_id' => $item->id])
                ->with('error', '売り切れの商品にはコメントできません。');
        }

        // validated & trim 済み（CommentRequest側でprepareForValidationしてる前提）
        $body = $request->validated()['body'];
        $userId = (int) Auth::id();

        // 保存（任意：10秒以内の同文連投を弾く）
        DB::transaction(function () use ($item, $userId, $body) {
            $exists = Comment::where('user_id', $userId)
                ->where('item_id', $item->id)
                ->where('body', $body)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->exists();

            if ($exists) {
                return;
            }

            Comment::create([
                'user_id' => $userId,
                'item_id' => $item->id,
                'body'    => $body,
            ]);
        });

        return redirect()
            ->route('items.show', ['item_id' => $item->id])
            ->with('message', 'コメントを送信しました！');
    }

    /**
     * コメント削除（自分のコメントだけ）
     * DELETE /comments/{comment_id}
     */
    public function destroy(int $comment_id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $comment = Comment::findOrFail($comment_id);

        // 自分のコメント以外は削除不可
        if ((int) $comment->user_id !== (int) Auth::id()) {
            return redirect()
                ->route('items.show', ['item_id' => $comment->item_id])
                ->with('error', '削除できません。');
        }

        $itemId = (int) $comment->item_id;

        $comment->delete();

        return redirect()
            ->route('items.show', ['item_id' => $itemId])
            ->with('message', 'コメントを削除しました！');
    }
}