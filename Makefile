# Makefile

# .envファイルから環境変数を読み込む
include .env

# 現在のユーザー名を取得する
CURRENT_USER := $(shell whoami)

# cakephpの所有者を変更する（dockerコンテナ内で実行した場合）
change_owner:
    sudo chown -R $(CURRENT_USER):$(CURRENT_USER) html

# webコンテナに入るコマンド
web:
		docker exec -it cakephp-web-1 bash

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
