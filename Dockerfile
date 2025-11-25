FROM php:8.2-apache

# Install PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Ensure Apache passes PATH_INFO to PHP for proper routing
RUN echo 'AcceptPathInfo On' >> /etc/apache2/apache2.conf

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Explicitly allow .htaccess in /var/www/html/public
RUN echo '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>' >> /etc/apache2/apache2.conf

# Set DocumentRoot to /var/www/html/public and update Directory block
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's#<Directory /var/www/html/>#<Directory /var/www/html/public/>#' /etc/apache2/apache2.conf

# Set DirectoryIndex for public directory
RUN echo 'DirectoryIndex index.php index.html' >> /etc/apache2/apache2.conf


# Install Composer
RUN apt-get update \
    && apt-get install -y curl unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy project files into the image
COPY . /var/www/html/

# Install PHP dependencies at root if composer.json exists (optional)
# This ensures both top-level and app-level dependencies are installed when present.
RUN if [ -f /var/www/html/composer.json ]; then \
            composer install --no-dev --optimize-autoloader --no-interaction --no-progress --working-dir=/var/www/html; \
        else echo "no root composer.json, skipping root composer install"; fi

# Install PHP dependencies in /var/www/html/app (app/composer.json should exist)
RUN if [ -f /var/www/html/app/composer.json ]; then \
            composer install --no-dev --optimize-autoloader --no-interaction --no-progress --working-dir=/var/www/html/app; \
        else echo "no app composer.json, skipping app composer install"; fi

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80