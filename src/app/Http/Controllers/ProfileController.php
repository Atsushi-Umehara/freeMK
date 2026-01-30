<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * PG09 マイページ（/mypage）
     * PG11 購入した商品一覧（/mypage?page=buy）
     * PG12 出品した商品一覧（/mypage?page=sell）
     */
    public function show(Request $request)
    {
        $user = Auth::user();

        // page=profile|buy|sell（不正値は profile 扱い）
        $page = (string) $request->query('page', 'profile');
        if (!in_array($page, ['profile', 'buy', 'sell'], true)) {
            $page = 'profile';
        }

        $purchases = null;
        $sellItems = null;

        // PG11: 購入した商品一覧（決済完了のみ）
        if ($page === 'buy') {
            $purchases = Purchase::with(['item.user']) // Purchase → Item → User
                ->where('user_id', $user->id)
                ->where('payment_status', Purchase::STATUS_PAID) // ✅ paid のみ
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString();
        }

        // PG12: 出品した商品一覧
        if ($page === 'sell') {
            $sellItems = Item::with(['user'])
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString();
        }

        return view('mypage.mypage', compact('user', 'page', 'purchases', 'sellItems'));
    }

    /**
     * PG10 プロフィール編集（/mypage/profile）
     */
    public function edit()
    {
        $user = Auth::user();
        return view('mypage.profile', compact('user'));
    }

    /**
     * PG10 更新（/mypage/profile POST）
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // ✅ デバッグ用：画像が送られてきているか確認（不要になったら削除OK）
        \Log::info('profile_image debug', [
            'hasFile'   => $request->hasFile('profile_image'),
            'original'  => $request->file('profile_image')?->getClientOriginalName(),
            'mime'      => $request->file('profile_image')?->getClientMimeType(),
            'size'      => $request->file('profile_image')?->getSize(),
        ]);

        $validated = $request->validate([
            // 表示している項目
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            // 追加プロフィール項目（任意）
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address'     => ['nullable', 'string', 'max:255'],
            'building'    => ['nullable', 'string', 'max:255'],

            // 画像（任意）※ Blade側 name="profile_image" に合わせる
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // パスワード（入力があるときだけ更新）
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // 基本情報
        $user->name        = $validated['name'];
        $user->email       = $validated['email'];
        $user->postal_code = $validated['postal_code'] ?? null;
        $user->address     = $validated['address'] ?? null;
        $user->building    = $validated['building'] ?? null;

        // パスワード更新（入力がある場合だけ）
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // 画像更新（ある場合だけ）
        if ($request->hasFile('profile_image')) {
            // 旧画像があれば削除（任意）
            if (!empty($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // 保存（storage/app/public/profiles/xxx.jpg）
            $path = $request->file('profile_image')->store('profiles', 'public');
            $user->profile_image = $path;
        }

        $user->save();

        return redirect()
            ->route('mypage.profile.edit')
            ->with('message', 'プロフィールを更新しました。');
    }
}