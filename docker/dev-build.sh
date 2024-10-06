#!/bin/bash

cd `dirname $0`

# --progress=plain
#
# BUILD_NO_CACHE=--no-cache ./dev-build.sh

docker build $BUILD_NO_CACHE -t ottwatch-base -f Dockerfile.base . && \
  docker build $BUILD_NO_CACHE -t ottwatch-dev -f Dockerfile.dev .
