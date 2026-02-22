FROM php:8.2-apache

# Install system dependencies required for pdo_pgsql
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Enable Apache rewrite module (for clean URLs)
RUN a2enmod rewrite

# Set ServerName to suppress Apache warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Allow .htaccess overrides in web root
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy project files to Apache web root
COPY . /var/www/html/

# Create uploads directory and set permissions
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

# Railway dynamically assigns PORT — Apache must listen on it
# Default to 80 for local Docker usage
ARG PORT=80
ENV PORT=${PORT}
RUN sed -i "s/Listen 80/Listen \${PORT}/" /etc/apache2/ports.conf \
    && sed -i "s/<VirtualHost \*:80>/<VirtualHost *:\${PORT}>/" /etc/apache2/sites-available/000-default.conf

EXPOSE ${PORT}

CMD ["apache2-foreground"]
