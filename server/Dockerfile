FROM phusion/baseimage

MAINTAINER Elbert Alias <elbert@alias.io>

ENV PROJECT_FOLDER /usr/local/extract_server/

ENV DEBIAN_FRONTEND noninteractive

RUN mkdir -p $PROJECT_FOLDER

ADD . $PROJECT_FOLDER

WORKDIR $PROJECT_FOLDER

RUN \
	apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10 && \
	apt-get update && apt-get install -y \
	nginx \
	php-apc \
	php5-cli \
	php5-dev \
	php5-fpm \
	php-pear \
	&& apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PECL
RUN pecl channel-update pecl.php.net && pecl install mongo

# Nginx
ADD docker/nginx/vhost.conf /etc/nginx/sites-enabled/default

ADD docker/nginx/run /etc/service/nginx/run

# PHP FPM
ADD docker/php-fpm/env.conf /etc/php5/fpm/pool.d/env.conf

ADD docker/php-fpm/mongo.ini /etc/service/php-fpm/conf.d/20-mongo.ini

ADD docker/php-fpm/run /etc/service/php-fpm/run

EXPOSE 80

ENTRYPOINT ["bin/extract"]
