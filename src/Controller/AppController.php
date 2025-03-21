<?php

declare(strict_types=1);

/**
 * CakePHP(tm) : 高速開発フレームワーク (https://cakephp.org)
 * 著作権 (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * このソフトウェアはMITライセンスの下でライセンスされています。
 * 著作権およびライセンス情報の全文についてはLICENSE.txtをご覧ください。
 * ファイルの再配布には上記の著作権表示を保持する必要があります。
 *
 * @copyright 著作権 (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) プロジェクト
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MITライセンス
 */

namespace App\Controller;

use Cake\Controller\Controller;

/**
 * アプリケーションコントローラー
 *
 * 以下のクラスにアプリケーション全体で使用するメソッドを追加してください。
 * 他のコントローラーはこのクラスを継承します。
 *
 * @link https://book.cakephp.org/4/ja/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * 初期化フックメソッド
     *
     * このメソッドを使用して、コンポーネントの読み込みなどの共通初期化コードを追加します。
     *
     * 例: `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        // CSRFトークンの保護を有効にする。なぜか初期状態で有効になっていない。
        $this->loadComponent('FormProtection');
    }
}
