FROM php:8.2-apache

# コンテナ内でファイル生成をした場合に起きる権限エラーの対応
ARG UID=1000
ARG GID=1000

RUN groupadd -g $GID appgroup && \
    useradd -m -u $UID -g appgroup appuser

# 必要なパッケージのインストール
RUN apt-get update \
    && apt-get install -y vim unzip libicu-dev \
    && docker-php-ext-install intl pdo_mysql \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composerのインストール
COPY --from=composer /usr/bin/composer /usr/bin/composer

# アプリケーションディレクトリの作成と権限設定
RUN mkdir -p /var/www/html/logs \
    /var/www/html/tmp \
    /var/www/html/tmp/cache/models \
    /var/www/html/tmp/cache/persistent \
    /var/www/html/tmp/cache/views \
    /var/www/html/tmp/sessions \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/tmp \
    && chmod -R 775 /var/www/html/tmp/cache \
    && chmod -R 775 /var/www/html/logs

WORKDIR /var/www/html