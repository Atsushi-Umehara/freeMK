{{-- resources/views/items/show.blade.php --}}

@extends('layouts.app')

@push('styles')
<style>
  :root{
    --accent:#ff5f5f;
    --text:#222;
    --muted:#666;
    --line:#dcdcdc;
    --chip:#e9e9e9;
    --blue:#1a73e8;
  }

  /* ===== PG05 Layout ===== */
  .pg05{
    max-width: 980px;
    margin: 0 auto;
    padding: 12px 0 40px;
  }

  .pg05-top{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 48px;
    align-items:start;
    margin-top: 12px;
  }

  /* Left image */
  .pg05-image{
    width: 100%;
    aspect-ratio: 1 / 1;
    background:#dcdcdc;
    border-radius: 2px;
    overflow:hidden;
  }
  .pg05-image img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
  }

  /* Right side */
  .pg05-title{
    font-size: 28px;
    font-weight: 900;
    margin: 0 0 6px;
    letter-spacing:.4px;
    color:var(--text);
  }
  .pg05-brand{
    font-size: 12px;
    color: var(--muted);
    margin: 0 0 18px;
  }
  .pg05-price{
    font-size: 26px;
    font-weight: 900;
    margin: 0 0 10px;
    color:var(--text);
  }
  .pg05-price small{
    font-size: 14px;
    font-weight: 800;
    color: var(--muted);
    margin-left: 6px;
  }

  /* icons */
  .pg05-icons{
    display:flex;
    gap: 28px;
    align-items:flex-end;
    margin: 6px 0 18px;
  }
  .pg05-icon{
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:6px;
    color:#222;
    font-weight:800;
    font-size:12px;
    min-width:40px;
  }
  .pg05-icon svg{
    width:28px;
    height:28px;
    display:block;
  }

  /* ✅ いいねボタン（押せる） */
  .pg05-likebtn{
    border:none;
    background:none;
    cursor:pointer;
    padding:0;
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:6px;
    font-weight:800;
    font-size:12px;
    color:#222;
    min-width:40px;
  }
  .pg05-likebtn svg{
    width:28px;
    height:28px;
    display:block;
  }
  .pg05-likebtn.is-link{
    text-decoration:none;
    cursor:pointer;
  }

  /* ===== CTA（購入ボタン） ===== */
  .pg05-cta{
    width:100%;
    height:40px;
    border:none;
    border-radius:2px;
    background: var(--accent);

    color:#fff !important;
    opacity:1 !important;
    visibility:visible !important;

    font-weight:900;
    cursor:pointer;
    text-decoration:none !important;

    display:flex;
    align-items:center;
    justify-content:center;

    margin: 10px 0 22px;
    line-height:1;
  }
  .pg05-cta:hover{ filter:brightness(.97); }

  .pg05-cta.is-disabled{
    background:#ddd !important;
    color:#777 !important;
    cursor:default;
    pointer-events:none;
  }

  .section-title{
    font-size: 20px;
    font-weight: 900;
    margin: 18px 0 12px;
    color:var(--text);
  }

  .desc{
    font-size: 13px;
    line-height: 1.9;
    color:#222;
    white-space: pre-wrap;
  }

  /* info table */
  .info{
    margin-top: 10px;
  }
  .info-row{
    display:grid;
    grid-template-columns: 120px 1fr;
    gap: 12px;
    align-items:center;
    margin: 10px 0;
    font-size: 13px;
  }
  .info-key{
    font-weight: 900;
  }
  .chips{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
  }
  .chip{
    background: var(--chip);
    border-radius: 999px;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 800;
    color:#333;
    display:inline-flex;
    align-items:center;
  }

  /* ===== Flash / Error ===== */
  .pg-msg{
    margin:12px 0 16px;
    padding:10px 12px;
    border-radius:8px;
    font-size:13px;
    font-weight:800;
  }
  .pg-msg.ok{background:#eaffea;color:#1b6b1b;}
  .pg-msg.ng{background:#ffecec;color:#c00;}
  .pg-err{
    background:#ffecec;
    color:#c00;
    border-radius:8px;
    padding:10px 12px;
    font-size:13px;
    margin:12px 0 16px;
  }
  .pg-err ul{ margin:0; padding-left:18px; }

  /* ===== comments ===== */
  .comments-title{
    font-size: 22px;
    font-weight: 900;
    color:#777;
    margin: 26px 0 10px;
  }
  .comment{
    display:flex;
    gap:12px;
    align-items:flex-start;
    margin: 12px 0 18px;
  }
  .avatar{
    width:44px;
    height:44px;
    border-radius:50%;
    background:#dcdcdc;
    flex:0 0 auto;
  }
  .comment-body{
    flex:1;
    min-width:0;
  }

  /* 上段：ユーザー名 + 右側に削除 */
  .comment-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    margin: 6px 0 8px;
  }
  .comment-user{
    font-weight:900;
  }
  .comment-meta{
    font-size:11px;
    color:#777;
    margin-left:10px;
    font-weight:700;
  }

  .comment-box{
    background:#e5e5e5;
    border-radius: 4px;
    padding: 10px 12px;
    font-size: 13px;
    color:#333;
    white-space:pre-wrap;
    word-break:break-word;
  }

  /* 削除ボタン（リンクっぽく） */
  .comment-del{
    background:none;
    border:none;
    color:#c00;
    font-size:12px;
    font-weight:900;
    cursor:pointer;
    padding:0;
    line-height:1;
  }
  .comment-del:hover{ text-decoration:underline; }

  /* comment form */
  .comment-form-title{
    font-size: 16px;
    font-weight: 900;
    margin: 18px 0 10px;
  }
  .textarea{
    width:100%;
    height: 160px;
    border:1px solid #999;
    border-radius: 2px;
    padding: 10px 12px;
    font-size:14px;
    outline:none;
    resize: vertical;
  }
  .comment-submit{
    margin-top: 14px;
    width:100%;
    height:40px;
    border:none;
    border-radius:2px;
    background: var(--accent);
    color:#fff;
    font-weight: 900;
    cursor:pointer;

    /* ✅ 押せない対策（上に何か被っても押せるように） */
    position:relative;
    z-index:5;
    pointer-events:auto;
  }
  .comment-submit:hover{ filter:brightness(.97); }

  /* responsive */
  @media (max-width: 860px){
    .pg05-top{
      grid-template-columns: 1fr;
      gap: 22px;
    }
  }
</style>
@endpush

@section('content')
@php
  $likeCount = $likeCount ?? ($item->likes_count ?? 0);

  // Controllerから渡ってこないケースでも落ちないように保険
  $comments = $comments ?? collect();
  $commentCount = $commentCount ?? $comments->count();

  $brand     = $item->brand ?? '';
  $category  = $item->category ?? '';
  $condition = $item->condition ?? '';

  $isSold  = ($item->status ?? '') === 'sold';
  $isOwner = auth()->check() && (int)$item->user_id === (int)auth()->id();

  // ✅ いいね済み判定（押し分け用）
  $liked = auth()->check()
    && \App\Models\Like::where('user_id', auth()->id())
        ->where('item_id', $item->id)
        ->exists();
@endphp

<div class="pg05">

  {{-- バリデーションエラー（コメント投稿など） --}}
  @if ($errors->any())
    <div class="pg-err">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- フラッシュ --}}
  @if (session('error'))
    <div class="pg-msg ng">{{ session('error') }}</div>
  @endif
  @if (session('message'))
    <div class="pg-msg ok">{{ session('message') }}</div>
  @endif

  <div class="pg05-top">

    {{-- 左：商品画像 --}}
    <div class="pg05-image">
      @if (!empty($item->image_path))
        <img src="{{ asset('storage/' . $item->image_path) }}" alt="商品画像">
      @else
        <img src="https://via.placeholder.com/800x800/e0e0e0/666?text=%E5%95%86%E5%93%81%E7%94%BB%E5%83%8F" alt="商品画像">
      @endif
    </div>

    {{-- 右：情報 --}}
    <div>

      <h1 class="pg05-title">{{ $item->title }}</h1>

      {{-- ★ブランド未設定は「未設定」に --}}
      <p class="pg05-brand">{{ $brand !== '' ? $brand : '未設定' }}</p>

      <p class="pg05-price">
        ￥{{ number_format($item->price) }} <small>(税込)</small>
      </p>

      <div class="pg05-icons">

        {{-- ✅ いいね（押せる：ログイン時はPOST/DELETE、未ログインはログインへ） --}}
        @auth
          <form method="POST"
                action="{{ $liked ? route('likes.destroy', ['item_id' => $item->id]) : route('likes.store', ['item_id' => $item->id]) }}"
                style="margin:0;">
            @csrf
            @if($liked)
              @method('DELETE')
            @endif

            <button type="submit" class="pg05-likebtn" aria-label="いいね">
              <svg viewBox="0 0 24 24"
                  fill="{{ $liked ? 'currentColor' : 'none' }}"
                  stroke="currentColor"
                  stroke-width="1.6">
                <path d="M12 21s-7-4.6-9.5-8.5C.7 9.6 2.4 6.6 5.6 6.1c1.7-.3 3.3.4 4.4 1.7 1.1-1.3 2.7-2 4.4-1.7 3.2.5 4.9 3.5 3.1 6.4C19 16.4 12 21 12 21z"/>
              </svg>
              <div>{{ $likeCount }}</div>
            </button>
          </form>
        @endauth

        @guest
          <a class="pg05-likebtn is-link" href="{{ route('login') }}" aria-label="いいね（ログインへ）">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <path d="M12 21s-7-4.6-9.5-8.5C.7 9.6 2.4 6.6 5.6 6.1c1.7-.3 3.3.4 4.4 1.7 1.1-1.3 2.7-2 4.4-1.7 3.2.5 4.9 3.5 3.1 6.4C19 16.4 12 21 12 21z"/>
            </svg>
            <div>{{ $likeCount }}</div>
          </a>
        @endguest

        {{-- コメント数（表示） --}}
        <div class="pg05-icon" aria-label="コメント数">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M21 12c0 4.4-4 8-9 8H7l-4 3 1.5-4.5C3.6 17.2 3 14.7 3 12c0-4.4 4-8 9-8s9 3.6 9 8z"/>
          </svg>
          <div>{{ $commentCount }}</div>
        </div>
      </div>

      {{-- ===== 購入ボタン ===== --}}
      @auth
        @if (!$isSold && !$isOwner)
          <a class="pg05-cta" href="{{ route('items.purchase', ['item_id' => $item->id]) }}">購入手続きへ</a>
        @elseif ($isSold)
          <span class="pg05-cta is-disabled">売り切れ</span>
        @else
          <span class="pg05-cta is-disabled">自分の商品は購入できません</span>
        @endif
      @endauth

      @guest
        <a class="pg05-cta" href="{{ route('login') }}">購入手続きへ</a>
      @endguest

      {{-- 商品説明 --}}
      <div class="section-title">商品説明</div>
      <div class="desc">{{ $item->description }}</div>

      {{-- 商品の情報 --}}
      <div class="section-title" style="margin-top:26px;">商品の情報</div>
      <div class="info">
        <div class="info-row">
          <div class="info-key">カテゴリー</div>
          <div class="chips">
            <span class="chip">{{ $category !== '' ? $category : '未設定' }}</span>
          </div>
        </div>

        <div class="info-row">
          <div class="info-key">商品の状態</div>
          <div>{{ $condition !== '' ? $condition : '未設定' }}</div>
        </div>
      </div>

      {{-- コメント --}}
      <div class="comments-title">コメント({{ $commentCount }})</div>

      @forelse ($comments as $c)
        @php
          $isMyComment = auth()->check() && (int)$c->user_id === (int)auth()->id();

          // ★本文カラム名の違いに強くする（body/comment/content）
          $commentText = $c->body ?? ($c->comment ?? ($c->content ?? ''));
        @endphp

        <div class="comment">
          <div class="avatar"></div>

          <div class="comment-body">
            <div class="comment-head">
              <div class="comment-user">
                {{ $c->user->name ?? 'user' }}
                <span class="comment-meta">
                  {{ $c->created_at ? $c->created_at->format('Y/m/d H:i') : '' }}
                </span>
              </div>

              {{-- ✅ 自分のコメントだけ削除ボタン表示 --}}
              @auth
                @if ($isMyComment)
                  <form method="POST"
                        action="{{ route('comments.destroy', ['comment_id' => $c->id]) }}"
                        onsubmit="return confirm('このコメントを削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <button class="comment-del" type="submit">削除</button>
                  </form>
                @endif
              @endauth
            </div>

            <div class="comment-box">{{ $commentText }}</div>
          </div>
        </div>

      @empty
        <div class="comment">
          <div class="avatar"></div>
          <div class="comment-body">
            <div class="comment-user">admin</div>
            <div class="comment-box">まだコメントはありません。</div>
          </div>
        </div>
      @endforelse

      {{-- コメント投稿 --}}
      <div class="comment-form-title">商品へのコメント</div>

      {{-- ✅ 未ログイン時は「ログインしてコメント」だけ表示 --}}
      @guest
        <a class="pg05-cta" href="{{ route('login') }}" style="margin-top:0;">ログインしてコメントする</a>
      @endguest

      @auth
        <form method="POST" action="{{ route('comments.store', ['item_id' => $item->id]) }}">
          @csrf
          <textarea class="textarea" name="body" required>{{ old('body') }}</textarea>
          <button class="comment-submit" type="submit">コメントを送信する</button>
        </form>
      @endauth

    </div>
  </div>
</div>
@endsection