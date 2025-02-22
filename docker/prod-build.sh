#!/bin/bash

. ~/src/infra/ottwatch-snack.sh

sudo docker build $BUILD_NO_CACHE -t ottwatch-base -f Dockerfile.base .
sudo docker build $BUILD_NO_CACHE -t ottwatch-prod -f Dockerfile.prod .
sudo docker image prune -f
