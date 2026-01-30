# freeMK（フリマアプリ）

COACHTECH 課題：フリマアプリ（Laravel）

---

## 概要
ユーザーが商品を出品し、他ユーザーが購入できるフリマアプリです。  
購入処理は Stripe Checkout を用いて決済を行い、Stripe Webhook により  
購入確定（購入履歴の保存・商品の sold 更新）を行います。

---

## 主な機能

### 認証・ユーザー
- 会員登録 / ログイン / ログアウト
- プロフィール編集
  - プロフィール画像
  - ユーザー名
  - メールアドレス
  - 郵便番号
  - 住所
  - 建物名

### 商品関連
- 商品一覧
  - おすすめ一覧（自分の出品商品は表示されない）
  - マイリスト（いいねした商品）
  - 商品名検索（部分一致）
- 商品詳細
  - 商品情報表示
  - いいね数・コメント数表示
  - コメント投稿（最新順表示）
- 商品出品
  - 画像アップロード
  - カテゴリ
  - 商品状態
  - 商品名
  - ブランド（任意）
  - 商品説明
  - 価格
- 商品削除（出品者のみ）

### 購入機能
- 商品購入（Stripe Checkout）
  - 支払い方法選択（クレジットカード）
  - 配送先住所変更
- 購入完了後
  - 購入履歴に表示
  - 商品は sold 状態に更新

### 決済連携
- Stripe Checkout
- Stripe Webhook（checkout.session.completed）
  - Purchase を paid に更新
  - Item を sold に更新

---

## 使用技術
- PHP 8.1
- Laravel 9.x
- MySQL 8.0
- Nginx
- Docker / Docker Compose
- Stripe（Checkout / Webhook）
- MailHog（開発用メール確認）

---

## 環境構築手順（Docker）

> ✅ `docker compose up -d` は **ローカル端末（Mac/Windows）** で実行してください  
> ※ `docker compose exec php bash` で入った **コンテナ内では docker コマンドは使えません**
> （`bash: docker: command not found` になるのは正常です）

### 1. リポジトリをクローン
```bash
git clone <GitHubリポジトリURL>
cd freeMK