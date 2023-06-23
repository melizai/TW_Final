FROM php:8.2-fpm

RUN apt-get update -q && \
    apt-get install -qy \
        libzip-dev \
        git \
        unzip \
        default-mysql-client

# Install Nginx and necessary libraries
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    default-mysql-client

# Install necessary PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy Nginx configuration
COPY ./docker/nginx/nginx.conf /etc/nginx/sites-available/default

# Set the working directory
WORKDIR /var/www/html

# Copy the application code
COPY . .

# Expose port 80 for HTTP traffic
EXPOSE 80

# Start Nginx and PHP-FPM
CMD service nginx start && php-fpm
