#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker container stop ottwatch-sidekiq
sudo docker container rm ottwatch-sidekiq
sudo docker run \
  --restart always \
  -d \
  --network $DOCKER_NETWORK \
  -e BUGSNAG_KEY=$BUGSNAG_KEY \
  -e DB_HOST=$DB_HOST \
  -e DB_NAME=$DB_NAME \
  -e DB_NAME_V1=$DB_NAME_V1 \
  -e DB_PASS=$DB_PASS \
  -e DB_USER=$DB_USER \
  -e GCS_KEYFILE=/infra/gcs-prodweb-service-account.json \
  -e MASTEDON_ACCESS_TOKEN=$MASTEDON_ACCESS_TOKEN \
  -e RAILS_ENV=production \
  -e RAILS_MASTER_KEY=$RAILS_MASTER_KEY \
  -e REDIS_URL=$REDIS_URL \
  -e SENDGRID_API_KEY=$SENDGRID_PRODWEB_FULL \
  -e RAILS_MAX_THREADS=10 \
  -e LOCAL_STORAGE_FOLDER=$LOCAL_STORAGE_FOLDER_CLIENT \
  -v $INFRA_FOLDER:/infra \
  -v $LOCAL_STORAGE_FOLDER_HOST:$LOCAL_STORAGE_FOLDER_CLIENT \
  --name ottwatch-sidekiq \
  ottwatch-prod \
  bundle exec sidekiq -q ottwatch_production_default

