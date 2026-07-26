FROM php:8.1-apache

COPY . /var/www/html/

# Make sure the data files exist, then hand ownership to the Apache user (www-data)
# so PHP can write to them at runtime — this is what was causing the
# "Permission denied" error on slots.json / orders.json.
RUN touch /var/www/html/slots.json /var/www/html/orders.json \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod 664 /var/www/html/slots.json /var/www/html/orders.json

EXPOSE 80
