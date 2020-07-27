FROM arm32v7/php:7.3-apache

# add url rewriting module
RUN a2enmod rewrite && \
  # add mysqli extension
  docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# change web root to /www directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/www
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf  && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . /var/www/html/
# add right for logs and cache
RUN chown -R www-data:www-data /var/www/html/logs/ /var/www/html/cache/

# run with these 2 commands:
# $ docker build -t zordania .
# $ docker run -d --name zordania -d -p 8088:80 --link mariadb:db zordania
