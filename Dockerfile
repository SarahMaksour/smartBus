FROM php:8.2-cli

# تثبيت الملحقات المطلوبة لـ Laravel
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    curl

# تثبيت ملحقات PHP
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    tokenizer \
    xml \
    ctype \
    bcmath \
    zip \
    curl

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تعيين مجلد العمل
WORKDIR /app

# نسخ ملفات المشروع
COPY . .

# تثبيت حزم Composer
RUN composer install --no-dev --optimize-autoloader

# إعداد Laravel
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# فتح المنفذ
EXPOSE ${PORT:-8080}

# تشغيل السيرفر
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}