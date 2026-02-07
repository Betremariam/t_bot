# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Install PostgreSQL client library, curl, and necessary extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libcurl4-openssl-dev \
    curl \
    && docker-php-ext-install pdo pdo_pgsql curl

# Enable Apache mod_rewrite (useful for SEO-friendly URLs if needed later)
RUN a2enmod rewrite

# Copy the source code to the web root
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (standard for Apache)
EXPOSE 80
