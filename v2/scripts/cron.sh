#!/bin/bash
DATE=$(date "+%d_%m_%Y.%H")
ZPATH=$(dirname `pwd`/$0)/../
echo "cron $ZPATH $DATE"
php -q $ZPATH/crons/cron.php >> $ZPATH/logs/crons/out/out_$DATE.log
