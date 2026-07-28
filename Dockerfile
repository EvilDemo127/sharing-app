FROM php:8.2-apache


# ==================================
# PHP Extensions + Dependencies
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
        xml


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
# Laravel + Reverb WebSocket Proxy
# ==================================
RUN echo '<VirtualHost *:80>

    ServerName sharing-app-6vcs.onrender.com

    DocumentRoot /var/www/html/public


    ProxyRequests Off
    ProxyPreserveHost On


    RewriteEngine On

    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/app/(.*) ws://127.0.0.1:8080/app/$1 [P,L]


    ProxyPass /app http://127.0.0.1:8080/app
    ProxyPassReverse /app http://127.0.0.1:8080/app


    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>


</VirtualHost>' \
> /etc/apache2/sites-available/000-default.conf



# ==================================
# Composer
# ==================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /var/www/html


# ==================================
# Copy Laravel App
# ==================================
COPY . .


ENV COMPOSER_ALLOW_SUPERUSER=1



# ==================================
# Laravel Dependencies
# ==================================
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist



# ==================================
# Vite Build Environment
# ==================================
ENV VITE_REVERB_APP_ID=664807
ENV VITE_REVERB_APP_KEY=gc99wf0dhlzygomofzex
ENV VITE_REVERB_HOST=sharing-app-6vcs.onrender.com
ENV VITE_REVERB_PORT=443
ENV VITE_REVERB_SCHEME=https



# ==================================
# Frontend Build
# ==================================
RUN npm install

RUN npm run build



# ==================================
# Laravel Permission
# ==================================
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache



# ==================================
# Supervisor
# Apache + Reverb
# ==================================
RUN mkdir -p /var/log/supervisor


RUN echo "[supervisord]\n\
nodaemon=true\n\
logfile=/dev/null\n\
\n\
[program:apache]\n\
command=/usr/local/bin/apache2-foreground\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
\n\
[program:reverb]\n\
command=/usr/local/bin/php /var/www/html/artisan reverb:start --host=0.0.0.0 --port=8080\n\
directory=/var/www/html\n\
autostart=true\n\
autorestart=true\n\
startsecs=5\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
" \
> /etc/supervisor/supervisord.conf



# ==================================
# Ports
# ==================================
EXPOSE 80



# ==================================
# Start Container
# ==================================
CMD ["supervisord","-c","/etc/supervisor/supervisord.conf"]