#!/bin/sh

set -eu

OPTIND=1

TARGET=localhost
CURL_OPTS="--connect-timeout 5 --silent --show-error --fail"

# shellcheck disable=SC2086
if curl ${CURL_OPTS} "http://${TARGET}:8080/" ; then
  exit 0;
else
  >&2 printf "can't open app";
  exit 1;
fi;
