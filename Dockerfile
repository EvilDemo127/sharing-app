FROM php:8.2-apache


# -----------------------------
# PHP Extensions + Dependencies
# -----------------------------
RUN apt-get update && apt-get install -y \
    libpng-dev \
    zlib1g-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    libpq-dev \
    zip \
    unzip \
    curl \
    git \
    supervisor \
    && docker-php-ext-configure gd \
    && docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        ctype \
        fileinfo \
        xml


# -----------------------------
# Node.js 20
# -----------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs


# -----------------------------
# Apache Laravel public folder
# -----------------------------
RUN a2enmod rewrite

RUN sed -i \
    's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf


RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' \
>> /etc/apache2/apache2.conf


# -----------------------------
# Composer
# -----------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /var/www/html


# -----------------------------
# Copy Laravel Project
# -----------------------------
COPY . .


ENV COMPOSER_ALLOW_SUPERUSER=1


# -----------------------------
# Install Laravel packages
# -----------------------------
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist


# -----------------------------
# Vite Build
# -----------------------------
RUN npm install

RUN npm run build



# -----------------------------
# Laravel Permission
# -----------------------------
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache



# -----------------------------
# Supervisor
# Apache + Reverb
# -----------------------------
RUN mkdir -p /var/log/supervisor


RUN echo "[supervisord]\n\
nodaemon=true\n\
\n\
[program:apache]\n\
command=/usr/local/bin/apache2-foreground\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stderr_logfile=/dev/stderr\n\
\n\
[program:reverb]\n\
command=php /var/www/html/artisan reverb:start --host=0.0.0.0 --port=8080\n\
directory=/var/www/html\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stderr_logfile=/dev/stderr\n" \
> /etc/supervisor/conf.d/supervisord.conf



# -----------------------------
# Ports
# -----------------------------
EXPOSE 80 8080



# -----------------------------
# Start
# -----------------------------
CMD ["supervisord","-c","/etc/supervisor/supervisord.conf"]