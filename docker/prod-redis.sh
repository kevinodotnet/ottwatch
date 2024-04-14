#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker run \
  --restart always \
  -d \
  --network $DOCKER_NETWORK \
  -v $INFRA_FOLDER:/infra \
  --ip $REDIS_IP \
  --name redis redis

