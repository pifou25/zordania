#! /bin/bash

thedate=`/bin/date +%D | /usr/bin/tr ./. .-.`
cd /home/zordania/backup/
/usr/bin/mysqldump -u USER -pPASSWORD DBNAME  > zordania.dump.$thedate
gzip zordania.dump.$thedate