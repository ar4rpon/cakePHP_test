# CakePHP のページルーティングの流れ

CakePHP のルーティングは、以下の要素が連携して動作します。

1.  **リクエスト**: ユーザーがブラウザから特定の URL にアクセスします。
2.  **ルーター (config/routes.php)**: URL を解析し、どのコントローラーとアクションを呼び出すかを決定します。
3.  **コントローラー**: リクエストを処理し、モデルを通じてデータを取得または更新し、ビューにデータを渡します。
4.  **ビュー**: コントローラーから渡されたデータを使って、HTML などのレスポンスを生成します。

## config/routes.php

`config/routes.php`には、ルーティングの設定が記述されています。

-   `/`にアクセスすると、`PagesController`の`display`アクションが実行され、`home`という引数が渡されます。
-   `/pages/*`にアクセスすると、`PagesController`の`display`アクションが実行され、`*`の部分が引数として渡されます。
-   `/articles/tagged/*`にアクセスすると、`ArticlesController`の`tags`アクションが実行され、`*`の部分が引数として渡されます。
-   `$builder->fallbacks()`は、上記以外の URL に対するデフォルトのルーティングを設定します。

## fallbacks()によるルーティング

`$builder->fallbacks()`は、明示的に定義されたルートに一致しないすべてのリクエストを処理するための、デフォルトのルーティングルールを提供します。

`fallbacks()`は以下の処理を行います。

1.  **コントローラー名の推測**: URL の最初のセグメントをコントローラー名として推測します。例えば、`/tags/index`の場合、`TagsController`を推測します。
2.  **アクション名の推測**: URL の 2 番目のセグメントをアクション名として推測します。例えば、`/tags/index`の場合、`index`アクションを推測します。
3.  **パラメータの引き渡し**: URL の残りのセグメントを、アクションのパラメータとして渡します。

したがって、`/tags/index`にアクセスすると、`TagsController`の`index`アクションが実行されます。同様に、`/users/add`にアクセスすると、`UsersController`の`add`アクションが実行されます。

`/users/index`が実行されるのは、`/pages/*`の設定によるものではありません。`/pages/*`のルーティングは、`PagesController`の`display`アクションにのみ適用されます。

`fallbacks()`は、URL の最初のセグメントをコントローラー名として推測し、2 番目のセグメントをアクション名として推測します。

## ArticlesController の tags アクション

`ArticlesController.php`の`tags`アクションは、可変長引数`...$tags`を受け取ります。これは、URL `/articles/tagged/tag1/tag2`のように、複数のタグを URL に含めることができることを意味します。

```php
public function tags(...$tags)
{
    // ArticlesTable を使用してタグ付きの記事を検索します。
    $articles = $this->Articles->find('tagged', [
        'tags' => $tags
    ])
        ->all();

    // 変数をビューテンプレートのコンテキストに渡します。
    $this->set([
        'articles' => $articles,
        'tags' => $tags
    ]);
}
```

`$this->Articles->find('tagged', ...)`を呼び出して、タグに関連付けられた記事を検索しています。`find('tagged')`は、`ArticlesTable`で定義されたカスタムファインダーです。

## ArticlesTable の tagged ファインダー

`ArticlesTable.php`の`findTagged`メソッドは、`$options['tags']`に基づいて記事を検索します。

-   `$options['tags']`が空の場合、タグのない記事を検索します。
-   `$options['tags']`にタグが含まれている場合、指定されたタグのいずれかを持つ記事を検索します。

```php
public function findTagged(Query $query, array $options)
{
    $columns = [
        'Articles.id',
        'Articles.user_id',
        'Articles.title',
        'Articles.body',
        'Articles.published',
        'Articles.created',
        'Articles.slug',
    ];

    $query = $query
        ->select($columns)
        ->distinct($columns);

    if (empty($options['tags'])) {
        // タグが指定されていない場合は、タグのない記事を検索します。
        $query->leftJoinWith('Tags')
            ->where(['Tags.title IS' => null]);
    } else {
        // 提供されたタグが1つ以上ある記事を検索します。
        $query->innerJoinWith('Tags')
            ->where(['Tags.title IN' => $options['tags']]);
    }

    return $query->group(['Articles.id']);
}
```

## templates/Articles/tags.php

`templates/Articles/tags.php`では、コントローラーから渡された`$articles`と`$tags`変数を使用して、HTML を生成しています。

```php
<h1>
    Articles tagged with
    <?= $this->Text->toList(h($tags), 'or') ?>
</h1>

<section>
    <?php foreach ($articles as $article): ?>
        <article>
            <!-- リンクの作成に HtmlHelper を使用 -->
            <h4><?= $this->Html->link(
                    $article->title,
                    ['controller' => 'Articles', 'action' => 'view', $article->slug]
                ) ?></h4>
            <span><?= h($article->created) ?></span>
        </article>
    <?php endforeach; ?>
</section>
```

## まとめ

`/articles/tagged/*`にアクセスしたときのページルーティングの流れは以下の通りです。

1.  URL `/articles/tagged/tag1/tag2`にアクセスします。
2.  `config/routes.php`のルーティング設定により、`ArticlesController`の`tags`アクションが実行されます。
3.  `tags`アクションは、`ArticlesTable`の`findTagged`メソッドを呼び出して、`tag1`または`tag2`に関連付けられた記事を検索します。
4.  `tags`アクションは、検索された記事とタグを`templates/Articles/tags.php`に渡します。
5.  `templates/Articles/tags.php`は、記事のリストとタグを表示する HTML を生成します。
