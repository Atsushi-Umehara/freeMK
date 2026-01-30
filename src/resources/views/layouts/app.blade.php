{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'COACHTECH' }}</title>

  <style>
    :root{
      --header:#111;
      --main:#ffffff;
      --muted:#777;
      --accent:#ff5f5f;
    }
    *{box-sizing:border-box;}
    body{
      margin:0;
      font-family:-apple-system,BlinkMacSystemFont,"Helvetica Neue","Segoe UI",
                  Arial,"游ゴシック体","YuGothic","メイリオ",sans-serif;
      background:var(--main);
      color:#222;
    }

    /* ===== Header ===== */
    .ct-header{
      background:var(--header);
      color:#fff;
      padding:14px 18px;
    }
    .ct-header__inner{
      max-width:1100px;
      margin:0 auto;
      display:flex;
      align-items:center;
      gap:14px;
    }

    /* logo */
    .ct-brand{
      display:flex;
      align-items:center;
      text-decoration:none;
      min-width:170px;
    }
    .ct-brand__logo{
      height:28px;
      width:auto;
      display:block;
    }

    /* search */
    .ct-search{
      flex:1;
      display:flex;
      justify-content:center;
    }
    .ct-search form{ width:min(520px, 100%); }
    .ct-search input{
      width:100%;
      height:34px;
      border-radius:4px;
      border:1px solid #444;
      padding:0 12px;
      outline:none;
    }

    /* nav */
    .ct-nav{
      display:flex;
      align-items:center;
      gap:14px;
      white-space:nowrap;
      font-size:14px;
    }
    .ct-nav a{
      color:#fff;
      text-decoration:none;
      opacity:.95;
      display:inline-flex;
      align-items:center;
      font-weight:400;
    }
    .ct-nav a:hover{
      opacity:1;
      text-decoration:underline;
    }

    /* ログアウト（リンク風ボタン） */
    .ct-nav__logout{
      background:none;
      border:none;
      color:#fff;
      padding:0;
      cursor:pointer;
      font-size:14px;
      font-weight:400;
    }
    .ct-nav__logout:hover{
      text-decoration:underline;
    }

    /* 出品ボタン */
    .ct-nav__sell{
      background:#fff;
      color:#111 !important;
      border:1px solid #fff;
      padding:7px 14px;
      border-radius:4px;
      font-weight:800;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      white-space:nowrap;
      min-width:64px;
      line-height:1;
    }
    .ct-nav__sell:hover{
      text-decoration:none;
      filter:brightness(.97);
    }

    .ct-nav form{margin:0;}

    /* content */
    .wrap{
      min-height:calc(100vh - 62px);
    }
    .main{
      max-width:1100px;
      margin:0 auto;
      padding:18px;
    }
    .main.is-auth{
      padding:60px 16px;
    }

    /* ===== responsive ===== */
    @media (max-width: 760px){
      .ct-header__inner{
        flex-wrap:wrap;
      }

      .ct-brand{
        min-width:auto;
      }

      /* ★検索枠：2段目にして「左寄せ + 全幅」で自然に見せる */
      .ct-search{
        order:3;
        width:100%;
        justify-content:flex-start; /* ← center をやめる */
        margin-top:10px;
      }
      .ct-search form{
        width:100%; /* ← min()をやめて全幅に */
      }

      .main{padding:14px;}
      .main.is-auth{padding:44px 12px;}
    }
  </style>

  @stack('styles')
</head>
<body>

@php
  $isAuthPage = request()->is('login', 'register');
@endphp

<header class="ct-header">
  <div class="ct-header__inner">

    {{-- ロゴ --}}
    <a class="ct-brand" href="{{ route('items.index', ['tab' => 'recommend']) }}">
      <img class="ct-brand__logo"
          src="{{ asset('storage/images/coachtech-logo.png') }}"
          alt="COACHTECH">
    </a>

    @unless($isAuthPage)

      {{-- 検索 --}}
      <div class="ct-search">
        <form method="GET" action="{{ route('items.index') }}">
          <input type="text" name="q"
                value="{{ request('q') }}"
                placeholder="なにをお探しですか？">
          <input type="hidden" name="tab"
                value="{{ request('tab', 'recommend') }}">
        </form>
      </div>

      {{-- ナビ --}}
      <nav class="ct-nav">

        @guest
          <a href="{{ route('login') }}">ログイン</a>
          <a href="{{ route('login') }}">マイページ</a>
          <a class="ct-nav__sell" href="{{ route('login') }}">出品</a>
        @endguest

        @auth
          {{-- 並び順：ログアウト → マイページ → 出品 --}}
          <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="ct-nav__logout">ログアウト</button>
          </form>

          <a href="{{ route('mypage') }}">マイページ</a>

          <a class="ct-nav__sell" href="{{ route('items.sell') }}">出品</a>
        @endauth

      </nav>

    @endunless
  </div>
</header>

<div class="wrap">
  <main class="main {{ $isAuthPage ? 'is-auth' : '' }}">
    @yield('content')
  </main>
</div>

</body>
</html>