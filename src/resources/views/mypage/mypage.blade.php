@extends('layouts.app')

@push('styles')
<style>
    /* ===== Mypage ===== */
    .container{max-width:900px;margin:16px auto 60px;padding:0 14px;}
    .card{background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.06);padding:18px;}

    .profile{display:flex;align-items:center;gap:14px;}
    .avatar{width:64px;height:64px;border-radius:50%;background:#eee;display:grid;place-items:center;font-weight:800;color:#777;flex:0 0 auto;}
    .name{font-size:18px;font-weight:800;margin:0 0 4px;}
    .email{font-size:13px;color:#666;margin:0;word-break:break-all;}

    .msg{margin:12px 0;padding:10px 12px;border-radius:8px;font-size:13px;}
    .msg-ok{background:#eaffea;color:#1b6b1b;}
    .msg-ng{background:#ffecec;color:#c00;}

    .tabs{display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;}
    .tab{display:inline-block;text-decoration:none;padding:10px 14px;border-radius:20px;font-size:13px;font-weight:700;border:1px solid #ddd;background:#fff;color:#222;}
    .tab-active{background:var(--accent);color:#fff;border:none;}

    .actions{margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;}
    .btn{padding:10px 14px;border-radius:20px;font-size:13px;font-weight:700;text-decoration:none;border:1px solid #ddd;color:#222;background:#fff;}
    .btn-primary{background:var(--accent);color:#fff;border:none;}

    .menu{margin-top:16px;}
    .menu a{display:flex;justify-content:space-between;padding:14px;text-decoration:none;color:#222;border-top:1px solid #eee;}
    .menu a:hover{background:#fafafa;}

    .list{margin-top:16px;display:grid;gap:12px;}
    .row{display:flex;gap:12px;align-items:center;border:1px solid #eee;border-radius:10px;padding:12px;background:#fff;}
    .thumb{width:72px;height:72px;border-radius:8px;background:#f2f2f2;display:block;object-fit:cover;flex:0 0 auto;}
    .meta{flex:1;min-width:0;}
    .meta .t{font-weight:800;margin:0 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .meta .s{font-size:12px;color:#666;margin:0;}
    .price{font-weight:800;color:#e60033;white-space:nowrap;}

    .badge{display:inline-block;font-size:11px;font-weight:800;padding:4px 8px;border-radius:999px;margin-left:8px;background:#eee;color:#555;}
    .badge-sold{background:#ffecec;color:#c00;}

    .note{font-size:12px;color:#777;margin-top:10px;}

    .pager{margin-top:14px;}
    .pager a, .pager span{display:inline-block;margin-right:6px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;text-decoration:none;color:#222;font-size:13px;}
    .pager .current{background:var(--accent);color:#fff;border:none;}
</style>
@endpush

@section('content')

<div class="container">
    <div class="card">

        {{-- フラッシュ --}}
        @if (session('message'))
            <div class="msg msg-ok">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="msg msg-ng">{{ session('error') }}</div>
        @endif

        {{-- プロフィール --}}
        <div class="profile">
            <div class="avatar">{{ mb_substr($user->name ?? 'U', 0, 1) }}</div>
            <div style="min-width:0;">
                <p class="name">{{ $user->name }}</p>
                <p class="email">{{ $user->email }}</p>
            </div>
        </div>

        {{-- タブ --}}
        <div class="tabs">
            <a class="tab {{ ($page ?? 'profile') === 'profile' ? 'tab-active' : '' }}" href="{{ route('mypage') }}">プロフィール</a>
            <a class="tab {{ ($page ?? 'profile') === 'buy' ? 'tab-active' : '' }}" href="{{ route('mypage', ['page' => 'buy']) }}">購入した商品</a>
            <a class="tab {{ ($page ?? 'profile') === 'sell' ? 'tab-active' : '' }}" href="{{ route('mypage', ['page' => 'sell']) }}">出品した商品</a>
            <a class="tab" href="{{ route('mypage.profile.edit') }}">設定（編集）</a>
        </div>

        {{-- 主要導線 --}}
        <div class="actions">
            <a class="btn btn-primary" href="{{ route('items.sell') }}">出品する</a>
            <a class="btn" href="{{ route('items.index', ['tab'=>'mylist']) }}">マイリスト</a>
            <a class="btn" href="{{ route('items.index', ['tab'=>'all']) }}">商品一覧</a>
        </div>

        {{-- PG11：購入した商品一覧 --}}
        @if (($page ?? 'profile') === 'buy')
            <div class="list">
                @forelse (($purchases ?? []) as $purchase)
                    <div class="row">
                        @php
                            $img = $purchase->item->image_path ?? null;
                        @endphp

                        @if ($img)
                            <img class="thumb" src="{{ asset('storage/' . $img) }}" alt="商品画像">
                        @else
                            <img class="thumb" src="https://via.placeholder.com/72?text=No" alt="No Image">
                        @endif

                        <div class="meta">
                            <p class="t">
                                {{ $purchase->item->title ?? '（商品が見つかりません）' }}
                            </p>
                            <p class="s">
                                出品者：{{ $purchase->item->user->name ?? '不明' }} ／
                                購入日：{{ optional($purchase->created_at)->format('Y-m-d') }}
                            </p>
                        </div>
                        <div class="price">￥{{ number_format($purchase->price) }}</div>
                    </div>
                @empty
                    <div class="note">まだ購入した商品はありません。</div>
                @endforelse
            </div>

            {{-- ページネーション --}}
            @if (!empty($purchases) && method_exists($purchases, 'hasPages') && $purchases->hasPages())
                <div class="pager">
                    {!! $purchases->links() !!}
                </div>
            @endif

            <div class="note">※ ここは後で「購入詳細」も追加できます。</div>

        {{-- PG12：出品した商品一覧 --}}
        @elseif (($page ?? 'profile') === 'sell')
            <div class="list">
                @forelse (($sellItems ?? []) as $item)
                    <div class="row">
                        @if (!empty($item->image_path))
                            <img class="thumb" src="{{ asset('storage/' . $item->image_path) }}" alt="商品画像">
                        @else
                            <img class="thumb" src="https://via.placeholder.com/72?text=No" alt="No Image">
                        @endif

                        <div class="meta">
                            <p class="t">
                                <a href="{{ route('items.show', $item->id) }}" style="text-decoration:none;color:#222;">
                                    {{ $item->title }}
                                </a>

                                @if ($item->status === 'sold')
                                    <span class="badge badge-sold">売り切れ</span>
                                @else
                                    <span class="badge">販売中</span>
                                @endif
                            </p>
                            <p class="s">
                                出品日：{{ optional($item->created_at)->format('Y-m-d') }}
                            </p>
                        </div>

                        <div class="price">￥{{ number_format($item->price) }}</div>
                    </div>
                @empty
                    <div class="note">まだ出品した商品はありません。</div>
                @endforelse
            </div>

            {{-- ページネーション --}}
            @if (!empty($sellItems) && method_exists($sellItems, 'hasPages') && $sellItems->hasPages())
                <div class="pager">
                    {!! $sellItems->links() !!}
                </div>
            @endif

            <div class="note">※ 次は「編集」「再出品」ボタンを付けるともっと良くなるよ。</div>

        {{-- PG09：プロフィール（メニュー） --}}
        @else
            <div class="menu">
                <a href="{{ route('mypage.profile.edit') }}">プロフィール編集（設定） <span>＞</span></a>
                <a href="{{ route('mypage', ['page' => 'buy']) }}">購入した商品一覧 <span>＞</span></a>
                <a href="{{ route('mypage', ['page' => 'sell']) }}">出品した商品一覧 <span>＞</span></a>
            </div>

            <div class="note">
                ※ PG11/PG12 はタブで切り替えできるようにしてあるよ。
            </div>
        @endif

    </div>
</div>

@endsection