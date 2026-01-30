{{-- resources/views/items/index.blade.php --}}

@extends('layouts.app')

@push('styles')
<style>
    :root{
        --line:#dcdcdc;
        --accent:#ff5f5f;
        --text:#222;
    }

    .pg-tabs{
        display:flex;
        gap:46px;
        padding:18px 0 14px;
        border-bottom:1px solid #999;
        margin:0 0 26px;
    }
    .pg-tab{
        text-decoration:none;
        font-weight:800;
        color:#666;
        font-size:16px;
    }
    .pg-tab.active{ color:var(--accent); }

    .pg-grid{
        display:grid;
        grid-template-columns:repeat(4, 1fr);
        gap:26px 34px;
    }
    @media (max-width: 1000px){ .pg-grid{grid-template-columns:repeat(3, 1fr);} }
    @media (max-width: 760px){ .pg-grid{grid-template-columns:repeat(2, 1fr);} }
    @media (max-width: 420px){ .pg-grid{grid-template-columns:1fr;} }

    .pg-card{
        position:relative;
        background:transparent;
        border:none;
        border-radius:0;
        overflow:visible;
    }

    .pg-link{
        display:block;
        color:inherit;
        text-decoration:none;
    }

    .pg-thumb{
        width:100%;
        aspect-ratio: 1 / 1;
        background:#dcdcdc;
        display:block;
        object-fit:cover;
        border-radius:4px;
    }

    .pg-sold{
        position:absolute;
        top:10px;
        left:10px;
        background:rgba(255,95,95,.95);
        color:#fff;
        font-weight:900;
        font-size:12px;
        padding:6px 10px;
        border-radius:4px;
        line-height:1;
        z-index:2;
        pointer-events:none;
    }

    .pg-name{
        margin:10px 0 0;
        font-size:14px;
        font-weight:700;
        color:var(--text);
        line-height:1.3;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }
    .pg-link:hover .pg-name{
        text-decoration:underline;
    }

    .pg-empty{
        padding:12px 0;
        color:#666;
    }

    .pg-msg{
        margin:12px 0 16px;
        padding:10px 12px;
        border-radius:8px;
        font-size:13px;
    }
    .pg-msg.ok{background:#eaffea;color:#1b6b1b;}
    .pg-msg.ng{background:#ffecec;color:#c00;}
</style>
@endpush

@section('content')
@php
    $activeTab = $tab ?? request('tab', 'recommend');
    $q = request('q');
@endphp

<div class="pg-tabs">
    <a
        class="pg-tab {{ $activeTab === 'recommend' ? 'active' : '' }}"
        href="{{ route('items.index', ['tab' => 'recommend', 'q' => $q]) }}"
    >おすすめ</a>

    @auth
        <a
            class="pg-tab {{ $activeTab === 'mylist' ? 'active' : '' }}"
            href="{{ route('items.index', ['tab' => 'mylist', 'q' => $q]) }}"
        >マイリスト</a>
    @else
        <a class="pg-tab" href="{{ route('login') }}">マイリスト</a>
    @endauth
</div>

@if (session('message'))
    <div class="pg-msg ok">{{ session('message') }}</div>
@endif
@if (session('error'))
    <div class="pg-msg ng">{{ session('error') }}</div>
@endif

@if (($items ?? collect())->count() === 0)
    <div class="pg-empty">商品がありません。</div>
@else
    <div class="pg-grid">
        @foreach ($items as $item)
            <div class="pg-card">
                @if (($item->status ?? '') === 'sold')
                    <span class="pg-sold">Sold</span>
                @endif

                {{-- ✅ ルート引数は配列指定にしてズレ防止 --}}
                <a class="pg-link" href="{{ route('items.show', ['item_id' => $item->id]) }}">
                    @if (!empty($item->image_path))
                        <img class="pg-thumb" src="{{ asset('storage/' . $item->image_path) }}" alt="商品画像">
                    @else
                        <img class="pg-thumb" src="https://via.placeholder.com/600x600/dcdcdc/333?text=%E5%95%86%E5%93%81%E7%94%BB%E5%83%8F" alt="商品画像">
                    @endif

                    <p class="pg-name">{{ $item->title }}</p>
                </a>
            </div>
        @endforeach
    </div>
@endif
@endsection