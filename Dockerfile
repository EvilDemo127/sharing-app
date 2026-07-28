FROM php:8.2-apache


# ==================================
# PHP Extensions + System Packages
# ==================================
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
        xml \
    && rm -rf /var/lib/apt/lists/*



# ==================================
# Node.js 20
# ==================================
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs



# ==================================
# Apache Modules
# ==================================
RUN a2enmod \
    rewrite \
    proxy \
    proxy_http \
    proxy_wstunnel \
    headers



# ==================================
# Apache Virtual Host
# Laravel + Reverb WebSocket
# ==================================
RUN cat <<'EOF' > /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>

    ServerName sharing-app-6vcs.onrender.com

    DocumentRoot /var/www/html/public


    ProxyRequests Off
    ProxyPreserveHost On


    # Laravel Reverb WebSocket
    ProxyPass /app ws://127.0.0.1:8080/app
    ProxyPassReverse /app ws://127.0.0.1:8080/app


    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>


</VirtualHost>
EOF



# ==================================
# Composer
# ==================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer



WORKDIR /var/www/html



# ==================================
# Copy Laravel Project
# ==================================
COPY . .



ENV COMPOSER_ALLOW_SUPERUSER=1



# ==================================
# Install Laravel Packages
# ==================================
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist



# ==================================
# Vite Build Variables
# ==================================
ENV VITE_REVERB_APP_ID=664807
ENV VITE_REVERB_APP_KEY=gc99wf0dhlzygomofzex
ENV VITE_REVERB_HOST=sharing-app-6vcs.onrender.com
ENV VITE_REVERB_PORT=443
ENV VITE_REVERB_SCHEME=https



# ==================================
# Build Frontend
# ==================================
RUN npm install

RUN npm run build



# ==================================
# Laravel Permission
# ==================================
RUN mkdir -p \
    storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && touch storage/logs/laravel.log \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache



# ==================================
# Supervisor Config
# Apache + Reverb
# ==================================
RUN mkdir -p /var/log/supervisor


RUN cat <<'EOF' > /etc/supervisor/supervisord.conf

[supervisord]
nodaemon=true
logfile=/dev/null


[program:apache]
command=/usr/local/bin/apache2-foreground
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0


[program:reverb]
command=/usr/local/bin/php /var/www/html/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/var/www/html
autostart=true
autorestart=true
startsecs=5
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

EOF



# ==================================
# Laravel Cache Clear
# ==================================
RUN php artisan optimize:clear || true



# ==================================
# Render Port
# ==================================
EXPOSE 80



# ==================================
# Start
# ==================================
CMD ["supervisord","-c","/etc/supervisor/supervisord.conf"]