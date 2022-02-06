#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker run \
  -d \
  -v $INFRA_FOLDER:/infra \
  --name redis redis

