#!/bin/sh

set -eu

OPTIND=1

export REQUEST_METHOD="GET"
export SCRIPT_NAME="/status"
export SCRIPT_FILENAME="/status"
FCGI_CONNECT_DEFAULT="localhost:9000"

FPM_STATUS=$(cgi-fcgi -bind -connect "$FCGI_CONNECT_DEFAULT" 2> /dev/null)
FPM_STATUS=$(echo "$FPM_STATUS" | tail +5)

if test "$FPM_STATUS" = "File not found."; then
  >&2 printf "php-fpm status page non reachable\\n";
  exit 8;
fi;
