<?php

/**
 * ルーティング設定。
 *
 * このファイルでは、コントローラーとそのアクションへのルートを設定します。
 * ルートは、選択したコントローラーとそのアクション（関数）に異なるURLを自由に接続できる非常に重要なメカニズムです。
 *
 * これは、`Application::routes()`メソッドのコンテキスト内でロードされ、
 * メソッド引数として`RouteBuilder`インスタンス`$routes`を受け取ります。
 *
 * CakePHP(tm) : 高速開発フレームワーク (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * MITライセンスに基づいてライセンスされています。
 * 著作権およびライセンス情報の詳細については、LICENSE.txtをご覧ください。
 * ファイルの再配布では、上記の著作権表示を保持する必要があります。
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) プロジェクト
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * このファイルは、`Application`クラスのコンテキストでロードされます。
 * 必要に応じて、`$this`を使用してアプリケーションクラスのインスタンスを参照できます。
 */

return function (RouteBuilder $routes): void {
    /*
     * すべてのルートで使用するデフォルトのクラス
     *
     * 次のルートクラスはCakePHPに付属しており、デフォルトとして設定するのに適しています。
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * `Router::defaultRouteClass()`が呼び出されない場合、使用されるクラスは
     * `Route`（`Cake\Routing\Route\Route`）です。
     *
     * `Route`はURLを屈折させないため、`{plugin}`、`{controller}`、
     * `{action}`マーカーを使用すると、URLのケースが一貫しなくなることに注意してください。
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        /*
         * ここでは、'/'（ベースパス）を'Pages'というコントローラーに接続し、
         * そのアクションを'display'と呼び、ビューファイルを選択するためのパラメーターを渡します
         * （この場合、templates/Pages/home.php）...
         */
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

        /*
         * ...そして、残りの'Pages'コントローラーのURLを接続します。
         */
        $builder->connect('/pages/*', 'Pages::display');

        /*
         * すべてのコントローラーのキャッチオールルートを接続します。
         *
         * `fallbacks`メソッドは、次のショートカットです。
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         *
         * アプリケーションで必要なルートを接続したら、これらのルートを削除できます。
         */
        $builder->fallbacks();
    });

    /*
     * 異なるミドルウェアのセットが必要な場合、またはまったくない場合は、
     * 新しいスコープを開き、そこにルートを定義します。
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // ここでは、$builder->applyMiddleware()はありません。
     *
     *     // URLから指定された拡張子を解析します
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // ここにAPIアクションを接続します。
     * });
     * ```
     */
};
