FROM node:22 AS frontend-builder

WORKDIR /srv/xero-rfm

COPY . .
RUN npm install && npm run build

FROM php:8.4-fpm-trixie AS app

LABEL maintainer="alexeydemidov@gmail.com"

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get -y -q upgrade \
      && apt-get install -y --no-install-recommends unzip msmtp-mta libfcgi-bin \
      && apt-get autoremove -y \
      && apt-get clean \
      && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
      && docker-php-ext-configure opcache --enable-opcache \
      && docker-php-ext-install pdo_mysql

COPY docker/php/php.ini $PHP_INI_DIR/php.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/timezone.ini $PHP_INI_DIR/conf.d/
COPY docker/php/docker-php-entrypoint /usr/local/bin
COPY docker/php/healthcheck.sh /healthcheck.sh

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

WORKDIR /srv/xero-rfm

COPY . .

COPY storage storage-init
COPY docker/php/check.php public

RUN /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

COPY --from=frontend-builder /srv/xero-rfm/public public

EXPOSE 9000
CMD ["php-fpm"]

HEALTHCHECK --interval=30s --timeout=10s CMD /healthcheck.sh || exit 1

FROM alpine:3.22 AS nginx

LABEL maintainer="alexeydemidov@gmail.com"

RUN apk --update --no-cache add nginx=~1.28 curl=~8.14 libintl=~0.24 && \
    apk add --no-cache --virtual build_deps gettext &&  \
    cp /usr/bin/envsubst /usr/local/bin/envsubst && \
    apk del build_deps

WORKDIR /srv/xero-rfm/public

COPY --from=frontend-builder /srv/xero-rfm/public/ .

COPY docker/nginx/app.conf.template /etc/nginx/http.d/
COPY docker/nginx/nginx.conf /etc/nginx/
COPY docker/nginx/healthcheck.sh /healthcheck.sh
COPY docker/nginx/docker-entrypoint.sh /docker-entrypoint.sh

RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log

EXPOSE 8080

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]

HEALTHCHECK --interval=30s --timeout=10s CMD /healthcheck.sh || exit 1
