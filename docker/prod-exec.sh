#!/bin/bash

. ~/src/infra/ottwatch-snack.sh

sudo docker run \
  --rm \
	--network $DOCKER_NETWORK \
  -e BUGSNAG_KEY=$BUGSNAG_KEY \
	-e DB_HOST=$DB_HOST \
	-e DB_NAME=$DB_NAME \
	-e DB_NAME_V1=$DB_NAME_V1 \
	-e DB_PASS=$DB_PASS \
	-e DB_USER=$DB_USER \
  -e MASTEDON_ACCESS_TOKEN=$MASTEDON_ACCESS_TOKEN \
	-e RAILS_ENV=production \
	-e RAILS_MASTER_KEY=$RAILS_MASTER_KEY \
  -e GCS_KEYFILE=/infra/gcs-prodweb-service-account.json \
  -e REDIS_URL=$REDIS_URL \
  -e SENDGRID_API_KEY=$SENDGRID_PRODWEB_FULL \
  -e LOCAL_STORAGE_FOLDER=$LOCAL_STORAGE_FOLDER_CLIENT \
  -v $INFRA_FOLDER:/infra \
  -v $LOCAL_STORAGE_FOLDER_HOST:$LOCAL_STORAGE_FOLDER_CLIENT \
  -i -t \
	ottwatch-prod $*

