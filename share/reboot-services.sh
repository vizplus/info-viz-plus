#!/bin/bash
for filename in /var/www/logs/*
do
	cat /dev/null > ${filename}
done
/usr/sbin/service nginx stop
/usr/sbin/service mysql stop
/usr/sbin/service php7.3-fpm restart
/usr/sbin/service mysql start
/usr/sbin/service nginx start
