FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install zip

# Install Composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy the application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 80 and start apache
EXPOSE 80
