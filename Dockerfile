FROM php:8.4-apache

# Install system dependencies
# libpq-dev       → pdo_pgsql, pgsql
# libonig-dev     → mbstring
# libxml2-dev     → xml
# libzip-dev      → zip (composer)
# libcurl4-openssl-dev → (curl already built-in, but needed for headers)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    default-libmysqlclient-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions that are NOT built-in to php:8.4-apache
# Already included: curl, ctype, json, tokenizer, openssl
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    mbstring \
    opcache \
    bcmath \
    xml

# Disable mpm_event, enable mpm_prefork (required for PHP), then enable rewrite
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true && \
    a2enmod mpm_prefork rewrite && \
    echo "MPM fix applied: $(date)"

# Install Composer via official installer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

ENV COMPOSER_ALLOW_SUPERUSER=1

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (for layer caching)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy the rest of the application
COPY . .

# Run autoloader dump
RUN composer dump-autoload --optimize

# Point Apache DocumentRoot to Laravel's public folder
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

# Add AllowOverride All so .htaccess works
RUN sed -i '/<\/VirtualHost>/i\\t<Directory /var/www/html/public>\n\t\tAllowOverride All\n\t\tRequire all granted\n\t</Directory>' \
    /etc/apache2/sites-available/000-default.conf

# Set correct ownership and permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
