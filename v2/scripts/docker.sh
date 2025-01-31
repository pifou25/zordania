#!/bin/bash

# préparer le fichier de config
cp conf/secret_parameters.php.default conf/secret_parameters.php

sed -i "s/define('MYSQL_BASE', 'zordania');/define('MYSQL_BASE', '${MARIADB_DATABASE}');/g" conf/secret_parameters.php
sed -i "s/define('MYSQL_HOST', 'localhost');/define('MYSQL_HOST', '${MARIADB_HOST}');/g" conf/secret_parameters.php
sed -i "s/define('MYSQL_USER', 'zordania');/define('MYSQL_USER', '${MARIADB_USER}');/g" conf/secret_parameters.php
sed -i "s/define('MYSQL_PASS', 'zordania');/define('MYSQL_PASS', '${MARIADB_PASSWORD}');/g" conf/secret_parameters.php

# run apache
exec apache2-foreground
