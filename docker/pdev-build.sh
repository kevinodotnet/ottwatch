#!/bin/bash

# --progress=plain
#
# BUILD_NO_CACHE=--no-cache ./dev-build.sh

podman build $BUILD_NO_CACHE -t ottwatch-base -f Dockerfile.base . && \
  podman build $BUILD_NO_CACHE -t ottwatch-dev -f Dockerfile.dev .
