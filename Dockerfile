# استخدم صورة PHP مع Apache
FROM php:8.2-apache

# تثبيت الاعتمادات الأساسية و PHP extensions المطلوبة من Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ضبط مجلد المشروع
WORKDIR /var/www/html

# نسخ ملفات Laravel إلى الحاوية
COPY . .

# تثبيت الحزم
RUN composer install --no-dev --optimize-autoloader

# إعداد صلاحيات Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# تفعيل mod_rewrite لـ Apache
RUN a2enmod rewrite

# ضبط إعدادات Apache
COPY ./vhost.conf /etc/apache2/sites-available/000-default.conf

# فتح المنفذ
EXPOSE 80
