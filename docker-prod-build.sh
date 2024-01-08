#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker build -t ottwatch-base -f Dockerfile.base .
sudo docker build -t ottwatch-prod -f Dockerfile.prod .
#sudo docker build --no-cache -t ottwatch-prod -f Dockerfile.prod .
sudo docker image prune -f
