{{-- resources/views/mypage/profile.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
:root{
  --accent:#ff5f5f;
  --text:#222;
  --muted:#666;
}

/* ===== Profile ===== */
.pf-wrap{
  max-width:1100px;
  margin:0 auto;
  padding:20px 0 60px;
}
.pf-title{
  text-align:center;
  font-size:24px;
  font-weight:900;
  margin:18px 0 30px;
}

/* panel */
.pf-panel{
  width:min(520px,100%);
  margin:0 auto;
}

/* avatar */
.pf-avatarRow{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:20px;
  margin-bottom:28px;
}
.pf-avatar{
  width:84px;
  height:84px;
  border-radius:50%;
  background:#d9d9d9;
  overflow:hidden;
}
.pf-avatar img{
  width:100%;
  height:100%;
  object-fit:cover;
}

/* file btn */
.pf-fileWrap{ position:relative; }
.pf-fileBtn{
  border:1px solid var(--accent);
  color:var(--accent);
  background:#fff;
  border-radius:6px;
  padding:8px 14px;
  font-size:13px;
  font-weight:800;
  cursor:pointer;
}
.pf-fileInput{
  position:absolute;
  inset:0;
  opacity:0;
  cursor:pointer;
}

/* errors */
.pf-errors{
  background:#ffecec;
  color:#c00;
  border-radius:8px;
  padding:10px 12px;
  margin-bottom:16px;
  font-size:13px;
}
.pf-errors ul{margin:0;padding-left:18px;}

/* form */
.pf-row{ margin-bottom:22px; }
.pf-row label{
  display:block;
  font-size:14px;
  font-weight:800;
  margin-bottom:8px;
}
.pf-row input{
  width:100%;
  height:40px;
  border:1px solid #bbb;
  border-radius:2px;
  padding:0 12px;
  font-size:14px;
}
.pf-row input:focus{ border-color:#888; outline:none; }

/* submit */
.pf-btnArea{
  margin-top:28px;
  display:flex;
  justify-content:center;
}
.pf-btn{
  width:100%;
  height:42px;
  background:var(--accent);
  color:#fff;
  border:none;
  border-radius:2px;
  font-weight:900;
  cursor:pointer;
}
.pf-btn:hover{filter:brightness(.97);}
</style>
@endpush

@section('content')
<div class="pf-wrap">

  <h1 class="pf-title">プロフィール設定</h1>

  <div class="pf-panel">

    {{-- フラッシュ --}}
    @if (session('message'))
      <div class="pf-errors" style="background:#eaffea;color:#1b6b1b;">
        {{ session('message') }}
      </div>
    @endif

    {{-- バリデーション --}}
    @if ($errors->any())
      <div class="pf-errors">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- フォーム --}}
    <form method="POST"
          action="{{ route('mypage.profile.update') }}"
          enctype="multipart/form-data">
      @csrf

      {{-- avatar --}}
      <div class="pf-avatarRow">
        <div class="pf-avatar">
          @if ($user->profile_image)
            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="avatar">
          @endif
        </div>

        <div class="pf-fileWrap">
          <span class="pf-fileBtn">画像を選択する</span>
          <input class="pf-fileInput"
                type="file"
                name="profile_image"
                accept="image/*">
        </div>
      </div>

      {{-- name --}}
      <div class="pf-row">
        <label for="name">ユーザー名</label>
        <input id="name" type="text" name="name"
              value="{{ old('name', $user->name) }}" required>
      </div>

      {{-- email（Controller必須なので表示） --}}
      <div class="pf-row">
        <label for="email">メールアドレス</label>
        <input id="email" type="text" name="email"
              value="{{ old('email', $user->email) }}" required>
      </div>

      {{-- postal --}}
      <div class="pf-row">
        <label for="postal_code">郵便番号</label>
        <input id="postal_code" type="text" name="postal_code"
              value="{{ old('postal_code', $user->postal_code) }}">
      </div>

      {{-- address --}}
      <div class="pf-row">
        <label for="address">住所</label>
        <input id="address" type="text" name="address"
              value="{{ old('address', $user->address) }}">
      </div>

      {{-- building --}}
      <div class="pf-row">
        <label for="building">建物名</label>
        <input id="building" type="text" name="building"
              value="{{ old('building', $user->building) }}">
      </div>

      {{-- submit --}}
      <div class="pf-btnArea">
        <button type="submit" class="pf-btn">更新する</button>
      </div>

    </form>
  </div>
</div>
@endsection