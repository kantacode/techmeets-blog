## 概要
Week 9：コントローラーのリファクタリング（Repository / Service パターン）と、タスク管理機能を追加しました。

## やったこと
- **実践課題1：タスク管理（/tasks）**
  - ログインした人だけが使える。表示されるのは自分のタスクだけ
  - 追加・編集・削除、完了／未完了の切り替え
  - 本人チェックは TaskPolicy に任せる
- **実践課題2：ProductController のリファクタリング**
  - ProductRequest：入力チェック
  - ProductService：トランザクション、ログ
  - ProductRepository：DB の読み書き
- **PostController のリファクタリング**
  - PostService、PostRepository、PostPolicy に分けた
- **開発環境の高速化**
  - `vendor` を名前付きボリューム（`vendor-data`）に移した
  - nginx の `fastcgi_read_timeout` を延ばした
  - Windows の bind mount が遅く、1ページに約12秒かかって 504 が出ていた。約0.2秒に改善した

## Before / After

### ProductController
**Before**：同じ入力チェックのルールが `store` と `update` の2か所にあり、DB 操作もコントローラーが直接していた。

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name'        => 'required|max:100',
        'price'       => 'required|integer|min:0',
        'description' => 'required',
        'stock'       => 'required|integer|min:0',
        'category'    => 'required|max:50',
    ]);

    $product = Product::create($validated);
    ...
}

public function update(Request $request, Product $product)
{
    $validated = $request->validate([ /* ↑と同じルールをもう一度 */ ]);
    $product->update($validated);
    ...
}
```

**After**：入力チェックは ProductRequest の1か所にまとめた。保存は Service に任せる。

```php
public function store(ProductRequest $request)
{
    $product = $this->productService->createProduct($request->validated());
    ...
}

public function update(ProductRequest $request, Product $product)
{
    $product = $this->productService->updateProduct($product, $request->validated());
    ...
}
```

### PostController
**Before**：本人チェック `if (...) abort(403)` が edit、update、destroy の3か所にあった。

```php
public function destroy(Post $post)
{
    if ($post->user_id !== auth()->id()) {
        abort(403, 'この操作は許可されていません');
    }
    $post->delete();
    ...
}
```

**After**：本人チェックは PostPolicy の1か所にまとめた。削除の手順は Service に任せる。

```php
public function destroy(Post $post)
{
    Gate::authorize('delete', $post);
    $this->postService->deletePost($post);
    ...
}
```

## 役割分担

| クラス | 役割 |
|---|---|
| Controller | リクエストを受け取って担当に渡し、画面を返す |
| FormRequest | 入力チェック |
| Policy | 本人かどうかの判定 |
| Service | 業務の手順（トランザクション、ログ） |
| Repository | DB の読み書き |

## 動作確認
- [x] `php artisan test`：35件すべて成功
- [x] タスクの追加、完了切り替え、編集、削除（ブラウザで確認）
- [x] 商品登録：未入力だとエラーが出る。正しく入力すると登録される（ブラウザで確認）
- [x] ログに `Task created`、`Product created` などが記録される

🤖 Generated with [Claude Code](https://claude.com/claude-code)
