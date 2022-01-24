#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker container rm ottwatch-migrate

sudo docker run \
	-e RAILS_ENV=production \
	-e DB_HOST=$DB_HOST \
	-e DB_NAME=$DB_NAME \
	-e DB_USER=$DB_USER \
	-e DB_PASS=$DB_PASS \
	-e RAILS_MASTER_KEY=$RAILS_MASTER_KEY \
	--name ottwatch-migrate \
	ottwatch-migrate
