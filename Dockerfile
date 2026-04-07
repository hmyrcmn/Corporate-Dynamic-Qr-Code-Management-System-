FROM php:8.2-fpm-alpine

# Sistem bağımlılıkları ve PHP eklentileri
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    curl

RUN docker-php-ext-install pdo_mysql gd bcmath

# Nginx ayarı
COPY .docker/nginx.conf /etc/nginx/http.d/default.conf

# Çalışma dizini
WORKDIR /var/www

# Proje dosyalarını kopyala
COPY . .

# Klasör izinleri
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

CMD ["sh", "-c", "nginx && php-fpm"]