# 使用官方 PHP 映像作為基礎
FROM php:8.3-fpm-alpine

# 設定工作目錄
WORKDIR /var/www/html

# 安裝系統依賴
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    mysql-client \
    rabbitmq-c-dev \
    librdkafka-dev \
    build-base \
    supervisor # 安裝 Supervisor 用於多進程管理 (例如 Laravel Horizon)

# 安裝 PHP 擴展
RUN docker-php-ext-install pdo pdo_mysql bcmath sockets
# 安裝 PECL 擴展以支援 AMQP (RabbitMQ) 和 RdKafka (Kafka)
RUN pecl install amqp rdkafka \
    && docker-php-ext-enable amqp rdkafka

# 安裝 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 僅複製 composer.json/lock 以優化 Docker Layer Cache
COPY composer.json composer.lock ./

# 安裝 Composer 依賴 (使用 --no-dev 減少映像大小，會在運行時通過 VOLUME 掛載)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 複製應用程式其餘代碼
COPY . .

# 設定儲存和快取目錄權限
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 暴露端口 (PHP-FPM 預設)
EXPOSE 9000

# 啟動 PHP-FPM
CMD ["php-fpm"]
