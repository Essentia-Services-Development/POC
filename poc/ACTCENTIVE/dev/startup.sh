#!/bin/sh
cp /home/dev/etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf && \
cp /home/dev/etc/redis.conf /etc/redis.conf && \
apk update && \
apk add autoconf gcc make icu-dev && \
apk add imagemagick imagemagick-dev && \
apk add php8 php8-dev php8-pear && \
pecl install imagick && \
service nginx restart