{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    :root{
        --accent:#ff5f5f;
        --text:#222;
    }

    /* ===== Register Panel (content only) ===== */
    .register-panel{
        max-width:980px;
        margin:0 auto;
        background:#fff;
        padding:60px 16px 40px;
    }

    .register-title{
        margin:0 0 28px;
        text-align:center;
        font-size:28px;
        font-weight:900;
        color:var(--text);
    }

    .register-form{
        width:min(520px, 100%);
        margin:0 auto;
    }

    .register-row{
        margin:0 0 18px;
    }

    .register-label{
        display:block;
        font-size:14px;
        font-weight:800;
        margin:0 0 8px;
        color:var(--text);
    }

    .register-input{
        width:100%;
        height:44px;
        border:1px solid #bbb;
        border-radius:2px;
        padding:0 12px;
        font-size:15px;
        outline:none;
        background:#fff;
    }
    .register-input:focus{
        border-color:#888;
    }

    /* errors */
    .register-errors{
        width:min(520px, 100%);
        margin:0 auto 18px;
        padding:10px 12px;
        border-radius:6px;
        background:#ffecec;
        color:#c00;
        font-size:13px;
    }
    .register-errors ul{
        margin:0;
        padding-left:18px;
    }

    .register-btn-area{
        width:min(520px, 100%);
        margin:34px auto 0;
        text-align:center;
    }
    .register-btn{
        width:100%;
        height:46px;
        border:none;
        border-radius:3px;
        background:var(--accent);
        color:#fff;
        font-size:16px;
        font-weight:900;
        cursor:pointer;
    }
    .register-btn:hover{
        filter:brightness(0.98);
    }

    .register-link{
        margin-top:18px;
        text-align:center;
        font-size:13px;
    }
    .register-link a{
        color:#1a73e8;
        text-decoration:none;
        font-weight:700;
    }
    .register-link a:hover{
        text-decoration:underline;
    }

    @media (max-width: 420px){
        .register-panel{ padding:44px 12px 34px; }
        .register-title{ font-size:24px; }
    }
</style>
@endpush

@section('content')
<div class="register-panel">
    <h1 class="register-title">会員登録</h1>

    {{-- エラー表示 --}}
    @if ($errors->any())
        <div class="register-errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="register-form" method="POST" action="{{ route('register') }}">
        @csrf

        <div class="register-row">
            <label class="register-label" for="name">ユーザー名</label>
            <input
                class="register-input"
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                maxlength="20"
                placeholder="20文字以内で入力"
                autofocus
                autocomplete="name"
            >
        </div>

        <div class="register-row">
            <label class="register-label" for="email">メールアドレス</label>
            <input
                class="register-input"
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                placeholder="example@example.com"
                autocomplete="email"
            >
        </div>

        <div class="register-row">
            <label class="register-label" for="password">パスワード</label>
            <input
                class="register-input"
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
            >
        </div>

        <div class="register-row">
            <label class="register-label" for="password_confirmation">確認用パスワード</label>
            <input
                class="register-input"
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            >
        </div>

        <div class="register-btn-area">
            <button type="submit" class="register-btn">登録する</button>
        </div>

        <div class="register-link">
            <a href="{{ route('login') }}">ログインはこちら</a>
        </div>
    </form>
</div>
@endsection