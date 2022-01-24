#!/bin/bash

. ~/src/infra/ottwatch.sh

DOCKER_FILES="
  Dockerfile.base
  Dockerfile.prod
  Dockerfile.migrate
  Dockerfile.web
"

for i in $DOCKER_FILES; do
  name=`echo $i | sed 's/Dockerfile./ottwatch-/'`
  sudo docker build -t $name -f $i .
done

