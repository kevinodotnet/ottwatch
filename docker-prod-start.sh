#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker run \
  --rm \
  -d \
	-e RAILS_ENV=production \
	-e DB_HOST=$DB_HOST \
	-e DB_NAME=$DB_NAME \
	-e DB_USER=$DB_USER \
	-e DB_PASS=$DB_PASS \
	-e RAILS_MASTER_KEY=$RAILS_MASTER_KEY \
  -p 3000:3000 \
	--name ottwatch-web \
	ottwatch-web

