<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * いいねする
     */
    public function store(int $item_id)
    {
        $item = Item::findOrFail($item_id);

        // 自分の商品はいいねできない（任意：要件に合わせて外してOK）
        if ($item->user_id === Auth::id()) {
            return back()->with('error', '自分の商品にはいいねできません。');
        }

        Like::firstOrCreate([
            'user_id' => Auth::id(),
            'item_id' => $item_id,
        ]);

        return back()->with('message', 'いいねしました。');
    }

    /**
     * いいね解除
     */
    public function destroy(int $item_id)
    {
        Like::where('user_id', Auth::id())
            ->where('item_id', $item_id)
            ->delete();

        return back()->with('message', 'いいねを解除しました。');
    }
}