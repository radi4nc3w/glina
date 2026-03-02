FROM php:8.2-apache

WORKDIR /var/www/html

# Required extension for MySQL (PDO)
RUN docker-php-ext-install pdo pdo_mysql

# Copy project files
COPY . /var/www/html

# Ensure upload directory exists and is writable by web server
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/uploads

# Timeweb provides runtime PORT; reconfigure Apache to listen on it
CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT:-8080}/\" /etc/apache2/ports.conf && sed -i \"s/:80/:${PORT:-8080}/\" /etc/apache2/sites-enabled/000-default.conf && apache2-foreground"]
