FROM phusion/baseimage

MAINTAINER Elbert Alias <elbert@alias.io>

ENV PROJECT_FOLDER /usr/local/extract_worker/

ENV DEBIAN_FRONTEND noninteractive

RUN mkdir -p $PROJECT_FOLDER

ADD . $PROJECT_FOLDER

WORKDIR $PROJECT_FOLDER

# Apt
RUN \
	apt-get update && apt-get install -y \
	jq \
	php5-cli \
	php5-curl \
	php5-mongo \
	php5-sqlite \
	&& apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENTRYPOINT ["bin/extract"]
