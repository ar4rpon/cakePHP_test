FROM php:8.2-apache

# コンテナ内でファイル生成をした場合に起きる権限エラーの対応
ARG UID=1000
ARG GID=1000

RUN groupadd -g $GID appgroup && \
    useradd -m -u $UID -g appgroup appuser

# Apacheが使用するwww-dataユーザーに権限を付与
RUN chown -R www-data:www-data /var/www/html

# 必要なパッケージのインストール
RUN apt-get update \
  && apt-get install -y vim \
  # unzipコマンド(composer create-projectで必要)
  && apt-get install -y unzip \
  # PHPのintl拡張(CakePHPで必要)
  && apt-get install -y libicu-dev \
  && docker-php-ext-install intl \
  # PDO MySQL拡張
  && docker-php-ext-install pdo_mysql \
  # mod_rewrite有効化
  && a2enmod rewrite

# ログディレクトリの作成
RUN mkdir -p /var/www/html/logs
#ログファイルの書き込み権限を付与 
RUN chown -R www-data:www-data /var/www/html/logs

# tmpディレクトリの作成と権限設定
RUN mkdir -p /var/www/html/tmp
RUN chown -R www-data:www-data /var/www/html/tmp
RUN chmod -R 777 /var/www/html/tmp

# キャッシュディレクトリの権限設定
RUN mkdir -p /var/www/html/tmp/cache
RUN mkdir -p /var/www/html/tmp/cache/models
RUN mkdir -p /var/www/html/tmp/cache/persistent
RUN mkdir -p /var/www/html/tmp/cache/views
RUN mkdir -p /var/www/html/tmp/sessions
RUN chown -R www-data:www-data /var/www/html/tmp/cache
RUN chmod -R 777 /var/www/html/tmp/cache

COPY --from=composer /usr/bin/composer /usr/bin/composer

# コンテナ内のアプリケーションフォルダの権限をwww-dataに変更
WORKDIR /var/www/html
