## CakePHP の UI 描画処理の流れ

CakePHP で UI が描画されるときの処理の流れは、以下のようになります。

1.  **リクエストの受付**: Web サーバーがリクエストを受け付けます。
2.  **ミドルウェアの実行**: `src/Application.php` で定義されたミドルウェアキューが実行されます。
    -   `ErrorHandlerMiddleware`: エラーと例外を処理します。
    -   `AssetMiddleware`: プラグインやテーマのアセットを処理します。
    -   `RoutingMiddleware`: ルーティングを処理し、リクエストを適切なコントローラーのアクションにディスパッチします。
    -   `BodyParserMiddleware`: リクエストボディを解析し、`$request->getData()` で利用できるようにします。
    -   `CsrfProtectionMiddleware`: CSRF 攻撃から保護します。
3.  **ルーティング**: `config/routes.php` に定義されたルーティングルールに基づいて、リクエストを適切なコントローラーのアクションにディスパッチします。
4.  **コントローラーの実行**: ルーティングによって選択されたコントローラーのアクションが実行されます。
    -   コントローラーのアクションは、モデルを使ってデータを取得したり、更新したりすることができます。
    -   コントローラーのアクションは、`$this->set()` メソッドを使ってビューに渡す変数を設定します。
    -   コントローラーのアクションは、`$this->render()` メソッドを使ってビューをレンダリングします。
5.  **ビューのレンダリング**: `$this->render()` メソッドは、テンプレートファイル (`templates/` ディレクトリにある `.php` ファイル) を読み込み、コントローラーから渡された変数を使って、HTML を生成します。
    -   テンプレートファイルは、レイアウトファイル (`templates/layout/default.php`) に埋め込まれて、最終的な HTML を生成します。
    -   レイアウトファイルには、ヘッダー、フッター、ナビゲーションなどの共通要素が含まれています。
    -   エレメント (`templates/element/`) を使って、再利用可能な UI コンポーネントを定義することもできます。

テンプレートの選択:

-   テンプレートの選択は、主にコントローラーの `render()` メソッドで行われます。
-   `render()` メソッドにテンプレート名を指定しない場合、CakePHP は規約に基づいてテンプレートを自動的に選択します。
    例えば、`ArticlesController` の `index()` アクションの場合、`templates/Articles/index.php` が自動的に選択されます。

レイアウトファイルへの埋め込み:

-   テンプレートファイル (`templates/Articles/index.php`) は、レイアウトファイル (`templates/layout/default.php`) に埋め込まれて、最終的な HTML を生成します。
-   この埋め込み処理は、`Cake\View\View::render()` メソッドで行われます。
-   `Cake\View\View::render()` メソッドは、テンプレートファイルを読み込み、レイアウトファイルを読み込み、テンプレートファイルの内容をレイアウトファイルの `<?= $this->fetch('content') ?>` の部分に挿入し、レイアウトファイル内の PHP コードを実行して、HTML を生成します。

レイアウトファイルの選択:

-   レイアウトファイルの選択は、主にコントローラーの `render()` メソッドで行われます。
-   `render()` メソッドにレイアウト名を指定しない場合、CakePHP は規約に基づいてレイアウトファイルを自動的に選択します。
    デフォルトのレイアウトファイルは、`templates/layout/default.php` です。
-   コントローラーの `beforeRender()` メソッドで、`$this->viewBuilder()->setLayout()` メソッドを使って、レイアウトファイルを明示的に指定することもできます。

6.  **レスポンスの送信**: 生成された HTML が Web サーバーに送信され、クライアントに表示されます。

## CakePHP のテスト

CakePHP では、PHPUnit を使ってテストを実行します。

### テストの種類

CakePHP では、以下の種類のテストを実行できます。

-   ユニットテスト: 個々のクラスやメソッドの動作をテストします。
-   結合テスト: 複数のクラスやモジュールが連携して動作することをテストします。
-   機能テスト: アプリケーション全体の動作をテストします。

### テストの実行方法

テストを実行するには、以下の手順に従います。

1.  intl 拡張機能を有効にします。
    -   `php.ini` ファイルを開き、`;extension=intl` の行のコメントを外します。
    -   Web サーバーを再起動します。
2.  プロジェクトのルートディレクトリで、以下のコマンドを実行します。
    ```
    bin/cake test
    ```

### テストコードの例

以下は、`src/Controller/PagesController.php` のテストコードの例です。

```php
namespace App\Test\TestCase\Controller;

use App\Controller\PagesController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class PagesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public function testDisplayingHomePage()
    {
        $this->get('/');
        $this->assertResponseOk();
        $this->assertResponseContains('CakePHP');
    }
}
```

### トラブルシューティング

-   `intl` 拡張機能が有効になっていない場合、`PHP Fatal error:  You must enable the intl extension to use CakePHP.` というエラーが表示されます。
    -   `php.ini` ファイルで `intl` 拡張機能が有効になっていることを確認してください。
