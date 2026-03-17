# Build v4 - 2026-03-17
FROM php:8.3-fpm-alpine

# Install nginx and required extensions
RUN apk add --no-cache nginx sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Disable display_errors in production
RUN echo "display_errors=Off" > /usr/local/etc/php/conf.d/production.ini \
    && echo "log_errors=On" >> /usr/local/etc/php/conf.d/production.ini

# Copy application files
COPY . /var/www/html
WORKDIR /var/www/html

# Create data directory with proper permissions
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html/data \
    && chmod -R 775 /var/www/html/data

# Expose port (Render uses $PORT env variable)
EXPOSE 10000

# Start script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
