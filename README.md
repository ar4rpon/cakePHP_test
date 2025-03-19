# CakePHP 4.x + Docker ボイラープレート

このボイラープレートは、CakePHP 4.x アプリケーションを Docker 環境で開発するためのものです。

## Docker 構成

このボイラープレートでは、以下の Docker サービスを使用しています。

-   **web:** CakePHP アプリケーションの Web サーバー。`./docker/web/Dockerfile` を使用してビルドされ、ローカルの現在のディレクトリをコンテナの `/var/www/html` にマウントし、ポート`${WEB_PORT:-80}`を公開します。
-   **db:** MySQL データベース。`mysql:8.0` イメージを使用し、ボリュームを使用してデータを永続化し、ポート`${DB_PORT:-3306}`を公開します。
-   **phpmyadmin:** MySQL データベースを管理するための Web インターフェース。`phpmyadmin/phpmyadmin` イメージを使用し、ポート`${PMA_PORT:-8085}`を公開します。

## 起動方法

以下の手順で、コンテナを起動します。

1.  `.env.example` ファイルをコピーして `.env` ファイルを作成します。
    環境変数の内容を参考に値を設定する
    ```
    cp .env.example .env
    ```
2.  `config/app_local.example.php` ファイルをコピーして `config/app_local.php` ファイルを作成します。
    ```
    cp config/app_local.example.php config/app_local.php
    ```
3.  composer install を実行します
    ```
    composer install
    ```
4.  以下のコマンドを使用して、コンテナをバックグラウンドで起動します。
    ```
    docker-compose up -d
    ```

## 環境変数

以下の環境変数を `.env` ファイルで設定できます。

-   `WEB_PORT`: Web サーバーのポート番号 (デフォルト: 80)。
-   `DB_PORT`: MySQL データベースのポート番号 (デフォルト: 3306)。
-   `PMA_PORT`: phpMyAdmin のポート番号 (デフォルト: 8085)。
-   `DB_NAME`: MySQL データベース名。
-   `DB_USER`: MySQL データベースユーザー。
-   `DB_PASS`: MySQL データベースパスワード。

**環境変数の設定例:**

```
WEB_PORT=8080
DB_PORT=3306
PMA_PORT=8085
DB_NAME=cakephp_db
DB_USER=cakephp_user
DB_PASS=secret
```

## その他

-   `docker-compose.yml` ファイルを使用して、Docker 環境を定義します。
-   `./docker/web/Dockerfile` を使用して、Web サーバーの Docker イメージをビルドします。
-   `./docker/mysql/my.cnf` および `./docker/mysql/init.sql` を使用して、MySQL データベースを初期化します。

## CakePHP アプリケーションの設定

-   `config/app.php` ファイルを編集して、アプリケーションの基本設定を行います。タイムゾーン、ロケール、デバッグモードなどを設定できます。
-   `config/app_local.php` ファイルを編集して、データベース接続設定を行います。データベースの種類、ホスト、ユーザー名、パスワードなどを設定します。

## 開発環境でのデバッグ

-   `config/app.php` ファイルで `debug` を `true` に設定すると、デバッグモードが有効になります。
-   エラーメッセージやログ出力を確認して、問題を特定します。

## トラブルシューティング

-   データベース接続エラー: `config/app_local.php` ファイルの設定を確認します。
-   Web サーバーエラー: Docker コンテナが正常に起動しているか確認します。
-   CakePHP アプリケーションのエラー: ログファイル (`logs/error.log`) を確認します。

## Makefile の利用

-   `make <コマンド>` で Makefile に定義されたコマンドを実行できます。
-   `.env`ファイルから環境変数を読み込みます。

## 主なコマンド

-   `make change_owner`: CakePHP の所有者を変更します（Docker コンテナ内で実行した場合）。
-   `make web`: web コンテナに入ります。
-   `make db`: db コンテナに接続して mysql コマンドラインを表示します。
-   `make db_root`: root ユーザーで db コンテナに接続して mysql コマンドラインを表示します。
-   `make build`: Docker イメージをビルドします。
-   `make up`: Docker コンテナを起動します。
-   `make down`: Docker コンテナを停止します。

## 公式ドキュメント

-   CakePHP 4.x: [https://book.cakephp.org/4/ja/](https://book.cakephp.org/4/ja/)
-   Docker: [https://docs.docker.com/](https://docs.docker.com/)
