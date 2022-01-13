#!/bin/bash

# --progress=plain

docker build -t ottwatch-base -f Dockerfile.base . && \
  docker build -t ottwatch-dev -f Dockerfile.dev .

# docker build -t ottwatch-prod -f Dockerfile.prod .
