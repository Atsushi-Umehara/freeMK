{{-- resources/views/auth/login.blade.php --}}

@extends('layouts.app')

@push('styles')
<style>
  :root{
    --accent:#ff5f5f;
    --text:#222;
    --muted:#666;
    --line:#dcdcdc;
  }

  .login-wrap{
    max-width:1100px;
    margin:0 auto;
  }

  .login-card{
    max-width:560px;
    margin:0 auto;
    padding:20px 18px 40px;
  }

  .login-title{
    text-align:center;
    font-size:26px;
    font-weight:900;
    margin:0 0 28px;
    letter-spacing:.4px;
    color:var(--text);
  }

  .login-form{
    width:min(520px, 100%);
    margin:0 auto;
  }

  .login-row{
    margin-bottom:22px;
  }

  .login-label{
    display:block;
    font-size:14px;
    font-weight:800;
    margin-bottom:8px;
    color:var(--text);
  }

  .login-input{
    width:100%;
    height:40px;
    padding:0 12px;
    border:1px solid #bbb;
    border-radius:2px;
    font-size:14px;
    outline:none;
    background:#fff;
  }
  .login-input:focus{
    border-color:#777;
  }

  .login-remember{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:13px;
    color:var(--muted);
    margin:6px 0 18px;
  }

  .login-btn{
    width:100%;
    height:46px;
    border:none;
    border-radius:3px;
    background:var(--accent);
    color:#fff;
    font-weight:900;
    font-size:15px;
    cursor:pointer;
  }
  .login-btn:hover{ filter:brightness(.97); }

  .login-links{
    text-align:center;
    margin-top:18px;
    font-size:13px;
  }
  .login-links a{
    color:#1a73e8;
    text-decoration:none;
    font-weight:700;
  }
  .login-links a:hover{ text-decoration:underline; }

  .login-error{
    width:min(520px, 100%);
    margin:0 auto 16px;
    padding:10px 12px;
    border-radius:6px;
    background:#ffecec;
    color:#c00;
    font-size:13px;
  }
  .login-error ul{
    margin:0;
    padding-left:18px;
  }

  @media (max-width: 640px){
    .login-card{ padding:10px 12px 34px; }
  }
</style>
@endpush

@section('content')
<div class="login-wrap">
  <div class="login-card">
    <h1 class="login-title">ログイン</h1>

    {{-- エラー表示 --}}
    @if ($errors->any())
      <div class="login-error">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- ログインフォーム --}}
    <form class="login-form" method="POST" action="{{ route('login') }}">
      @csrf

      <div class="login-row">
        <label class="login-label" for="email">メールアドレス</label>
        <input
          class="login-input"
          id="email"
          type="email"
          name="email"
          value="{{ old('email') }}"
          required
          autocomplete="email"
        >
      </div>

      <div class="login-row">
        <label class="login-label" for="password">パスワード</label>
        <input
          class="login-input"
          id="password"
          type="password"
          name="password"
          required
          autocomplete="current-password"
        >
      </div>

      {{-- 見本どおりにしたいなら、下の remember を削除してOK --}}
      <label class="login-remember">
        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
        ログイン状態を保持する
      </label>

      <button class="login-btn" type="submit">ログインする</button>

      <div class="login-links">
        <a href="{{ route('register') }}">会員登録はこちら</a>
      </div>
    </form>
  </div>
</div>
@endsection