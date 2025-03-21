<?php

declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App;

use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;

/**
 * アプリケーション設定クラス。
 *
 * このクラスでは、アプリケーションのブートストラップロジックと
 * 使用するミドルウェアレイヤーを定義します。
 */
class Application extends BaseApplication
{
    /**
     * アプリケーションの設定とブートストラップロジックをすべて読み込みます。
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // 親クラスを呼び出して、ファイルからブートストラップを読み込みます。
        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        } else {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false)
            );
        }

        /*
         * 開発モードでのみDebugKitを読み込む
         * Debug Kitは本番環境にはインストールしないでください
         */
        if (Configure::read('debug')) {
            $this->addPlugin('DebugKit');
        }

        // ここでさらにプラグインを読み込む
    }

    /**
     * アプリケーションで使用するミドルウェアキューを設定します。
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue 設定するミドルウェアキュー。
     * @return \Cake\Http\MiddlewareQueue 更新されたミドルウェアキュー。
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // 下層で発生した例外をキャッチし、
            // エラーページ/レスポンスを作成します。
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // CakePHPが通常行うように、プラグイン/テーマのアセットを処理します。
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // ルーティングミドルウェアを追加します。
            // 多数のルートが接続されている場合、本番環境でルートキャッシュを有効にすると
            // パフォーマンスが向上する可能性があります。
            // 詳細は https://github.com/CakeDC/cakephp-cached-routing を参照してください。
            ->add(new RoutingMiddleware($this))

            // 様々な形式でエンコードされたリクエストボディを解析し、
            // $request->getData() を通じて配列として利用可能にします。
            // 詳細は https://book.cakephp.org/4/ja/controllers/middleware.html#body-parser-middleware を参照してください。
            ->add(new BodyParserMiddleware())

            // クロスサイトリクエストフォージェリ (CSRF) 保護ミドルウェア
            // 詳細は https://book.cakephp.org/4/ja/security/csrf.html#cross-site-request-forgery-csrf-middleware を参照してください。
            ->add(new CsrfProtectionMiddleware([
                'httponly' => true,
            ]));

        return $middlewareQueue;
    }

    /**
     * アプリケーションコンテナサービスを登録します。
     *
     * @param \Cake\Core\ContainerInterface $container 更新するコンテナ。
     * @return void
     * @link https://book.cakephp.org/4/ja/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void {}

    /**
     * CLIアプリケーションのブートストラップ処理。
     *
     * コマンドを実行する際に使用されます。
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        $this->addOptionalPlugin('Bake');

        $this->addPlugin('Migrations');

        // ここでさらにプラグインを読み込む
    }
}
