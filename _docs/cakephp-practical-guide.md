# CakePHP 実践開発ガイド

このガイドでは、CakePHP を使った実際の開発手順を、シンプルなブログ機能を例に説明します。Laravel 経験者が CakePHP での開発フローを理解するための実践的なガイドです。

## 目次

1. [開発環境のセットアップ](#開発環境のセットアップ)
2. [ブログ機能の要件](#ブログ機能の要件)
3. [データベースの設計](#データベースの設計)
4. [モデルの作成](#モデルの作成)
5. [コントローラーの作成](#コントローラーの作成)
6. [ビューの作成](#ビューの作成)
7. [ルーティングの設定](#ルーティングの設定)
8. [動作確認](#動作確認)
9. [機能拡張](#機能拡張)

## 開発環境のセットアップ

まず、Docker を使って CakePHP の開発環境をセットアップします。

### 1. 環境変数の設定

`.env` ファイルを作成し、必要な環境変数を設定します。

```bash
cp .env.example .env
```

`.env` ファイルを編集して、以下のような設定を行います。

```
WEB_PORT=8080
DB_PORT=3306
PMA_PORT=8085
DB_NAME=blog_db
DB_USER=blog_user
DB_PASS=secret
```

### 2. アプリケーション設定

`config/app_local.php` ファイルを作成し、アプリケーションの設定を行います。

```bash
cp config/app_local.example.php config/app_local.php
```

`config/app_local.php` ファイルを編集して、データベース接続設定を行います。

```php
'Datasources' => [
    'default' => [
        'host' => 'db',
        'username' => 'blog_user',
        'password' => 'secret',
        'database' => 'blog_db',
    ],
],
```

### 3. Docker コンテナの起動

Docker コンテナを起動します。

```bash
docker-compose up -d
```

### 4. 依存パッケージのインストール

Composer を使って依存パッケージをインストールします。

```bash
docker-compose exec web composer install
```

## ブログ機能の要件

今回作成するブログ機能の要件は以下の通りです。

1. 記事の一覧表示
2. 記事の詳細表示
3. 記事の作成
4. 記事の編集
5. 記事の削除
6. カテゴリ別の記事一覧表示

## データベースの設計

ブログ機能に必要なテーブルを設計します。

### 1. マイグレーションプラグインのインストール

```bash
docker-compose exec web composer require cakephp/migrations
```

### 2. マイグレーションファイルの作成

```bash
docker-compose exec web bin/cake bake migration CreateArticles
```

`config/Migrations/YYYYMMDDHHMMSS_CreateArticles.php` ファイルを編集します。

```php
public function change()
{
    $table = $this->table('articles');
    $table->addColumn('title', 'string', [
        'limit' => 255,
        'null' => false,
    ]);
    $table->addColumn('body', 'text', [
        'null' => false,
    ]);
    $table->addColumn('category_id', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('published', 'boolean', [
        'default' => false,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => true,
    ]);
    $table->create();
}
```

カテゴリテーブルのマイグレーションファイルも作成します。

```bash
docker-compose exec web bin/cake bake migration CreateCategories
```

`config/Migrations/YYYYMMDDHHMMSS_CreateCategories.php` ファイルを編集します。

```php
public function change()
{
    $table = $this->table('categories');
    $table->addColumn('name', 'string', [
        'limit' => 100,
        'null' => false,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => true,
    ]);
    $table->create();
}
```

### 3. マイグレーションの実行

```bash
docker-compose exec web bin/cake migrations migrate
```

### 4. シードデータの作成

カテゴリのシードデータを作成します。

```bash
docker-compose exec web bin/cake bake seed Categories
```

`config/Seeds/CategoriesSeed.php` ファイルを編集します。

```php
public function run()
{
    $data = [
        [
            'name' => '技術',
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ],
        [
            'name' => 'マーケティング',
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ],
        [
            'name' => 'デザイン',
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ],
    ];

    $table = $this->table('categories');
    $table->insert($data)->save();
}
```

記事のシードデータも作成します。

```bash
docker-compose exec web bin/cake bake seed Articles
```

`config/Seeds/ArticlesSeed.php` ファイルを編集します。

```php
public function run()
{
    $data = [
        [
            'title' => 'CakePHP 入門',
            'body' => 'CakePHP は PHP のフレームワークです。',
            'category_id' => 1,
            'published' => true,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ],
        [
            'title' => 'マーケティング戦略',
            'body' => 'マーケティング戦略について解説します。',
            'category_id' => 2,
            'published' => true,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ],
        [
            'title' => 'UI デザインのポイント',
            'body' => 'UI デザインのポイントについて解説します。',
            'category_id' => 3,
            'published' => true,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ],
    ];

    $table = $this->table('articles');
    $table->insert($data)->save();
}
```

### 5. シードデータの実行

```bash
docker-compose exec web bin/cake migrations seed
```

## モデルの作成

### 1. モデルの生成

```bash
docker-compose exec web bin/cake bake model Articles
docker-compose exec web bin/cake bake model Categories
```

### 2. モデルの編集

`src/Model/Table/ArticlesTable.php` ファイルを編集して、バリデーションとリレーションシップを設定します。

```php
public function initialize(array $config): void
{
    parent::initialize($config);

    $this->setTable('articles');
    $this->setDisplayField('title');
    $this->setPrimaryKey('id');

    $this->addBehavior('Timestamp');

    $this->belongsTo('Categories', [
        'foreignKey' => 'category_id',
    ]);
}

public function validationDefault(Validator $validator): Validator
{
    $validator
        ->integer('id')
        ->allowEmptyString('id', null, 'create');

    $validator
        ->scalar('title')
        ->maxLength('title', 255)
        ->requirePresence('title', 'create')
        ->notEmptyString('title');

    $validator
        ->scalar('body')
        ->requirePresence('body', 'create')
        ->notEmptyString('body');

    $validator
        ->boolean('published')
        ->notEmptyString('published');

    return $validator;
}
```

`src/Model/Table/CategoriesTable.php` ファイルも編集します。

```php
public function initialize(array $config): void
{
    parent::initialize($config);

    $this->setTable('categories');
    $this->setDisplayField('name');
    $this->setPrimaryKey('id');

    $this->addBehavior('Timestamp');

    $this->hasMany('Articles', [
        'foreignKey' => 'category_id',
    ]);
}

public function validationDefault(Validator $validator): Validator
{
    $validator
        ->integer('id')
        ->allowEmptyString('id', null, 'create');

    $validator
        ->scalar('name')
        ->maxLength('name', 100)
        ->requirePresence('name', 'create')
        ->notEmptyString('name');

    return $validator;
}
```

## コントローラーの作成

### 1. コントローラーの生成

```bash
docker-compose exec web bin/cake bake controller Articles
docker-compose exec web bin/cake bake controller Categories
```

### 2. コントローラーの編集

`src/Controller/ArticlesController.php` ファイルを編集して、アクションを実装します。

```php
public function index()
{
    $this->paginate = [
        'contain' => ['Categories'],
        'order' => ['Articles.created' => 'DESC'],
    ];
    $articles = $this->paginate($this->Articles);

    $this->set(compact('articles'));
}

public function view($id = null)
{
    $article = $this->Articles->get($id, [
        'contain' => ['Categories'],
    ]);

    $this->set(compact('article'));
}

public function add()
{
    $article = $this->Articles->newEmptyEntity();
    if ($this->request->is('post')) {
        $article = $this->Articles->patchEntity($article, $this->request->getData());
        if ($this->Articles->save($article)) {
            $this->Flash->success(__('記事が保存されました。'));

            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error(__('記事を保存できませんでした。もう一度お試しください。'));
    }
    $categories = $this->Articles->Categories->find('list', ['limit' => 200]);
    $this->set(compact('article', 'categories'));
}

public function edit($id = null)
{
    $article = $this->Articles->get($id, [
        'contain' => [],
    ]);
    if ($this->request->is(['patch', 'post', 'put'])) {
        $article = $this->Articles->patchEntity($article, $this->request->getData());
        if ($this->Articles->save($article)) {
            $this->Flash->success(__('記事が保存されました。'));

            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error(__('記事を保存できませんでした。もう一度お試しください。'));
    }
    $categories = $this->Articles->Categories->find('list', ['limit' => 200]);
    $this->set(compact('article', 'categories'));
}

public function delete($id = null)
{
    $this->request->allowMethod(['post', 'delete']);
    $article = $this->Articles->get($id);
    if ($this->Articles->delete($article)) {
        $this->Flash->success(__('記事が削除されました。'));
    } else {
        $this->Flash->error(__('記事を削除できませんでした。もう一度お試しください。'));
    }

    return $this->redirect(['action' => 'index']);
}

public function category($id = null)
{
    $this->paginate = [
        'contain' => ['Categories'],
        'conditions' => ['Articles.category_id' => $id],
        'order' => ['Articles.created' => 'DESC'],
    ];
    $articles = $this->paginate($this->Articles);
    $category = $this->Articles->Categories->get($id);

    $this->set(compact('articles', 'category'));
    $this->render('index');
}
```

## ビューの作成

### 1. ビューの生成

```bash
docker-compose exec web bin/cake bake template Articles
docker-compose exec web bin/cake bake template Categories
```

### 2. ビューの編集

`templates/Articles/index.php` ファイルを編集します。

```php
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article[]|\Cake\Collection\CollectionInterface $articles
 */
?>
<div class="articles index content">
    <h3><?= __('記事一覧') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('title', 'タイトル') ?></th>
                    <th><?= $this->Paginator->sort('category_id', 'カテゴリ') ?></th>
                    <th><?= $this->Paginator->sort('published', '公開') ?></th>
                    <th><?= $this->Paginator->sort('created', '作成日') ?></th>
                    <th class="actions"><?= __('操作') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article): ?>
                <tr>
                    <td><?= $this->Number->format($article->id) ?></td>
                    <td><?= h($article->title) ?></td>
                    <td><?= $article->has('category') ? $this->Html->link($article->category->name, ['controller' => 'Articles', 'action' => 'category', $article->category->id]) : '' ?></td>
                    <td><?= $article->published ? __('はい') : __('いいえ') ?></td>
                    <td><?= h($article->created) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('表示'), ['action' => 'view', $article->id]) ?>
                        <?= $this->Html->link(__('編集'), ['action' => 'edit', $article->id]) ?>
                        <?= $this->Form->postLink(__('削除'), ['action' => 'delete', $article->id], ['confirm' => __('記事 #{0} を削除してもよろしいですか？', $article->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('最初')) ?>
            <?= $this->Paginator->prev('< ' . __('前')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('次') . ' >') ?>
            <?= $this->Paginator->last(__('最後') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('{{page}}/{{pages}} ページ目、{{count}} 件中 {{current}} 件表示')) ?></p>
    </div>
    <div class="button">
        <?= $this->Html->link(__('新規記事'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    </div>
</div>
```

`templates/Articles/view.php` ファイルを編集します。

```php
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('操作') ?></h4>
            <?= $this->Html->link(__('記事一覧'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('記事の編集'), ['action' => 'edit', $article->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('記事の削除'), ['action' => 'delete', $article->id], ['confirm' => __('記事 #{0} を削除してもよろしいですか？', $article->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('新規記事'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="articles view content">
            <h3><?= h($article->title) ?></h3>
            <table>
                <tr>
                    <th><?= __('カテゴリ') ?></th>
                    <td><?= $article->has('category') ? $this->Html->link($article->category->name, ['controller' => 'Articles', 'action' => 'category', $article->category->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('公開') ?></th>
                    <td><?= $article->published ? __('はい') : __('いいえ') ?></td>
                </tr>
                <tr>
                    <th><?= __('作成日') ?></th>
                    <td><?= h($article->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('更新日') ?></th>
                    <td><?= h($article->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('本文') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($article->body)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
```

`templates/Articles/add.php` と `templates/Articles/edit.php` ファイルも必要に応じて編集します。

## ルーティングの設定

`config/routes.php` ファイルを編集して、カテゴリ別の記事一覧表示のルートを追加します。

```php
$routes->scope('/', function (RouteBuilder $builder): void {
    // ... 既存のルート設定 ...

    // カテゴリ別の記事一覧表示
    $builder->connect('/articles/category/{id}', ['controller' => 'Articles', 'action' => 'category'])
        ->setPatterns(['id' => '\d+'])
        ->setPass(['id']);

    // RESTful リソース
    $builder->resources('Articles');
    $builder->resources('Categories');

    $builder->fallbacks();
});
```

## 動作確認

### 1. サーバーの起動

```bash
docker-compose exec web bin/cake server -H 0.0.0.0
```

### 2. ブラウザでアクセス

ブラウザで `http://localhost:8080/articles` にアクセスして、記事一覧が表示されることを確認します。

## 機能拡張

基本的なブログ機能が実装できたら、以下のような機能を追加してみましょう。

### 1. 記事の検索機能

`src/Controller/ArticlesController.php` に検索アクションを追加します。

```php
public function search()
{
    $query = $this->request->getQuery('q');
    if ($query) {
        $this->paginate = [
            'contain' => ['Categories'],
            'conditions' => [
                'OR' => [
                    'Articles.title LIKE' => '%' . $query . '%',
                    'Articles.body LIKE' => '%' . $query . '%',
                ],
            ],
            'order' => ['Articles.created' => 'DESC'],
        ];
        $articles = $this->paginate($this->Articles);
    } else {
        $articles = [];
    }

    $this->set(compact('articles', 'query'));
}
```

`templates/Articles/search.php` ファイルを作成します。

```php
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article[]|\Cake\Collection\CollectionInterface $articles
 * @var string $query
 */
?>
<div class="articles search content">
    <h3><?= __('記事検索') ?></h3>
    <?= $this->Form->create(null, ['type' => 'get']) ?>
    <div class="row">
        <div class="column">
            <?= $this->Form->control('q', ['label' => '検索キーワード', 'value' => $query]) ?>
        </div>
        <div class="column">
            <?= $this->Form->button(__('検索')) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>

    <?php if (!empty($articles)): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->Paginator->sort('id') ?></th>
                        <th><?= $this->Paginator->sort('title', 'タイトル') ?></th>
                        <th><?= $this->Paginator->sort('category_id', 'カテゴリ') ?></th>
                        <th><?= $this->Paginator->sort('published', '公開') ?></th>
                        <th><?= $this->Paginator->sort('created', '作成日') ?></th>
                        <th class="actions"><?= __('操作') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td><?= $this->Number->format($article->id) ?></td>
                        <td><?= h($article->title) ?></td>
                        <td><?= $article->has('category') ? $this->Html->link($article->category->name, ['controller' => 'Articles', 'action' => 'category', $article->category->id]) : '' ?></td>
                        <td><?= $article->published ? __('はい') : __('いいえ') ?></td>
                        <td><?= h($article->created) ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('表示'), ['action' => 'view', $article->id]) ?>
                            <?= $this->Html->link(__('編集'), ['action' => 'edit', $article->id]) ?>
                            <?= $this->Form->postLink(__('削除'), ['action' => 'delete', $article->id], ['confirm' => __('記事 #{0} を削除してもよろしいですか？', $article->id)]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="paginator">
            <ul class="pagination">
                <?= $this->Paginator->first('<< ' . __('最初')) ?>
                <?= $this->Paginator->prev('< ' . __('前')) ?>
                <?= $this->Paginator->numbers() ?>
                <?= $this->Paginator->next(__('次') . ' >') ?>
                <?= $this->Paginator->last(__('最後') . ' >>') ?>
            </ul>
            <p><?= $this->Paginator->counter(__('{{page}}/{{pages}} ページ目、{{count}} 件中 {{current}} 件表示')) ?></p>
        </div>
    <?php elseif ($query): ?>
        <div class="message">
            <?= __('検索結果がありません。') ?>
        </div>
    <?php endif; ?>

    <div class="button">
        <?= $this->Html->link(__('記事一覧に戻る'), ['action' => 'index'], ['class' => 'button float-right']) ?>
    </div>
</div>
```

`templates/layout/default.php` ファイルに検索フォームへのリンクを追加します。

```php
<nav class="top-nav">
    <div class="top-nav-title">
        <a href="<?= $this->Url->build('/') ?>"><span>Cake</span>PHP</a>
    </div>
    <div class="top-nav-links">
        <?= $this->Html->link(__('記事一覧'), ['controller' => 'Articles', 'action' => 'index']) ?>
        <?= $this->Html->link(__('カテゴリ一覧'), ['controller' => 'Categories', 'action' => 'index']) ?>
        <?= $this->Html->link(__('記事検索'), ['controller' => 'Articles', 'action' => 'search']) ?>
    </div>
</nav>
```

### 2. タグ機能の追加

タグ機能を追加するには、以下の手順で実装します。

1. タグテーブルとタグ付けテーブルの作成
2. モデルの作成と関連付け
3. コントローラーの作成
4. ビューの作成

詳細な実装は省略しますが、CakePHP の公式ドキュメントにある [CMS チュートリアル](https://book.cakephp.org/4/ja/tutorials-and-examples/cms/tags-and-users.html) を参考にすると良いでしょう。

## まとめ

このガイドでは、CakePHP を使ったブログ機能の開発手順を説明しました。CakePHP は規約に従って開発することで、少ないコードで多くの機能を実装できます。

Laravel の経験があれば、CakePHP の概念は比較的簡単に理解できるでしょう。両方のフレームワークは「規約よりも設定」の原則に従っており、開発者が迅速にアプリケーションを構築できるように設計されています。

CakePHP の詳細については、[公式ドキュメント](https://book.cakephp.org/4/ja/) を参照してください。
