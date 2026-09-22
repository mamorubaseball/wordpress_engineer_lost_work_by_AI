# AI Jobless Engineer WordPress Theme

`ai-jobless-engineer` は「AIで仕事がなくなるエンジニア」向けの独自WordPressブロックテーマです。

## 実装方針

- トップページはB寄りのダーク/濃紺ベースで、水色アクセントを使ったブランド体験にする。
- 中心メッセージは「AIで仕事がなくなる。じゃあ、どうする？」。
- 記事一覧と記事本文はA寄りの白背景にし、note 70% + Zenn 30%の読みやすさを優先する。
- PCでは左サイドバーを共通表示し、スマホではボタンで折りたたむ。
- 主要カテゴリは「AIツール」「開発・プロダクト」「キャリア・働き方」「ライフスタイル」。
- 投稿カードはWordPressのQuery Loopで動的表示し、記事をハードコードしない。
- Productsはカスタム投稿タイプとして実装し、筋トレアプリなど制作物を蓄積できる構造にする。
- ブログ/YouTubeからnote、アプリ、コンサルへの導線を置く。
- SEO、アクセシビリティ、レスポンシブ、パフォーマンスを考慮し、不要な外部依存は使わない。

## 構造

```text
ai-jobless-engineer/
├─ style.css
├─ functions.php
├─ index.php（古いインストーラー向けの互換フォールバック）
├─ theme.json
├─ templates/
│  ├─ front-page.html
│  ├─ index.html
│  ├─ home.html
│  ├─ single.html
│  ├─ archive.html
│  ├─ archive-product.html
│  ├─ page.html
│  ├─ page-products.html
│  ├─ single-product.html
│  └─ 404.html
├─ parts/
│  ├─ sidebar.html
│  ├─ mobile-header.html
│  └─ footer.html
├─ patterns/
│  ├─ note-cta.php
│  └─ profile.php
└─ assets/
   ├─ images/hero-final.jpg
   ├─ images/cta-final.jpg
   └─ js/theme.js
```

## WordPress側で行う初期設定

1. テーマZIPを `外観 > テーマ > 新規追加 > テーマのアップロード` からアップロードする。
2. カテゴリを次のスラッグで作成する。
   - AIツール: `ai-tools`
   - 開発・プロダクト: `development-product`
   - キャリア・働き方: `career-work`
   - ライフスタイル: `lifestyle`
3. 固定ページとして「Blog」「Products」「Profile」「Contact」を作成し、「Blog」を投稿ページに指定する。
4. PICK UPに出したい記事を「ブログのトップに固定」に設定する。
5. Products投稿タイプに、筋トレアプリなどの制作物を追加する。
6. `patterns/note-cta.php` のnoteリンクと、プロフィール文・各SNSリンクを実URLに変更する。

## 表示確認

ローカルWordPress環境がある場合は、`wp-content/themes/ai-jobless-engineer` にテーマフォルダを置いて有効化してください。

テーマ更新後に旧表示が残る場合は、WordPressのキャッシュ系プラグイン、サーバーキャッシュ、CDN、ブラウザキャッシュを順に削除してください。通常はZIPを置き換えて有効化するだけでデザインが適用されます。

このテーマのブロックテンプレートはWordPress 6.5以降、PHP 7.4以降を前提とします。テーマ設定は `theme.json` v2でWordPress 6.5に合わせています。古いインストーラーで `index.php` が必要というエラーが出る場合に備えて、ルートに互換フォールバックを同梱しています。インストールできても画面が簡易表示になる場合は、WordPress本体のバージョンを確認してください。PHP 7.4はサポート終了済みのため、運用時はホスティング側で新しいPHPへ更新することを推奨します。

PC版トップの完成画像は `assets/images/hero-final.jpg`、下部CTAは `assets/images/cta-final.jpg` です。指定されたデザイン素材をWeb用JPEGへ最適化して使用しています。画像内のCTA位置には実際のリンク領域を重ねています。スマホでは可読性を保つため、HTMLテキスト版のヒーローとCTAを表示します。

## WordPress管理画面での運用

### サイドバー

`外観 > エディター > デザイン > パターン > テンプレートパーツ > Sidebar` から確認できます。現在はレイアウトを崩しにくくするためカスタムHTMLで構成しており、文字とリンクはコード表示で編集できます。通常のブロック操作へ変換したい場合は、Navigationブロック化が別途必要です。

### ヒーロー・CTA画像

現在はテーマ同梱画像です。WordPressのメディアライブラリから直接交換する形式ではありません。交換時は `assets/images/hero-final.jpg` と `assets/images/cta-final.jpg` を差し替えてテーマZIPを更新します。管理画面から交換できる形にする場合は、テーマエディター上の画像・カバーブロックへ移行します。

### 記事とカテゴリの紐付け

1. `投稿 > カテゴリー` でカテゴリを作成します。
2. 投稿の編集画面を開き、右側の `投稿 > カテゴリー` で該当カテゴリにチェックを入れます。
3. 更新または公開すると、カテゴリ一覧とトップの記事カードへ自動反映されます。

サイドバーとカテゴリカードは、README冒頭に記載した英字スラッグを前提にリンクしています。

### 注目記事

トップの `PICK UP` はWordPressの固定表示投稿を自動取得します。投稿編集画面の `投稿 > 概要（またはステータス）` で `ブログのトップに固定` をオンにしてください。投稿一覧の `クイック編集 > この投稿を先頭に固定` からも設定できます。最大3件を表示します。

最終成果物は `dist/ai-jobless-engineer.zip` です。
