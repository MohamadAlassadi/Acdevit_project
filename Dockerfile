# استخدم صورة PHP مع Apache
FROM php:8.1-apache

# تثبيت الأدوات والامتدادات اللازمة
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git \
    && docker-php-ext-install pdo_mysql zip

# تفعيل mod_rewrite الخاص بـ Apache
RUN a2enmod rewrite

# نسخ ملفات المشروع إلى مجلد Apache
COPY . /var/www/html

# تعيين مجلد العمل
WORKDIR /var/www/html

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تشغيل تثبيت الحزم بدون ملفات التطوير وتحسين الأداء
RUN composer install --no-dev --optimize-autoloader

# ضبط أذونات مجلدات التخزين و bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# تعريض المنفذ 80
EXPOSE 80

# أمر تشغيل Apache في الواجهة الأمامية
CMD ["apache2-foreground"]
