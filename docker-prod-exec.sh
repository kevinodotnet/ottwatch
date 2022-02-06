#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker run \
  --rm \
	--network prodweb \
	-e RAILS_ENV=production \
	-e DB_HOST=$DB_HOST \
	-e DB_NAME=$DB_NAME \
	-e DB_USER=$DB_USER \
	-e DB_PASS=$DB_PASS \
	-e RAILS_MASTER_KEY=$RAILS_MASTER_KEY \
  -e REDIS_URL=$REDIS_URL \
  -i -t \
	ottwatch-prod $*

