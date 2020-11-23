FROM php:7.4-fpm
RUN apt-get update && apt-get install -y libmcrypt-dev

RUN docker-php-ext-install pdo pdo_mysql
ADD zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
RUN apt-get install -y libz-dev
RUN apt-get install -y zip git