#!/bin/bash

docker build --progress=plain -t ottwatch-base -f Dockerfile.base . && \
  docker build --progress=plain -t ottwatch-dev -f Dockerfile.dev . && \
  docker build --progress=plain -t ottwatch-prod -f Dockerfile.prod .
