FROM php:8.4-fpm

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

# Очищаем кэш apt, чтобы образ весил меньше
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Конфигурируем библиотеку GD для работы с картинками (как у ребят)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Устанавливаем и включаем расширение Redis для очередей и кэша
RUN pecl install redis && docker-php-ext-enable redis

# Устанавливаем все необходимые PHP-расширения для Laravel и MySQL
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip xml

# Копируем свежую версию Composer из официального образа
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www

# Копируем файлы проекта внутрь контейнера
COPY . .

# Запускаем composer install, чтобы внутри докера сами скачались все вендоры
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Открываем порт 8000 наружу
EXPOSE 8000
