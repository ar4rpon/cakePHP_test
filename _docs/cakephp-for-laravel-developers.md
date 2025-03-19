# CakePHP 開発ガイド（Laravel 経験者向け）

このドキュメントは、Laravel の経験があり CakePHP を初めて使用する開発者向けに、CakePHP の基本的な概念、ディレクトリ構造、および新規機能開発の流れを説明します。

## 目次

1. [CakePHP の概要](#cakephp-の概要)
2. [ディレクトリ構造](#ディレクトリ構造)
3. [MVC アーキテクチャ](#mvc-アーキテクチャ)
4. [ルーティング](#ルーティング)
5. [コントローラー](#コントローラー)
6. [モデルとデータベース](#モデルとデータベース)
7. [ビューとテンプレート](#ビューとテンプレート)
8. [プラグイン](#プラグイン)
9. [認証と認可](#認証と認可)
10. [新規機能開発の流れ](#新規機能開発の流れ)
11. [便利なコマンド](#便利なコマンド)

## CakePHP の概要

CakePHP は PHP 用の高速開発フレームワークで、「規約よりも設定」の原則に従っています。これは Laravel と同様のアプローチですが、いくつかの違いがあります。

**Laravel との主な類似点**:

-   MVC アーキテクチャの採用
-   規約よりも設定の原則
-   ORM の使用
-   テンプレートエンジン
-   ミドルウェアの概念

**Laravel との主な相違点**:

-   Laravel は Blade テンプレートエンジンを使用しますが、CakePHP は PHP ネイティブのテンプレートを使用します
-   Laravel は Eloquent ORM を使用しますが、CakePHP は独自の ORM を使用します
-   ディレクトリ構造と命名規則が異なります
-   Laravel は Artisan コマンドを使用しますが、CakePHP は `bin/cake` コマンドを使用します

## ディレクトリ構造

CakePHP のディレクトリ構造は Laravel と似ていますが、いくつかの違いがあります。

```
/project_root
├── bin/                  # 実行可能ファイル（Laravel の artisan に相当）
├── config/               # 設定ファイル（Laravel の config/ に相当）
│   ├── app.php           # アプリケーション設定（Laravel の config/app.php に相当）
│   ├── routes.php        # ルート定義（Laravel の routes/ ディレクトリに相当）
│   └── ...
├── logs/                 # ログファイル（Laravel の storage/logs/ に相当）
├── plugins/              # プラグイン（Laravel の packages/ に相当）
├── resources/            # リソースファイル
├── src/                  # アプリケーションのソースコード（Laravel の app/ に相当）
│   ├── Application.php   # アプリケーションクラス（Laravel の app/Providers/ に相当）
│   ├── Console/          # コンソールコマンド（Laravel の app/Console/ に相当）
│   ├── Controller/       # コントローラー（Laravel の app/Http/Controllers/ に相当）
│   ├── Model/            # モデル（Laravel の app/Models/ に相当）
│   │   ├── Table/        # テーブルクラス（Laravel のモデルに相当）
│   │   └── Entity/       # エンティティクラス（Laravel のモデルに相当）
│   └── View/             # ビュークラス（Laravel にはない概念）
├── templates/            # ビューテンプレート（Laravel の resources/views/ に相当）
│   ├── layout/           # レイアウトファイル（Laravel の layouts/ に相当）
│   ├── element/          # 再利用可能な UI 要素（Laravel の partials/ に相当）
│   └── [Controller名]/   # コントローラーごとのテンプレート
├── tests/                # テストコード（Laravel の tests/ に相当）
└── webroot/              # 公開ディレクトリ（Laravel の public/ に相当）
    ├── css/              # CSS ファイル
    ├── js/               # JavaScript ファイル
    └── img/              # 画像ファイル
```

### Laravel との対応関係

| CakePHP              | Laravel                     | 説明                 |
| -------------------- | --------------------------- | -------------------- |
| `bin/cake`           | `artisan`                   | コマンドラインツール |
| `config/`            | `config/`                   | 設定ファイル         |
| `config/routes.php`  | `routes/web.php`            | ルート定義           |
| `src/Controller/`    | `app/Http/Controllers/`     | コントローラー       |
| `src/Model/Table/`   | `app/Models/`               | モデル               |
| `src/Model/Entity/`  | `app/Models/`               | エンティティ         |
| `templates/`         | `resources/views/`          | ビューテンプレート   |
| `templates/layout/`  | `resources/views/layouts/`  | レイアウト           |
| `templates/element/` | `resources/views/partials/` | 部分テンプレート     |
| `webroot/`           | `public/`                   | 公開ディレクトリ     |

## MVC アーキテクチャ

CakePHP は Laravel と同様に MVC（Model-View-Controller）アーキテクチャを採用しています。

### Model（モデル）

CakePHP のモデルは、Laravel のモデルと似ていますが、2 つの主要なクラスに分かれています：

1. **Table クラス**（`src/Model/Table/`）：

    - データベーステーブルとの対話を担当
    - リレーションシップ、バリデーション、ビヘイビアを定義
    - Laravel のモデルクラスに相当

    ```php
    // src/Model/Table/ArticlesTable.php
    namespace App\Model\Table;

    use Cake\ORM\Table;

    class ArticlesTable extends Table
    {
        public function initialize(array $config): void
        {
            parent::initialize($config);

            $this->setTable('articles');
            $this->setPrimaryKey('id');

            // リレーションシップ（Laravel の relationships に相当）
            $this->belongsTo('Users');
            $this->hasMany('Comments');

            // ビヘイビア（Laravel のトレイトに相当）
            $this->addBehavior('Timestamp');
        }

        // カスタムファインダー（Laravel のスコープに相当）
        public function findPublished($query, array $options)
        {
            return $query->where(['published' => true]);
        }
    }
    ```

2. **Entity クラス**（`src/Model/Entity/`）：

    - データベースレコードの単一行を表す
    - アクセサとミューテータを定義
    - Laravel のモデルの一部の機能に相当

    ```php
    // src/Model/Entity/Article.php
    namespace App\Model\Entity;

    use Cake\ORM\Entity;

    class Article extends Entity
    {
        // アクセス可能なフィールド（Laravel の $fillable に相当）
        protected $_accessible = [
            'title' => true,
            'body' => true,
            'user_id' => true,
            'published' => true,
        ];

        // アクセサ（Laravel の getXxxAttribute に相当）
        protected function _getTitle($title)
        {
            return ucfirst($title);
        }
    }
    ```

### View（ビュー）

CakePHP のビューは、Laravel の Blade テンプレートとは異なり、PHP ネイティブのテンプレートを使用します。

```php
<!-- templates/Articles/view.php -->
<h1><?= h($article->title) ?></h1>
<p><?= h($article->body) ?></p>

<p>投稿者: <?= $this->Html->link($article->user->name, ['controller' => 'Users', 'action' => 'view', $article->user->id]) ?></p>

<?php if (count($article->comments) > 0): ?>
    <h2>コメント</h2>
    <?php foreach ($article->comments as $comment): ?>
        <div class="comment">
            <p><?= h($comment->body) ?></p>
            <p>投稿者: <?= h($comment->user->name) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
```

**Laravel との違い**:

-   CakePHP は PHP ネイティブのテンプレートを使用（`<?= ?>` 構文）
-   Laravel は Blade テンプレートエンジンを使用（`{{ }}` 構文）
-   CakePHP はヘルパーを `$this->Helper->method()` 形式で呼び出す
-   Laravel はヘルパー関数を直接呼び出す（例：`route()`, `url()`）

### Controller（コントローラー）

CakePHP のコントローラーは、Laravel のコントローラーと似ています。

```php
// src/Controller/ArticlesController.php
namespace App\Controller;

class ArticlesController extends AppController
{
    public function index()
    {
        // モデルからデータを取得（Laravel の Eloquent に相当）
        $articles = $this->Articles->find('all', [
            'contain' => ['Users'],
        ]);

        // ビューに変数を渡す（Laravel の with() に相当）
        $this->set(compact('articles'));
    }

    public function view($id = null)
    {
        // 特定の記事を取得（Laravel の find() に相当）
        $article = $this->Articles->get($id, [
            'contain' => ['Users', 'Comments.Users'],
        ]);

        $this->set(compact('article'));
    }
}
```

**Laravel との違い**:

-   CakePHP は `$this->ModelName` でモデルにアクセス
-   Laravel はモデルをインポートして使用
-   CakePHP は `$this->set()` でビューに変数を渡す
-   Laravel は `return view()->with()` または `return view()->compact()` を使用

## ルーティング

CakePHP のルーティングは、Laravel のルーティングと似ていますが、構文が異なります。

```php
// config/routes.php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        // 静的ページのルート（Laravel の Route::get() に相当）
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

        // パラメータ付きのルート（Laravel の Route::get('/user/{id}') に相当）
        $builder->connect('/articles/{id}', ['controller' => 'Articles', 'action' => 'view'])
            ->setPatterns(['id' => '\d+'])
            ->setPass(['id']);

        // RESTful リソース（Laravel の Route::resource() に相当）
        $builder->resources('Articles');

        // デフォルトのルート（Laravel のルートにはない概念）
        $builder->fallbacks();
    });
};
```

**Laravel との違い**:

-   CakePHP は `connect()` メソッドを使用
-   Laravel は `Route::get()`, `Route::post()` などを使用
-   CakePHP は `resources()` メソッドで RESTful リソースを定義
-   Laravel は `Route::resource()` を使用
-   CakePHP は `fallbacks()` メソッドでデフォルトのルートを定義
-   Laravel にはデフォルトのルートの概念がない

### デフォルトのルーティング規約

CakePHP は、`fallbacks()` メソッドを使用して、以下のようなデフォルトのルーティング規約を提供します：

-   `/controller` → `Controller::index()`
-   `/controller/action` → `Controller::action()`
-   `/controller/action/param1/param2` → `Controller::action(param1, param2)`

これは Laravel にはない概念で、明示的にルートを定義しなくても、規約に従った URL でアクセスできます。

## コントローラー

CakePHP のコントローラーは、Laravel のコントローラーと似ていますが、いくつかの違いがあります。

```php
// src/Controller/ArticlesController.php
namespace App\Controller;

use Cake\Http\Exception\NotFoundException;

class ArticlesController extends AppController
{
    // 初期化メソッド（Laravel の __construct() に相当）
    public function initialize(): void
    {
        parent::initialize();

        // コンポーネントのロード（Laravel のミドルウェアに相当）
        $this->loadComponent('Flash');
        $this->loadComponent('Auth');

        // アクションの前後に実行されるコールバック（Laravel のミドルウェアに相当）
        $this->Auth->allow(['index', 'view']);
    }

    // アクションメソッド（Laravel のコントローラーメソッドに相当）
    public function index()
    {
        $articles = $this->Articles->find('all');
        $this->set(compact('articles'));
    }

    public function view($id = null)
    {
        if ($id === null) {
            throw new NotFoundException(__('記事が見つかりません'));
        }

        $article = $this->Articles->get($id);
        $this->set(compact('article'));
    }

    public function add()
    {
        $article = $this->Articles->newEmptyEntity();

        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            $article->user_id = $this->Auth->user('id');

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('記事が保存されました'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('記事を保存できませんでした'));
        }

        $this->set(compact('article'));
    }
}
```

**Laravel との違い**:

-   CakePHP は `initialize()` メソッドでコントローラーを初期化
-   Laravel は `__construct()` メソッドを使用
-   CakePHP は `loadComponent()` でコンポーネントをロード
-   Laravel は `middleware()` メソッドでミドルウェアを適用
-   CakePHP は `$this->request->is()` でリクエストメソッドをチェック
-   Laravel は `$request->isMethod()` を使用
-   CakePHP は `$this->redirect()` でリダイレクト
-   Laravel は `return redirect()` を使用

### コンポーネント

CakePHP のコンポーネントは、Laravel のミドルウェアやサービスに相当します。

```php
// src/Controller/Component/MyComponent.php
namespace App\Controller\Component;

use Cake\Controller\Component;

class MyComponent extends Component
{
    // デフォルト設定（Laravel のミドルウェアのデフォルト設定に相当）
    protected $_defaultConfig = [
        'key' => 'value',
    ];

    // 初期化メソッド（Laravel のミドルウェアの handle() に相当）
    public function initialize(array $config): void
    {
        parent::initialize($config);
        // 初期化処理
    }

    // カスタムメソッド
    public function doSomething()
    {
        // 処理
    }
}
```

**Laravel との違い**:

-   CakePHP のコンポーネントはコントローラーに関連付けられる
-   Laravel のミドルウェアはリクエスト/レスポンスのパイプラインに関連付けられる
-   CakePHP のコンポーネントはコントローラー内で `$this->MyComponent->method()` として使用
-   Laravel のサービスは依存性注入または `app()` ヘルパーを通じてアクセス

## モデルとデータベース

CakePHP のモデルは、Laravel のモデルと似ていますが、Table クラスと Entity クラスに分かれています。

### テーブルクラス

```php
// src/Model/Table/ArticlesTable.php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ArticlesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('articles');
        $this->setPrimaryKey('id');

        // リレーションシップ（Laravel のリレーションシップに相当）
        $this->belongsTo('Users');
        $this->hasMany('Comments');

        // ビヘイビア（Laravel のトレイトに相当）
        $this->addBehavior('Timestamp');
    }

    // バリデーション（Laravel の $rules に相当）
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

        return $validator;
    }

    // カスタムファインダー（Laravel のスコープに相当）
    public function findPublished($query, array $options)
    {
        return $query->where(['published' => true]);
    }
}
```

**Laravel との違い**:

-   CakePHP は Table クラスでリレーションシップを定義
-   Laravel はモデルクラスでリレーションシップを定義
-   CakePHP は `validationDefault()` メソッドでバリデーションを定義
-   Laravel は `$rules` プロパティまたは Form Request クラスを使用
-   CakePHP は `findXxx()` メソッドでカスタムファインダーを定義
-   Laravel は `scopeXxx()` メソッドでスコープを定義

### エンティティクラス

```php
// src/Model/Entity/Article.php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Article extends Entity
{
    // アクセス可能なフィールド（Laravel の $fillable に相当）
    protected $_accessible = [
        'title' => true,
        'body' => true,
        'user_id' => true,
        'published' => true,
        'user' => true,
        'comments' => true,
    ];

    // 非表示フィールド（Laravel の $hidden に相当）
    protected $_hidden = [
        'password',
    ];

    // アクセサ（Laravel の getXxxAttribute に相当）
    protected function _getTitle($title)
    {
        return ucfirst($title);
    }

    // ミューテータ（Laravel の setXxxAttribute に相当）
    protected function _setTitle($title)
    {
        return strtolower($title);
    }
}
```

**Laravel との違い**:

-   CakePHP は Entity クラスでアクセサとミューテータを定義
-   Laravel はモデルクラスでアクセサとミューテータを定義
-   CakePHP は `_getXxx()` と `_setXxx()` メソッドを使用
-   Laravel は `getXxxAttribute()` と `setXxxAttribute()` メソッドを使用

### クエリビルダー

CakePHP のクエリビルダーは、Laravel のクエリビルダーと似ていますが、構文が異なります。

```php
// CakePHP のクエリビルダー
$query = $this->Articles->find()
    ->select(['id', 'title', 'body'])
    ->where(['published' => true])
    ->order(['created' => 'DESC'])
    ->limit(10);

// Laravel のクエリビルダー
$query = Article::select(['id', 'title', 'body'])
    ->where('published', true)
    ->orderBy('created', 'desc')
    ->limit(10);
```

**Laravel との違い**:

-   CakePHP は `$this->ModelName->find()` からクエリを開始
-   Laravel は `Model::query()` または直接 `Model::` からクエリを開始
-   CakePHP は `where()` に連想配列を渡す
-   Laravel は `where()` にカラム名、演算子、値を渡す

## ビューとテンプレート

CakePHP のビューは、Laravel の Blade テンプレートとは異なり、PHP ネイティブのテンプレートを使用します。

### レイアウト

```php
<!-- templates/layout/default.php -->
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <title><?= $this->fetch('title') ?></title>
    <?= $this->Html->css('app') ?>
    <?= $this->fetch('css') ?>
</head>
<body>
    <header>
        <!-- ヘッダーコンテンツ -->
    </header>

    <main>
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>

    <footer>
        <!-- フッターコンテンツ -->
    </footer>

    <?= $this->Html->script('app') ?>
    <?= $this->fetch('script') ?>
</body>
</html>
```

**Laravel との違い**:

-   CakePHP は `$this->fetch('content')` でコンテンツを表示
-   Laravel は `@yield('content')` を使用
-   CakePHP は `$this->Html->css()` と `$this->Html->script()` でアセットを読み込む
-   Laravel は `<link>` と `<script>` タグを直接使用するか、`@vite` ディレクティブを使用

### テンプレート

```php
<!-- templates/Articles/index.php -->
<?php $this->assign('title', '記事一覧'); ?>

<h1>記事一覧</h1>

<?php if (count($articles) > 0): ?>
    <ul>
        <?php foreach ($articles as $article): ?>
            <li>
                <?= $this->Html->link(
                    $article->title,
                    ['controller' => 'Articles', 'action' => 'view', $article->id]
                ) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>記事がありません。</p>
<?php endif; ?>

<?= $this->Html->link(
    '新規記事を作成',
    ['controller' => 'Articles', 'action' => 'add'],
    ['class' => 'button']
) ?>
```

**Laravel との違い**:

-   CakePHP は `$this->assign('title', '記事一覧')` でタイトルを設定
-   Laravel は `@section('title', '記事一覧')` を使用
-   CakePHP は `$this->Html->link()` でリンクを生成
-   Laravel は `{{ route('articles.show', $article->id) }}` を使用

### エレメント（部分テンプレート）

```php
<!-- templates/element/article_card.php -->
<div class="article-card">
    <h2><?= h($article->title) ?></h2>
    <p><?= h($article->body) ?></p>
    <p>投稿者: <?= h($article->user->name) ?></p>
    <p>投稿日: <?= h($article->created) ?></p>
</div>
```

```php
<!-- templates/Articles/index.php -->
<?php foreach ($articles as $article): ?>
    <?= $this->element('article_card', ['article' => $article]) ?>
<?php endforeach; ?>
```

**Laravel との違い**:

-   CakePHP は `$this->element()` で部分テンプレートを読み込む
-   Laravel は `@include()` を使用

### ヘルパー

CakePHP のヘルパーは、Laravel のヘルパー関数に相当します。

```php
// HTML ヘルパー
<?= $this->Html->link('リンクテキスト', ['controller' => 'Articles', 'action' => 'view', $id]) ?>
<?= $this->Html->image('logo.png', ['alt' => 'ロゴ']) ?>

// フォームヘルパー
<?= $this->Form->create($article) ?>
<?= $this->Form->control('title') ?>
<?= $this->Form->control('body', ['type' => 'textarea']) ?>
<?= $this->Form->button('保存') ?>
<?= $this->Form->end() ?>

// URL ヘルパー
<?= $this->Url->build(['controller' => 'Articles', 'action' => 'view', $id]) ?>
```

**Laravel との違い**:

-   CakePHP は `$this->Helper->method()` 形式でヘルパーを呼び出す
-   Laravel はヘルパー関数を直接呼び出す（例：`route()`, `url()`）
-   CakePHP のヘルパーはクラスベース
-   Laravel のヘルパーは関数ベース

## プラグイン

CakePHP のプラグインは、Laravel のパッケージに相当します。

### プラグインのインストール

```bash
# Composer を使用してプラグインをインストール
composer require cakephp/authentication
```

### プラグインのロード

```php
// src/Application.php
public function bootstrap(): void
{
    parent::bootstrap();

    // プラグインのロード（Laravel の config/app.php の providers に相当）
    $this->addPlugin('Authentication');
}
```

**Laravel との違い**:

-   CakePHP は `addPlugin()` メソッドでプラグインをロード
-   Laravel は `config/app.php` の `providers` 配列にサービスプロバイダーを追加

## 認証と認可

CakePHP 4.x では、認証と認可は別々のプラグインとして提供されています。

### 認証

```php
// src/Application.php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    // 認証ミドルウェアの追加
    $middlewareQueue->add(new AuthenticationMiddleware($this));

    return $middlewareQueue;
}

public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
{
    $service = new AuthenticationService();

    // 認証の設定
    $service->loadIdentifier('Authentication.Password', [
        'fields' => [
            'username' => 'email',
            'password' => 'password',
        ],
    ]);

    $service->loadAuthenticator('Authentication.Session');
    $service->loadAuthenticator('Authentication.Form', [
        'fields' => [
            'username' => 'email',
            'password' => 'password',
        ],
        'loginUrl' => '/users/login',
    ]);

    return $service;
}
```

**Laravel との違い**:

-   CakePHP は Authentication プラグインを使用
-   Laravel は Auth ファサードと config/auth.php を使用
-   CakePHP は認証サービスを明示的に設定
-   Laravel はデフォルトの認証設定を提供

### 認可

```php
// src/Application.php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    // 認可ミドルウェアの追加
    $middlewareQueue->add(new AuthorizationMiddleware($this));

    return $middlewareQueue;
}

public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
{
    $service = new AuthorizationService();

    // 認可の設定
    $resolver = new OrmResolver();
    $service->setPolicy(Article::class, ArticlePolicy::class);

    return $service;
}
```

**Laravel との違い**:

-   CakePHP は Authorization プラグインを使用
-   Laravel は Gate ファサードと Policies を使用
-   CakePHP は認可サービスを明示的に設定
-   Laravel はデフォルトの認可設定を提供

## 新規機能開発の流れ

CakePHP での新規機能開発の一般的な流れは以下の通りです。

### 1. データベースの設計と作成

```sql
-- データベーステーブルの作成
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    user_id INT NOT NULL,
    published BOOLEAN DEFAULT FALSE,
    created DATETIME,
    modified DATETIME
);
```

**Laravel との違い**:

-   CakePHP は手動で SQL を書くか、Migrations プラグインを使用
-   Laravel は Migrations を標準で提供

### 2. モデルの作成

```bash
# モデルの作成（Laravel の php artisan make:model に相当）
bin/cake bake model Articles
```

これにより、以下のファイルが生成されます：

-   `src/Model/Table/ArticlesTable.php`
-   `src/Model/Entity/Article.php`
-   `tests/TestCase/Model/Table/ArticlesTableTest.php`

**Laravel との違い**:

-   CakePHP は `bin/cake bake` コマンドを使用
-   Laravel は `php artisan make:xxx` コマンドを使用
-   CakePHP は Table クラスと Entity クラスを別々に生成
-   Laravel は単一のモデルクラスを生成

### 3. コントローラーの作成

```bash
# コントローラーの作成（Laravel の php artisan make:controller に相当）
bin/cake bake controller Articles
```

これにより、以下のファイルが生成されます：

-   `src/Controller/ArticlesController.php`
-   `tests/TestCase/Controller/ArticlesControllerTest.php`

**Laravel との違い**:

-   CakePHP は `bin/cake bake controller` コマンドを使用
-   Laravel は `php artisan make:controller` コマンドを使用

### 4. ビューの作成

```bash
# ビューの作成（Laravel の php artisan make:view に相当）
bin/cake bake template Articles
```

これにより、以下のファイルが生成されます：

-   `templates/Articles/index.php`
-   `templates/Articles/view.php`
-   `templates/Articles/add.php`
-   `templates/Articles/edit.php`

**Laravel との違い**:

-   CakePHP は `bin/cake bake template` コマンドを使用
-   Laravel は `php artisan make:view` コマンドを使用
-   CakePHP は複数のビューテンプレートを一度に生成
-   Laravel は単一のビューテンプレートを生成

### 5. ルーティングの設定

```php
// config/routes.php
$routes->scope('/', function (RouteBuilder $builder): void {
    // RESTful リソースの追加（Laravel の Route::resource() に相当）
    $builder->resources('Articles');

    $builder->fallbacks();
});
```

**Laravel との違い**:

-   CakePHP は `resources()` メソッドで RESTful リソースを定義
-   Laravel は `Route::resource()` を使用

### 6. 全てを一度に生成

```bash
# モデル、コントローラー、ビューを一度に生成（Laravel にはない機能）
bin/cake bake all Articles
```

**Laravel との違い**:

-   CakePHP は `bin/cake bake all` コマンドで全てを一度に生成
-   Laravel にはこの機能がなく、個別にコマンドを実行する必要がある

## 便利なコマンド

CakePHP には、開発を効率化するための便利なコマンドがあります。

```bash
# サーバーの起動（Laravel の php artisan serve に相当）
bin/cake server

# データベースのマイグレーション（Laravel の php artisan migrate に相当）
bin/cake migrations migrate

# シェルの作成（Laravel の php artisan make:command に相当）
bin/cake bake shell MyShell

# シェルの実行（Laravel の php artisan command:name に相当）
bin/cake my_shell

# キャッシュのクリア（Laravel の php artisan cache:clear に相当）
bin/cake cache clear_all

# ルートの一覧表示（Laravel の php artisan route:list に相当）
bin/cake routes
```

**Laravel との違い**:

-   CakePHP は `bin/cake` コマンドを使用
-   Laravel は `php artisan` コマンドを使用
-   コマンド名と引数の形式が異なる

## まとめ

CakePHP と Laravel は、どちらも PHP の高速開発フレームワークであり、MVC アーキテクチャを採用しています。主な違いは、ディレクトリ構造、命名規則、テンプレートエンジン、ORM の実装などにあります。

Laravel の経験があれば、CakePHP の概念は比較的簡単に理解できるでしょう。両方のフレームワークは「規約よりも設定」の原則に従っており、開発者が迅速にアプリケーションを構築できるように設計されています。

CakePHP の詳細については、[公式ドキュメント](https://book.cakephp.org/4/ja/) を参照してください。
