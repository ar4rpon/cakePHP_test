<?php

declare(strict_types=1);

/**
 * CakePHP(tm) : 高速開発フレームワーク (https://cakephp.org)
 * 著作権 (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * このソフトウェアはMITライセンスの下で提供されています。
 * ファイルの再配布時には上記の著作権表示を保持してください。
 *
 * @copyright 著作権 (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) プロジェクト
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MITライセンス
 */

namespace App\View;

use Cake\View\View;

/**
 * アプリケーションビュー
 *
 * アプリケーションのデフォルトビュークラス
 *
 * @link https://book.cakephp.org/4/ja/views.html#the-app-view
 */
class AppView extends View
{
    /**
     * 初期化フックメソッド
     *
     * このメソッドを使用して、ヘルパーの読み込みなどの共通初期化コードを追加します。
     *
     * 例: `$this->loadHelper('Html');`
     *
     * @return void
     */
    public function initialize(): void {}

    /**
     * ユーザーがログインしているかを確認します。
     *
     * @return bool ログインしている場合はtrue、そうでない場合はfalse
     */
    public function isLoggedIn()
    {
        return $this->request->getAttribute('identity') !== null;
    }

    /**
     * 現在のユーザー情報を取得します。
     *
     * @return mixed ユーザー情報、またはnull
     */
    public function getUser()
    {
        return $this->request->getAttribute('identity');
    }
}
