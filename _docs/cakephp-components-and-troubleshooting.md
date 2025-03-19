# CakePHP 主要コンポーネントとトラブルシューティング

このドキュメントでは、CakePHP の主要コンポーネントの詳細な説明と、開発中によく遭遇する問題のトラブルシューティングガイドを提供します。

## 目次

1. [主要コンポーネント](#主要コンポーネント)
    - [リクエスト/レスポンスサイクル](#リクエストレスポンスサイクル)
    - [ミドルウェア](#ミドルウェア)
    - [コントローラーコンポーネント](#コントローラーコンポーネント)
    - [ビューヘルパー](#ビューヘルパー)
    - [ORM](#orm)
    - [バリデーション](#バリデーション)
    - [認証と認可](#認証と認可)
    - [キャッシュ](#キャッシュ)
    - [ロギング](#ロギング)
    - [セッション](#セッション)
    - [イベントシステム](#イベントシステム)
2. [トラブルシューティング](#トラブルシューティング)
    - [一般的なエラー](#一般的なエラー)
    - [データベース接続の問題](#データベース接続の問題)
    - [ルーティングの問題](#ルーティングの問題)
    - [ビューの問題](#ビューの問題)
    - [フォーム送信の問題](#フォーム送信の問題)
    - [認証の問題](#認証の問題)
    - [パフォーマンスの問題](#パフォーマンスの問題)
3. [Laravel との比較](#laravel-との比較)
    - [概念の対応関係](#概念の対応関係)
    - [機能の違い](#機能の違い)
    - [移行のヒント](#移行のヒント)

## 主要コンポーネント

### リクエスト/レスポンスサイクル

CakePHP のリクエスト/レスポンスサイクルは、以下の流れで処理されます。

1. **リクエストの受付**: Web サーバーがリクエストを受け付けます。
2. **ディスパッチャー**: `webroot/index.php` がリクエストを `Cake\Http\Server` に渡します。
3. **アプリケーション**: `src/Application.php` がリクエストを処理します。
4. **ミドルウェア**: リクエストがミドルウェアスタックを通過します。
5. **ルーティング**: リクエストが適切なコントローラーとアクションにルーティングされます。
6. **コントローラー**: コントローラーがリクエストを処理し、モデルを使用してデータを取得または更新します。
7. **ビュー**: コントローラーがビューをレンダリングします。
8. **レスポンス**: レスポンスがクライアントに返されます。

**Laravel との比較**:

-   Laravel も同様のリクエスト/レスポンスサイクルを持っていますが、`public/index.php` から始まり、`App\Http\Kernel` を通過します。
-   CakePHP は `Application` クラスでミドルウェアを設定しますが、Laravel は `App\Http\Kernel` クラスで設定します。

### ミドルウェア

CakePHP のミドルウェアは、リクエストとレスポンスを処理するための仕組みです。

```php
// src/Application.php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    $middlewareQueue
        // エラーハンドリング
        ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))
        // アセット処理
        ->add(new AssetMiddleware([
            'cacheTime' => Configure::read('Asset.cacheTime'),
        ]))
        // ルーティング
        ->add(new RoutingMiddleware($this))
        // リクエストボディの解析
        ->add(new BodyParserMiddleware())
        // CSRF 保護
        ->add(new CsrfProtectionMiddleware([
            'httponly' => true,
        ]));

    return $middlewareQueue;
}
```

**Laravel との比較**:

-   Laravel のミドルウェアは `app/Http/Kernel.php` で設定され、グローバルミドルウェア、ルートミドルウェア、ミドルウェアグループに分類されます。
-   CakePHP のミドルウェアは `src/Application.php` で設定され、単一のミドルウェアキューとして管理されます。

### コントローラーコンポーネント

CakePHP のコントローラーコンポーネントは、コントローラーの機能を拡張するためのクラスです。

```php
// src/Controller/AppController.php
public function initialize(): void
{
    parent::initialize();

    $this->loadComponent('RequestHandler');
    $this->loadComponent('Flash');
    $this->loadComponent('Auth', [
        'authenticate' => [
            'Form' => [
                'fields' => [
                    'username' => 'email',
                    'password' => 'password'
                ]
            ]
        ],
        'loginAction' => [
            'controller' => 'Users',
            'action' => 'login'
        ]
    ]);
}
```

**Laravel との比較**:

-   Laravel にはコントローラーコンポーネントの概念はなく、代わりにミドルウェアとサービスプロバイダーを使用します。
-   CakePHP のコンポーネントはコントローラーに直接関連付けられますが、Laravel のサービスは依存性注入を通じて利用されます。

### ビューヘルパー

CakePHP のビューヘルパーは、ビューテンプレートで使用できるユーティリティクラスです。

```php
// templates/Articles/view.php
<?= $this->Html->link('記事一覧', ['controller' => 'Articles', 'action' => 'index']) ?>
<?= $this->Form->create($article) ?>
<?= $this->Form->control('title') ?>
<?= $this->Form->control('body', ['type' => 'textarea']) ?>
<?= $this->Form->button('保存') ?>
<?= $this->Form->end() ?>
```

**Laravel との比較**:

-   Laravel は Blade ディレクティブとヘルパー関数を使用します（例：`@include`, `route()`, `csrf_field()`）。
-   CakePHP はオブジェクト指向のヘルパークラスを使用します（例：`$this->Html->link()`, `$this->Form->create()`）。

### ORM

CakePHP の ORM（Object-Relational Mapping）は、データベースとオブジェクト間のマッピングを提供します。

```php
// src/Model/Table/ArticlesTable.php
public function initialize(array $config): void
{
    parent::initialize($config);

    $this->setTable('articles');
    $this->setDisplayField('title');
    $this->setPrimaryKey('id');

    $this->addBehavior('Timestamp');

    $this->belongsTo('Users', [
        'foreignKey' => 'user_id',
    ]);
    $this->hasMany('Comments', [
        'foreignKey' => 'article_id',
    ]);
    $this->belongsToMany('Tags', [
        'foreignKey' => 'article_id',
        'targetForeignKey' => 'tag_id',
        'joinTable' => 'articles_tags',
    ]);
}
```

**Laravel との比較**:

-   Laravel は Eloquent ORM を使用し、モデルクラスにリレーションシップを定義します。
-   CakePHP は Table クラスと Entity クラスを分離し、Table クラスにリレーションシップを定義します。

### バリデーション

CakePHP のバリデーションは、データの検証を行うための仕組みです。

```php
// src/Model/Table/ArticlesTable.php
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
        ->integer('user_id')
        ->notEmptyString('user_id');

    return $validator;
}
```

**Laravel との比較**:

-   Laravel はフォームリクエストクラスまたはコントローラーでバリデーションを定義します。
-   CakePHP は Table クラスでバリデーションを定義します。

### 認証と認可

CakePHP 4.x では、認証と認可は別々のプラグインとして提供されています。

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

**Laravel との比較**:

-   Laravel は Auth ファサードと config/auth.php を使用します。
-   CakePHP は Authentication プラグインと Authorization プラグインを使用します。

### キャッシュ

CakePHP のキャッシュは、データをキャッシュするための仕組みです。

```php
// config/app.php
'Cache' => [
    'default' => [
        'className' => 'File',
        'path' => CACHE,
        'url' => env('CACHE_DEFAULT_URL', null),
    ],
    '_cake_core_' => [
        'className' => 'File',
        'prefix' => 'myapp_cake_core_',
        'path' => CACHE . 'persistent/',
        'serialize' => true,
        'duration' => '+1 years',
        'url' => env('CACHE_CAKECORE_URL', null),
    ],
    '_cake_model_' => [
        'className' => 'File',
        'prefix' => 'myapp_cake_model_',
        'path' => CACHE . 'models/',
        'serialize' => true,
        'duration' => '+1 years',
        'url' => env('CACHE_CAKEMODEL_URL', null),
    ],
],
```

```php
// キャッシュの使用例
use Cake\Cache\Cache;

// データの書き込み
Cache::write('key', 'value', 'default');

// データの読み取り
$value = Cache::read('key', 'default');

// データの削除
Cache::delete('key', 'default');

// キャッシュのクリア
Cache::clear(false, 'default');
```

**Laravel との比較**:

-   Laravel は Cache ファサードと config/cache.php を使用します。
-   CakePHP は Cache クラスと config/app.php の Cache 設定を使用します。

### ロギング

CakePHP のロギングは、ログメッセージを記録するための仕組みです。

```php
// config/app.php
'Log' => [
    'debug' => [
        'className' => 'Cake\Log\Engine\FileLog',
        'path' => LOGS,
        'file' => 'debug',
        'url' => env('LOG_DEBUG_URL', null),
        'scopes' => false,
        'levels' => ['notice', 'info', 'debug'],
    ],
    'error' => [
        'className' => 'Cake\Log\Engine\FileLog',
        'path' => LOGS,
        'file' => 'error',
        'url' => env('LOG_ERROR_URL', null),
        'scopes' => false,
        'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
    ],
],
```

```php
// ロギングの使用例
use Cake\Log\Log;

// ログの書き込み
Log::debug('デバッグメッセージ');
Log::error('エラーメッセージ');
Log::info('情報メッセージ');
```

**Laravel との比較**:

-   Laravel は Log ファサードと config/logging.php を使用します。
-   CakePHP は Log クラスと config/app.php の Log 設定を使用します。

### セッション

CakePHP のセッションは、ユーザーセッションを管理するための仕組みです。

```php
// コントローラーでのセッションの使用例
$this->request->getSession()->write('key', 'value');
$value = $this->request->getSession()->read('key');
$this->request->getSession()->delete('key');
```

**Laravel との比較**:

-   Laravel は Session ファサードと config/session.php を使用します。
-   CakePHP は `$this->request->getSession()` メソッドを使用します。

### イベントシステム

CakePHP のイベントシステムは、アプリケーション内でイベントを発行し、リスナーを登録するための仕組みです。

```php
// イベントの発行
use Cake\Event\Event;
use Cake\Event\EventManager;

$event = new Event('Model.User.afterSave', $this, [
    'user' => $user,
    'options' => $options
]);
EventManager::instance()->dispatch($event);
```

```php
// イベントリスナーの登録
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;

class UserListener implements EventListenerInterface
{
    public function implementedEvents(): array
    {
        return [
            'Model.User.afterSave' => 'afterSave',
        ];
    }

    public function afterSave(EventInterface $event, $user, $options)
    {
        // イベント処理
    }
}

// リスナーの登録
EventManager::instance()->on(new UserListener());
```

**Laravel との比較**:

-   Laravel は Event ファサードと Listener クラスを使用します。
-   CakePHP は EventManager クラスと EventListenerInterface を使用します。

## トラブルシューティング

### 一般的なエラー

#### 1. Class not found エラー

**症状**: `Class 'App\Controller\XXXController' not found` のようなエラーが表示される。

**原因**: クラスが見つからない、または名前空間が正しくない。

**解決策**:

1. クラス名とファイル名が一致しているか確認する。
2. 名前空間が正しいか確認する。
3. オートローダーが正しく設定されているか確認する。

```php
// 正しい名前空間と使用例
namespace App\Controller;

use App\Controller\AppController;

class ArticlesController extends AppController
{
    // ...
}
```

**Laravel との比較**:

-   Laravel も同様のエラーが発生する可能性があります。
-   Laravel では `App\Http\Controllers` 名前空間を使用しますが、CakePHP では `App\Controller` 名前空間を使用します。

#### 2. Database connection failed エラー

**症状**: `Database connection failed` のようなエラーが表示される。

**原因**: データベース接続設定が正しくない。

**解決策**:

1. `config/app_local.php` のデータベース接続設定を確認する。
2. データベースサーバーが起動しているか確認する。
3. データベースユーザーとパスワードが正しいか確認する。

```php
// config/app_local.php
'Datasources' => [
    'default' => [
        'host' => 'localhost',
        'username' => 'my_user',
        'password' => 'my_password',
        'database' => 'my_database',
        // ...
    ],
],
```

**Laravel との比較**:

-   Laravel では `.env` ファイルでデータベース接続設定を行いますが、CakePHP では `config/app_local.php` ファイルで設定します。

#### 3. Missing Template エラー

**症状**: `Missing Template` のようなエラーが表示される。

**原因**: テンプレートファイルが見つからない。

**解決策**:

1. テンプレートファイルが正しいディレクトリに存在するか確認する。
2. テンプレートファイル名が正しいか確認する。
3. コントローラーのアクション名とテンプレートファイル名が一致しているか確認する。

```php
// 正しいテンプレートファイルのパス
// src/Controller/ArticlesController.php の index アクションの場合
// templates/Articles/index.php
```

**Laravel との比較**:

-   Laravel では `resources/views` ディレクトリにテンプレートファイルを配置しますが、CakePHP では `templates` ディレクトリに配置します。
-   Laravel では `.blade.php` 拡張子を使用しますが、CakePHP では `.php` 拡張子を使用します。

### データベース接続の問題

#### 1. データベース接続設定の確認

**症状**: データベースに接続できない。

**解決策**:

1. `config/app_local.php` のデータベース接続設定を確認する。
2. Docker を使用している場合、Docker コンテナ内からデータベースに接続できるか確認する。

```bash
# Docker コンテナ内からデータベースに接続
docker-compose exec web mysql -h db -u my_user -p my_database
```

**Laravel との比較**:

-   Laravel では `.env` ファイルでデータベース接続設定を行いますが、CakePHP では `config/app_local.php` ファイルで設定します。

#### 2. マイグレーションの問題

**症状**: マイグレーションが失敗する。

**解決策**:

1. マイグレーションファイルが正しいか確認する。
2. データベースユーザーに必要な権限があるか確認する。
3. マイグレーションを実行する前に、データベースが存在するか確認する。

```bash
# マイグレーションの実行
bin/cake migrations migrate

# マイグレーションのロールバック
bin/cake migrations rollback
```

**Laravel との比較**:

-   Laravel では `php artisan migrate` コマンドを使用しますが、CakePHP では `bin/cake migrations migrate` コマンドを使用します。

### ルーティングの問題

#### 1. ルートが見つからない

**症状**: `Missing Route` のようなエラーが表示される。

**解決策**:

1. `config/routes.php` ファイルでルートが正しく定義されているか確認する。
2. コントローラーとアクションが存在するか確認する。
3. ルートのパラメータが正しいか確認する。

```php
// ルートの一覧表示
bin/cake routes
```

**Laravel との比較**:

-   Laravel では `php artisan route:list` コマンドを使用しますが、CakePHP では `bin/cake routes` コマンドを使用します。

#### 2. URL 生成の問題

**症状**: 生成された URL が正しくない。

**解決策**:

1. ルート名が正しいか確認する。
2. パラメータが正しいか確認する。
3. URL 生成メソッドが正しいか確認する。

```php
// 正しい URL 生成
$url = $this->Url->build([
    'controller' => 'Articles',
    'action' => 'view',
    $article->id
]);
```

**Laravel との比較**:

-   Laravel では `route()` ヘルパー関数を使用しますが、CakePHP では `$this->Url->build()` メソッドを使用します。

### ビューの問題

#### 1. レイアウトの問題

**症状**: レイアウトが正しく適用されない。

**解決策**:

1. レイアウトファイルが存在するか確認する。
2. コントローラーでレイアウトが正しく設定されているか確認する。
3. ビューでレイアウトが無効化されていないか確認する。

```php
// コントローラーでのレイアウト設定
$this->viewBuilder()->setLayout('custom');

// ビューでのレイアウト無効化
$this->disableAutoLayout();
```

**Laravel との比較**:

-   Laravel では `@extends('layouts.app')` ディレクティブを使用しますが、CakePHP では `$this->viewBuilder()->setLayout('custom')` メソッドを使用します。

#### 2. ヘルパーの問題

**症状**: ヘルパーメソッドが見つからない。

**解決策**:

1. ヘルパーがロードされているか確認する。
2. ヘルパー名が正しいか確認する。
3. ヘルパーメソッドが存在するか確認する。

```php
// AppView.php でのヘルパーのロード
public function initialize(): void
{
    $this->loadHelper('Html');
    $this->loadHelper('Form');
    $this->loadHelper('Flash');
}
```

**Laravel との比較**:

-   Laravel ではヘルパー関数を直接使用しますが、CakePHP ではヘルパークラスをロードして使用します。

### フォーム送信の問題

#### 1. CSRF エラー

**症状**: CSRF トークンが無効であるというエラーが表示される。

**解決策**:

1. フォームに CSRF トークンが含まれているか確認する。
2. セッションが正しく設定されているか確認する。
3. CSRF 保護が有効になっているか確認する。

```php
// フォームでの CSRF トークンの追加
<?= $this->Form->create($article) ?>
// CSRF トークンは自動的に追加される
<?= $this->Form->end() ?>
```

**Laravel との比較**:

-   Laravel では `@csrf` ディレクティブを使用しますが、CakePHP では `$this->Form->create()` メソッドが自動的に CSRF トークンを追加します。

#### 2. バリデーションエラー

**症状**: バリデーションエラーが表示される。

**解決策**:

1. バリデーションルールが正しいか確認する。
2. フォームデータが正しいか確認する。
3. エラーメッセージが正しく表示されているか確認する。

```php
// バリデーションエラーの表示
<?= $this->Form->control('title', ['error' => true]) ?>
```

**Laravel との比較**:

-   Laravel では `$errors->has('field')` と `$errors->first('field')` を使用しますが、CakePHP では `$this->Form->control()` メソッドが自動的にエラーを表示します。

### 認証の問題

#### 1. ログインの問題

**症状**: ログインできない。

**解決策**:

1. 認証設定が正しいか確認する。
2. ユーザーデータが正しいか確認する。
3. パスワードハッシュが正しいか確認する。

```php
// 認証の設定
// src/Application.php
public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
{
    $service = new AuthenticationService();

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

**Laravel との比較**:

-   Laravel では `Auth::attempt()` メソッドを使用しますが、CakePHP では Authentication プラグインを使用します。

#### 2. 認可の問題

**症状**: アクセス権限がないというエラーが表示される。

**解決策**:

1. 認可設定が正しいか確認する。
2. ユーザーに必要な権限があるか確認する。
3. ポリシーが正しく設定されているか確認する。

```php
// 認可の設定
// src/Application.php
public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
{
    $service = new AuthorizationService();

    $resolver = new OrmResolver();
    $service->setPolicy(Article::class, ArticlePolicy::class);

    return $service;
}
```

**Laravel との比較**:

-   Laravel では `Gate::allows()` メソッドを使用しますが、CakePHP では Authorization プラグインを使用します。

### パフォーマンスの問題

#### 1. クエリの最適化

**症状**: データベースクエリが遅い。

**解決策**:

1. インデックスを追加する。
2. クエリを最適化する。
3. キャッシュを使用する。

```php
// クエリの最適化
$query = $this->Articles->find()
    ->select(['id', 'title', 'created'])
    ->where(['published' => true])
    ->order(['created' => 'DESC'])
    ->limit(10)
    ->cache('recent_articles');
```

**Laravel との比較**:

-   Laravel も同様のクエリ最適化テクニックを使用できます。
-   CakePHP では `->cache()` メソッドを使用してクエリ結果をキャッシュできます。

#### 2. キャッシュの使用

**症状**: アプリケーションが遅い。

**解決策**:

1. ビューキャッシュを使用する。
2. クエリキャッシュを使用する。
3. 結果キャッシュを使用する。

```php
// ビューキャッシュの使用
// templates/Articles/index.php
<?php $this->Blocks->set('title', 'Articles'); ?>
<?php $this->Blocks->set('cache', ['key' => 'articles_index', 'duration' => '+1 hour']); ?>

// クエリキャッシュの使用
$query = $this->Articles->find()->cache('all_articles');
```

**Laravel との比較**:

-   Laravel では `Cache::remember()` メソッドを使用しますが、CakePHP では `->cache()` メソッドを使用します。

## Laravel との比較

### 概念の対応関係

| CakePHP               | Laravel                                | 説明                     |
| --------------------- | -------------------------------------- | ------------------------ |
| `src/Application.php` | `app/Providers/AppServiceProvider.php` | アプリケーションの初期化 |
| `src/Controller/`     | `app/Http/Controllers/`                | コントローラー           |
| `src/Model/Table/`    | `app/Models/`                          | モデル                   |
| `src/Model/Entity/`   | `app/Models/`                          | エンティティ             |
| `templates/`          | `resources/views/`                     | ビュー                   |
| `config/routes.php`   | `routes/web.php`                       | ルート定義               |
| `config/app.php`      | `config/app.php`                       | アプリケーション設定     |
| `webroot/`            | `public/`                              | 公開ディレクトリ         |
| `bin/cake`            | `artisan`                              | コマンドラインツール     |

### 機能の違い

#### 1. テンプレートエンジン

**CakePHP**: PHP ネイティブのテンプレートを使用します。

```php
<!-- templates/Articles/index.php -->
<h1><?= h($article->title) ?></h1>
<p><?= h($article->body) ?></p>
```

**Laravel**: Blade テンプレートエンジンを使用します。

```php
<!-- resources/views/articles/index.blade.php -->
<h1>{{ $article->title }}</h1>
<p>{{ $article->body }}</p>
```

#### 2. ORM

**CakePHP**: Table クラスと Entity クラスを分離します。

```php
// src/Model/Table/ArticlesTable.php
class ArticlesTable extends Table
{
    public function initialize(array $config): void
    {
        $this->belongsTo('Users');
        $this->hasMany('Comments');
    }
}

// src/Model/Entity/Article.php
class Article extends Entity
{
    protected $_accessible = [
        'title' => true,
        'body' => true,
    ];
}
```

**Laravel**: 単一のモデルクラスを使用します。

```php
// app/Models/Article.php
class Article extends Model
{
    protected $fillable = ['title', 'body'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

#### 3. ルーティング

**CakePHP**: `config/routes.php` ファイルでルートを定義します。

```php
// config/routes.php
$routes->scope('/', function (RouteBuilder $builder): void {
    $builder->connect('/articles/{id}', ['controller' => 'Articles', 'action' => 'view'])
        ->setPatterns(['id' => '\d+'])
        ->setPass(['id']);

    $builder->resources('Articles');

    $builder->fallbacks();
});
```

**Laravel**: `routes/web.php` ファイルでルートを定義します。

```php
// routes/web.php
Route::get('/articles/{id}', [ArticleController::class, 'show']);
Route::resource('articles', ArticleController::class);
```

#### 4. ミドルウェア

**CakePHP**: `src/Application.php` ファイルでミドルウェアを設定します。

```php
// src/Application.php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    $middlewareQueue
        ->add(new ErrorHandlerMiddleware())
        ->add(new RoutingMiddleware($this))
        ->add(new CsrfProtectionMiddleware());

    return $middlewareQueue;
}
```

**Laravel**: `app/Http/Kernel.php` ファイルでミドルウェアを設定します。

```php
// app/Http/Kernel.php
protected $middleware = [
    \App\Http\Middleware\TrustProxies::class,
    \App\Http\Middleware\CheckForMaintenanceMode::class,
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    \App\Http\Middleware\TrimStrings::class,
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
];
```

### 移行のヒント

Laravel から CakePHP に移行する場合、以下のポイントに注意してください。

#### 1. ディレクトリ構造

Laravel のディレクトリ構造と CakePHP のディレクトリ構造は異なります。ファイルを適切なディレクトリに配置してください。

#### 2. 名前空間

Laravel の名前空間と CakePHP の名前空間は異なります。名前空間を適切に変更してください。

#### 3. モデル

Laravel のモデルは単一のクラスですが、CakePHP のモデルは Table クラスと Entity クラスに分かれています。モデルを適切に分割してください。

#### 4. ビュー

Laravel の Blade テンプレートは CakePHP の PHP ネイティブテンプレートに変換する必要があります。

#### 5. ルーティング

Laravel のルート定義は CakePHP のルート定義に変換する必要があります。

#### 6. ミドルウェア

Laravel のミドルウェアは CakePHP のミドルウェアに変換する必要があります。

#### 7. 認証と認可

Laravel の認証と認可は CakePHP の Authentication プラグインと Authorization プラグインに変換する必要があります。

#### 8. キャッシュ

Laravel のキャッシュは CakePHP のキャッシュに変換する必要があります。

#### 9. セッション

Laravel のセッションは CakePHP のセッションに変換する必要があります。

#### 10. イベント

Laravel のイベントは CakePHP のイベントに変換する必要があります。

## まとめ

CakePHP は Laravel と同様に、PHP の高速開発フレームワークです。両方のフレームワークは MVC アーキテクチャを採用しており、開発者が迅速にアプリケーションを構築できるように設計されています。

主な違いは、ディレクトリ構造、命名規則、テンプレートエンジン、ORM の実装などにあります。Laravel の経験があれば、CakePHP の概念は比較的簡単に理解できるでしょう。

CakePHP の詳細については、[公式ドキュメント](https://book.cakephp.org/4/ja/) を参照してください。
