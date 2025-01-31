FROM php:7.3-apache

# Installer l'extension GD avec le support JPEG et PNG
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd mysqli pdo_mysql

COPY --chown=www-data:www-data . /var/www/html/
# add right for logs and cache
RUN chown -R www-data:www-data /var/www/html/logs/ /var/www/html/cache/
# Configurer Apache pour utiliser le répertoire www comme document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/www
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
 sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
 echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Activer le module rewrite
RUN a2enmod mpm_prefork && a2enmod rewrite && \
  mv conf/secret_parameters.php.docker conf/secret_parameters.php

# === Configuration BDD ===
ENV MYSQL_BASE=zordania
ENV MYSQL_HOST=localhost
ENV MYSQL_USER=zordania
ENV MYSQL_PASS=zordania
ENV MYSQL_PREBDD=zrd_
ENV MYSQL_PREBDD_FRM=frm_
# === Configuration IRC === 
ENV IRC_SERVER=irc.quakenet.org
ENV IRC_PORT=6667
ENV IRC_CHAN='#zordania'
ENV IRC_PSEUDO=Barnabe
ENV IRC_PASS='changeit'
ENV IRC_USER=zordania
# === Configuration DISCORD ===
ENV DISCORD_WEBHOOK='token_webhook_changeit'
# === Configuration debug ou travaux ===
ENV SITE_DEBUG='false'
ENV SITE_TRAVAUX='false'
