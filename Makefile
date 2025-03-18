# Makefile

# 現在のユーザー名を取得する
CURRENT_USER := $(shell whoami)

# cakephpの所有者を変更する（dockerコンテナ内で実行した場合）
change_owner:
    sudo chown -R $(CURRENT_USER):$(CURRENT_USER) html

# webコンテナに入るコマンド
web_container:
		docker exec -it cakephp-web-1 bash

# dbコンテナに入るコマンド
db_container:
		docker exec -it cakephp-db-1 bash

#Dockerのよく使うコマンドを書いておく

