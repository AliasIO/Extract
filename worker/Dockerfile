FROM phusion/baseimage

MAINTAINER Elbert Alias <elbert@alias.io>

ENV PROJECT_FOLDER /usr/local/extract_worker/

ENV DEBIAN_FRONTEND noninteractive

RUN mkdir -p $PROJECT_FOLDER

WORKDIR $PROJECT_FOLDER

ADD bin bin
ADD index.php index.php
ADD process.php process.php

RUN mkdir lib log

# Apt
RUN \
	apt-get update && apt-get install -y \
	jq \
	libfreetype6 \
	libfontconfig \
	php5-cli \
	php5-curl \
	php5-mongo \
	php5-sqlite \
	&& apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Composer
RUN \
	curl -sS https://getcomposer.org/installer | php && \
	mv composer.phar /usr/local/bin/composer

# PhantomJS
RUN \
	mkdir lib/phantomjs && \
	curl -L https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-1.9.8-linux-x86_64.tar.bz2 | tar xvjC lib/phantomjs --strip 1

RUN \
	mkdir lib/aranea && \
	curl -sSL https://github.com/AliasIO/Aranea/archive/master.tar.gz | tar xzC lib/aranea --strip 1 && \
	cd lib/aranea && \
	composer dumpautoload

RUN \
	mkdir lib/congregate && \
	curl -sSL https://github.com/AliasIO/Congregate/archive/master.tar.gz | tar xzC lib/congregate --strip 1 && \
	cd lib/congregate && \
	composer dumpautoload

RUN \
	mkdir lib/geoip && \
	curl -sSL https://github.com/AliasIO/GeoIP/archive/master.tar.gz | tar xzC lib/geoip --strip 1 && \
	cd lib/geoip && \
	composer dumpautoload

RUN \
	mkdir lib/wappalyzer && \
	curl -sSL https://github.com/AliasIO/Wappalyzer/archive/master.tar.gz | tar xzC lib/wappalyzer --strip 1 && \
	lib/wappalyzer/bin/wappalyzer-links lib/wappalyzer

CMD ["bin/extract"]
