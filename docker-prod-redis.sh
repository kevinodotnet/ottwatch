#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker run \
  --rm \
  -d \
	--network prodweb \
  --log-driver=gcplogs \
  -v $INFRA_FOLDER:/infra \
  --ip $REDIS_IP \
  --name redis redis

