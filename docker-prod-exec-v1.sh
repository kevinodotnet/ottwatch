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
  -i -t \
	ottwatch-prod-v1 $*

