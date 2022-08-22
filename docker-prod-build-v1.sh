#!/bin/bash

. ~/src/infra/ottwatch.sh

DOCKER_FILES="
  Dockerfile.prod-v1
"

for i in $DOCKER_FILES; do
  name=`echo $i | sed 's/Dockerfile./ottwatch-/'`
  sudo docker build -t $name -f $i .
done

# sudo docker image prune -f
