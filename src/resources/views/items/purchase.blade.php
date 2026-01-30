{{-- resources/views/items/purchase.blade.php（PG06） --}}

@extends('layouts.app')

@push('styles')
<style>
    :root{
        --line:#dcdcdc;
        --muted:#666;
        --blue:#1a73e8;
    }

    /* ===== PG06 wrapper ===== */
    .pg6{
        max-width:1100px;
        margin:0 auto;
        padding:24px 0 60px;
    }

    .pg6-grid{
        display:grid;
        grid-template-columns: 1fr 380px;  /* 左 / 右 */
        gap:42px;
        align-items:start;
    }
    @media (max-width: 980px){
        .pg6-grid{ grid-template-columns: 1fr; }
    }

    /* ===== 商品サマリー（左上） ===== */
    .itemRow{
        display:flex;
        gap:22px;
        align-items:flex-start;
        padding:12px 0 18px;
        border-bottom:1px solid var(--line);
    }
    .itemThumb{
        width:110px;
        height:110px;
        background:#dcdcdc;
        display:block;
        object-fit:cover;
    }
    .itemInfo{ min-width:0; }
    .itemTitle{
        font-weight:900;
        font-size:18px;
        margin:4px 0 8px;
    }
    .itemPrice{
        font-weight:900;
        font-size:16px;
        margin:0;
    }

    /* ===== セクション ===== */
    .section{
        padding:18px 0;
        border-bottom:1px solid var(--line);
    }
    .sectionTitle{
        font-weight:900;
        margin:0 0 12px;
        font-size:13px;
    }

    /* 支払い方法：見本はプルダウン */
    .select{
        width:240px;
        height:24px;
        font-size:12px;
    }

    /* 配送先 */
    .shipHead{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        margin-bottom:10px;
    }
    .changeLink{
        color:var(--blue);
        text-decoration:none;
        font-size:12px;
        font-weight:700;
    }
    .changeLink:hover{ text-decoration:underline; }

    .shipText{
        font-size:12px;
        line-height:1.7;
        color:#111;
        white-space:pre-line;
    }

    /* エラー */
    .error-list{
        background:#ffecec;
        color:#c00;
        border-radius:8px;
        padding:10px 12px;
        font-size:13px;
        margin:0 0 14px;
    }
    .error-list ul{ margin:0; padding-left:18px; }

    /* ===== 右カラム（枠サマリー） ===== */
    .summary{
        border:1px solid #999;
        background:#fff;
    }
    .sumRow{
        display:flex;
        justify-content:space-between;
        gap:16px;
        padding:18px 18px;
        font-size:13px;
    }
    .sumRow + .sumRow{ border-top:1px solid #999; }
    .sumLabel{ font-weight:700; }
    .sumValue{ font-weight:900; white-space:nowrap; }

    /* ★購入ボタン（赤が出ない事故を防ぐ完全版） */
    .buyBtn{
        margin-top:20px;
        width:100%;
        height:44px;
        border:none;

        /* ★accentが無くても赤になる */
        background: var(--accent, #ff5f5f);

        /* ★文字が消える事故も防ぐ */
        color:#fff !important;

        font-weight:900;
        cursor:pointer;
        border-radius:2px;

        opacity:1 !important;
        visibility:visible !important;
    }
    .buyBtn:hover{ filter:brightness(.97); }
</style>
@endpush

@section('content')
@php
    // PG07（住所変更）で保存した住所を優先して表示
    $addressData = session("purchase.address.{$item->id}", []);

    // 見本の表示寄せ：空ならダミー文言（必要なければ '' にしてOK）
    $postal = old('postal_code', $addressData['postal_code'] ?? 'XXX-YYYY');
    $addr   = old('address',     $addressData['address']     ?? 'ここには住所が入ります');
    $bldg   = old('name',        $addressData['name']        ?? 'ここには建物名が入ります');

    // 支払い方法（初期は未選択にして「選択してください」を出す）
    $pm = old('payment_method', '');
    $pmLabel = $pm === 'convenience' ? 'コンビニ払い' : ($pm === 'credit' ? 'クレジットカード' : '');
@endphp

<div class="pg6">

    {{-- バリデーションエラー --}}
    @if ($errors->any())
        <div class="error-list">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- フラッシュエラー（Controllerでwith('error')してる場合） --}}
    @if (session('error'))
        <div class="error-list">
            {{ session('error') }}
        </div>
    @endif

    {{-- ✅ Stripeへ遷移するため purchase.stripe に送る --}}
    <form method="POST" action="{{ route('purchase.stripe', ['item_id' => $item->id]) }}">
        @csrf

        <div class="pg6-grid">

            {{-- ================= 左 ================= --}}
            <div>

                {{-- 商品サマリー --}}
                <div class="itemRow">
                    @if (!empty($item->image_path))
                        <img class="itemThumb" src="{{ asset('storage/' . $item->image_path) }}" alt="商品画像">
                    @else
                        <img class="itemThumb" src="https://via.placeholder.com/110x110?text=%E5%95%86%E5%93%81%E7%94%BB%E5%83%8F" alt="商品画像">
                    @endif

                    <div class="itemInfo">
                        <div class="itemTitle">{{ $item->title }}</div>
                        <p class="itemPrice">￥{{ number_format($item->price) }}</p>
                    </div>
                </div>

                {{-- 支払い方法 --}}
                <div class="section">
                    <div class="sectionTitle">支払い方法</div>

                    <select class="select" name="payment_method" id="payment_method" required>
                        <option value="" disabled {{ old('payment_method') ? '' : 'selected' }}>選択してください</option>

                        <option value="credit" {{ old('payment_method') === 'credit' ? 'selected' : '' }}>
                            クレジットカード
                        </option>

                        <option value="convenience" {{ old('payment_method') === 'convenience' ? 'selected' : '' }}>
                            コンビニ払い
                        </option>
                    </select>
                </div>

                {{-- 配送先 --}}
                <div class="section">
                    <div class="shipHead">
                        <div class="sectionTitle" style="margin:0;">配送先</div>
                        <a class="changeLink" href="{{ route('purchase.address', ['item_id' => $item->id]) }}">
                            変更する
                        </a>
                    </div>

                    <div class="shipText">
                        <strong>〒 {{ $postal }}</strong>
                        <br>
                        <strong>{{ $addr }}</strong>
                        <br>
                        <strong>{{ $bldg }}</strong>
                    </div>

                    {{-- ※サーバ側で確定させる設計なら不要だが、現状維持で残す --}}
                    <input type="hidden" name="postal_code" value="{{ $postal }}">
                    <input type="hidden" name="address" value="{{ $addr }}">
                    <input type="hidden" name="name" value="{{ $bldg }}">
                </div>

            </div>

            {{-- ================= 右 ================= --}}
            <aside>
                <div class="summary">
                    <div class="sumRow">
                        <div class="sumLabel">商品代金</div>
                        <div class="sumValue">￥{{ number_format($item->price) }}</div>
                    </div>
                    <div class="sumRow">
                        <div class="sumLabel">支払い方法</div>
                        <div class="sumValue" id="pmText">{{ $pmLabel }}</div>
                    </div>
                </div>

                <button type="submit" class="buyBtn">購入する</button>
            </aside>

        </div>
    </form>
</div>

<script>
    // 右枠の「支払い方法」表示をプルダウンに合わせて更新（見本寄せ）
    (function(){
        const sel = document.getElementById('payment_method');
        const pmText = document.getElementById('pmText');
        if(!sel || !pmText) return;

        const map = {
            credit: 'クレジットカード',
            convenience: 'コンビニ払い'
        };

        const render = () => {
            pmText.textContent = map[sel.value] || '';
        };

        sel.addEventListener('change', render);
        render();
    })();
</script>
@endsection