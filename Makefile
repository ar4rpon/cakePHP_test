# Makefile

# .envファイルから環境変数を読み込む
include .env

# webコンテナに入るコマンド
web:
		docker exec -it -u 1000 cakephp-web-1 bash

# dbコンテナに接続してmysqlコマンドラインを表示
db:
		docker exec -it cakephp-db-1 mysql -u ${DB_USER} -p${DB_PASS}
db_root:
		docker exec -it cakephp-db-1 mysql -uroot -p${DB_PASS}

#Dockerのよく使うコマンドを書いておく
build:
		docker compose build --no-cache
up:
		docker compose up -d
down:
		docker compose down
