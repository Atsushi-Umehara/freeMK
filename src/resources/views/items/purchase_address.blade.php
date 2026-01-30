{{-- resources/views/items/purchase_address.blade.php --}}

@extends('layouts.app')

@push('styles')
<style>
    /* ===== PG07: 住所の変更（ページ固有） ===== */
    :root{
        --pg07-accent: var(--accent, #ff5f5f);
        --pg07-text: #222;
    }

    .pg07{
        max-width: 1100px;
        margin: 0 auto;
        padding: 10px 0 40px;
    }

    .pg07__title{
        text-align:center;
        font-size:24px;          /* 見本寄せで少し大きめ */
        font-weight:900;
        margin:18px 0 28px;
        letter-spacing:.4px;
        color:var(--pg07-text);
    }

    .pg07__panel{
        width: min(520px, 100%);
        margin: 0 auto;
    }

    /* エラー */
    .pg07__errors{
        background:#ffecec;
        color:#c00;
        border-radius:8px;
        padding:10px 12px;
        margin:0 0 14px;
        font-size:13px;
    }
    .pg07__errors ul{
        margin:0;
        padding-left:18px;
    }

    /* フォーム */
    .pg07__row{
        margin:0 0 22px;          /* 見本は縦の間隔が少し広め */
    }
    .pg07__row label{
        display:block;
        font-size:14px;
        font-weight:800;
        margin:0 0 8px;           /* 見本寄せ */
        color:var(--pg07-text);
    }
    .pg07__row input[type="text"]{
        width:100%;
        height:40px;              /* 見本寄せ */
        border:1px solid #bbb;
        border-radius:2px;        /* 見本は角が小さめ */
        padding:0 12px;
        font-size:14px;
        outline:none;
        box-sizing:border-box;
        background:#fff;
    }
    .pg07__row input[type="text"]:focus{
        border-color:#888;
    }

    /* ボタン */
    .pg07__btnArea{
        margin-top:28px;
        display:flex;
        justify-content:center;
    }
    .pg07__btn{
        width: min(520px, 100%);
        height:42px;              /* 見本寄せ */
        background: var(--pg07-accent);
        color:#fff;
        border:none;
        border-radius:2px;        /* 見本は角が小さめ */
        font-weight:900;
        cursor:pointer;
    }
    .pg07__btn:hover{
        filter:brightness(.97);
    }
</style>
@endpush

@section('content')
<div class="pg07">

    <h1 class="pg07__title">住所の変更</h1>

    <div class="pg07__panel">

        {{-- エラー --}}
        @if ($errors->any())
            <div class="pg07__errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 住所変更フォーム --}}
        <form method="POST" action="{{ route('purchase.address.store', $item->id) }}">
            @csrf

            <div class="pg07__row">
                <label for="postal_code">郵便番号</label>
                <input
                    type="text"
                    id="postal_code"
                    name="postal_code"
                    value="{{ old('postal_code', session("purchase.address.{$item->id}.postal_code")) }}"
                >
            </div>

            <div class="pg07__row">
                <label for="address">住所</label>
                <input
                    type="text"
                    id="address"
                    name="address"
                    value="{{ old('address', session("purchase.address.{$item->id}.address")) }}"
                >
            </div>

            {{-- 見た目は「建物名」だけど、送信nameは既存仕様維持で name のまま --}}
            <div class="pg07__row">
                <label for="name">建物名</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', session("purchase.address.{$item->id}.name")) }}"
                >
            </div>

            <div class="pg07__btnArea">
                <button type="submit" class="pg07__btn">更新する</button>
            </div>
        </form>

    </div>
</div>
@endsection