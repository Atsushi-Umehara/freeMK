{{-- resources/views/items/sell.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
  :root{
    --accent:#ff5f5f;
    --ink:#111;
    --muted:#666;
    --line:#cfcfcf;
  }

  /* ===== page ===== */
  .sell-wrap{
    max-width: 760px;
    margin: 0 auto;
    padding: 38px 24px 60px;
  }

  .sell-title{
    text-align:center;
    font-size:22px;
    font-weight:900;
    color:var(--ink);
    margin: 6px 0 28px;
  }

  /* ===== section header (見本の太字＋下線) ===== */
  .sec-title{
    font-size:16px;
    font-weight:900;
    color:var(--ink);
    margin: 34px 0 10px;
  }
  .sec-rule{
    height:1px;
    background: var(--line);
    margin: 0 0 18px;
  }

  /* ===== field ===== */
  .field{ margin: 16px 0; }
  .label{
    display:block;
    font-size:14px;
    font-weight:900;
    color:var(--ink);
    margin-bottom:8px;
  }
  .req{
    display:inline-block;
    margin-left:6px;
    font-size:11px;
    font-weight:900;
    color:var(--accent);
  }

  .input, .textarea, .select{
    width:100%;
    border:1px solid #bfbfbf;
    border-radius:4px;
    padding:10px 12px;
    font-size:14px;
    outline:none;
    background:#fff;
    box-sizing:border-box;
  }
  .textarea{ min-height:130px; resize:vertical; }
  .input:focus, .textarea:focus, .select:focus{ border-color:#999; }

  /* ===== image box (点線枠＋中央ボタン) ===== */
  .dropbox{
    border:1px dashed #bfbfbf;
    height:140px;
    border-radius:2px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#fff;
    position:relative;
    overflow:hidden;
  }
  .file-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1px solid var(--accent);
    color:var(--accent);
    background:#fff;
    border-radius:6px;
    padding:8px 14px;
    font-weight:900;
    font-size:12px;
    cursor:pointer;
    user-select:none;
    z-index:2;
  }
  .file-input{ display:none; }
  .hint{
    margin-top:8px;
    font-size:12px;
    color:#888;
    font-weight:700;
  }

  /* プレビュー画像 */
  .preview{
    position:absolute;
    inset:0;
    width:100%;
    height:100%;
    object-fit:contain;
    background:#fff;
    display:none;
    z-index:1;
  }
  .preview.on{ display:block; }
  .dropbox.has-preview .file-btn{
    position:absolute;
    right:10px;
    bottom:10px;
    background:#fff;
  }

  /* ===== category chips (白/赤) ===== */
  .chips{
    display:flex;
    flex-wrap:wrap;
    gap:10px 10px;
    padding: 6px 0 2px;
  }
  .chip{ position:relative; }
  .chip input{
    position:absolute;
    opacity:0;
    pointer-events:none;
  }
  .chip label{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:6px 14px;
    border-radius:999px;
    border:1px solid var(--accent);
    color:var(--accent);
    background:#fff;
    font-size:12px;
    font-weight:900;
    cursor:pointer;
    user-select:none;
    line-height:1;
    white-space:nowrap;
  }
  .chip input:checked + label{
    background:var(--accent);
    color:#fff;
  }

  /* ===== price (¥ + input) ===== */
  .price-row{
    display:flex;
    align-items:center;
    gap:8px;
  }
  .yen{
    font-weight:900;
    color:var(--ink);
    margin-left:2px;
  }

  /* ===== errors ===== */
  .error-box{
    background:#ffecec;
    color:#c00;
    border-radius:6px;
    padding:10px 12px;
    margin: 0 0 16px;
    font-size:13px;
    font-weight:800;
  }
  .error-box ul{ margin:0; padding-left:18px; }
  .err{
    margin-top:6px;
    color:#d00;
    font-size:12px;
    font-weight:800;
  }

  /* ===== submit ===== */
  .submit-area{ margin-top: 46px; }
  .btn-submit{
    width:100%;
    border:none;
    background:var(--accent);
    color:#fff;
    font-weight:900;
    font-size:14px;
    padding:16px 10px;
    border-radius:4px;
    cursor:pointer;
  }
  .btn-submit:hover{ filter:brightness(.95); }

  /* ===== responsive ===== */
  @media (max-width: 520px){
    .sell-wrap{ padding: 26px 16px 44px; }
  }
</style>
@endpush

@section('content')
<div class="sell-wrap">

  <h1 class="sell-title">商品の出品</h1>

  {{-- バリデーションエラー（まとめて表示） --}}
  @if ($errors->any())
    <div class="error-box">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @php
    // 見本に合わせたカテゴリー（必要なら増減OK）
    $cats = ['ファッション','家電','インテリア','レディース','メンズ','コスメ','本','ゲーム','スポーツ','キッチン','ハンドメイド','アクセサリー','おもちゃ','ベビー・キッズ'];

    // 商品状態（見本に合わせた候補）
    $conditions = ['良好','目立った傷や汚れなし','やや傷や汚れあり','状態が悪い'];

    $oldCat = old('category');
    $oldCon = old('condition', '良好');
  @endphp

  <form method="POST" action="{{ route('items.sell.store') }}" enctype="multipart/form-data">
    @csrf

    {{-- 商品画像 --}}
    <div class="field">
      <span class="label">商品画像<span class="req">必須</span></span>

      <div class="dropbox" id="dropbox">
        <img id="preview" class="preview" alt="画像プレビュー">

        <label class="file-btn" for="image">
          画像を選択する
        </label>
        <input id="image" class="file-input" type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required>
      </div>

      <div class="hint">※ JPG / PNG / WEBP（最大2MB）</div>

      @error('image')
        <div class="err">{{ $message }}</div>
      @enderror
    </div>

    {{-- 商品の詳細 --}}
    <div class="sec-title">商品の詳細</div>
    <div class="sec-rule"></div>

    {{-- カテゴリー --}}
    <div class="field">
      <span class="label">カテゴリー<span class="req">必須</span></span>

      <div class="chips">
        @foreach($cats as $c)
          @php $id = 'cat_' . $loop->index; @endphp
          <div class="chip">
            <input type="radio" id="{{ $id }}" name="category" value="{{ $c }}" {{ $oldCat === $c ? 'checked' : '' }} required>
            <label for="{{ $id }}">{{ $c }}</label>
          </div>
        @endforeach
      </div>

      @error('category')
        <div class="err">{{ $message }}</div>
      @enderror
    </div>

    {{-- 商品の状態 --}}
    <div class="field">
      <span class="label">商品の状態<span class="req">必須</span></span>

      <select class="select" name="condition" required>
        @foreach($conditions as $con)
          <option value="{{ $con }}" {{ $oldCon === $con ? 'selected' : '' }}>{{ $con }}</option>
        @endforeach
      </select>

      @error('condition')
        <div class="err">{{ $message }}</div>
      @enderror
    </div>

    {{-- 商品名と説明 --}}
    <div class="sec-title">商品名と説明</div>
    <div class="sec-rule"></div>

    <div class="field">
      <label class="label" for="title">商品名<span class="req">必須</span></label>
      <input class="input" id="title" type="text" name="title" value="{{ old('title') }}" required>
      @error('title')
        <div class="err">{{ $message }}</div>
      @enderror
    </div>

    <div class="field">
      <label class="label" for="brand">ブランド名</label>
      <input class="input" id="brand" type="text" name="brand" value="{{ old('brand') }}">
      @error('brand')
        <div class="err">{{ $message }}</div>
      @enderror
    </div>

    <div class="field">
      <label class="label" for="description">商品の説明<span class="req">必須</span></label>
      <textarea class="textarea" id="description" name="description" required>{{ old('description') }}</textarea>
      @error('description')
        <div class="err">{{ $message }}</div>
      @enderror
    </div>

    {{-- 販売価格 --}}
    <div class="field">
      <label class="label" for="price">販売価格<span class="req">必須</span></label>
      <div class="price-row">
        <span class="yen">¥</span>
        <input class="input" id="price" type="number" name="price" min="1" step="1" value="{{ old('price') }}" required>
      </div>
      @error('price')
        <div class="err">{{ $message }}</div>
      @enderror
    </div>

    <div class="submit-area">
      <button class="btn-submit" type="submit">出品する</button>
    </div>
  </form>
</div>

{{-- 画像プレビュー（選択したら枠内に表示） --}}
<script>
  (function(){
    const input = document.getElementById('image');
    const preview = document.getElementById('preview');
    const dropbox = document.getElementById('dropbox');

    if(!input) return;

    input.addEventListener('change', function(){
      const file = this.files && this.files[0];
      if(!file){
        preview.src = '';
        preview.classList.remove('on');
        dropbox.classList.remove('has-preview');
        return;
      }

      const url = URL.createObjectURL(file);
      preview.src = url;
      preview.classList.add('on');
      dropbox.classList.add('has-preview');
    });
  })();
</script>
@endsection