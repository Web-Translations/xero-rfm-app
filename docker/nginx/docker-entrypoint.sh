#!/usr/bin/env sh
set -eu

[ -f /local/http_realip.conf ] && cp /local/http_realip.conf /etc/nginx/http.d/http_realip.conf

# shellcheck disable=SC2016
envsubst '${NOMAD_ADDR_fastcgi},${APP_DOMAIN}' < /etc/nginx/http.d/app.conf.template > /etc/nginx/http.d/app.conf

exec "$@"
