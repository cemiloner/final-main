# PHP 8.1 ve Apache içeren resmi imajı kullan
FROM php:8.1-apache

# Gerekli PHP eklentilerini kur (pdo_sqlite dahil)
# libsqlite3-dev SQLite geliştirme dosyaları için gereklidir
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_sqlite zip

# Composer'ı kur
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache yapılandırmasını public klasörünü gösterecek şekilde ayarla
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Apache mod_rewrite'ı etkinleştir (htaccess için)
RUN a2enmod rewrite

# Çalışma dizinini ayarla
WORKDIR /var/www/html

# Önce composer dosyalarını kopyala ve bağımlılıkları kur (cache'den yararlanmak için)
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-progress

# Uygulama kodunu kopyala
COPY . .

# Gerekli klasörlerin sahibi Apache kullanıcısı (www-data) olsun
RUN mkdir -p database storage/logs public/uploads/products \
    && chown -R www-data:www-data database storage public/uploads

# İsteğe bağlı: Port 80'i dışarı aç (docker-compose içinde de yapılabilir)
# EXPOSE 80

# Apache sunucusunu ön planda başlat
CMD ["apache2-foreground"] 