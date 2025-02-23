#!/bin/bash

. ~/src/infra/ottwatch-snack.sh

sudo docker container stop ottwatch-web
sudo docker container rm ottwatch-web

sudo docker run \
  --restart always \
  -d \
  --network $DOCKER_NETWORK \
  --ip 10.50.1.13 \
  -e BUGSNAG_KEY=$BUGSNAG_KEY \
  -e DB_HOST=$DB_HOST \
  -e DB_NAME=$DB_NAME \
  -e DB_NAME_V1=$DB_NAME_V1 \
  -e DB_PASS=$DB_PASS \
  -e DB_USER=$DB_USER \
  -e SOLID_QUEUE_IN_PUMA=1 \
  -e GCS_KEYFILE=/infra/gcs-prodweb-service-account.json \
  -e GOOGLE_MAPS_API_KEY=$GOOGLE_MAPS_API_KEY \
  -e GOOGLE_WEB_APP_CLIENT_ID=$GOOGLE_WEB_APP_CLIENT_ID \
  -e GOOGLE_WEB_APP_CLIENT_SECRET=$GOOGLE_WEB_APP_CLIENT_SECRET \
  -e RAILS_ENV=production \
  -e RAILS_MASTER_KEY=$RAILS_MASTER_KEY \
  -e RAILS_SERVE_STATIC_FILES=1 \
  -e REDIS_URL=$REDIS_URL \
  -e SENDGRID_API_KEY=$SENDGRID_PRODWEB_FULL \
  -e OCTRANSPO_APP_ID=$OCTRANSPO_APP_ID \
  -e OCTRANSPO_APP_KEY=$OCTRANSPO_APP_KEY \
  -e RAILS_MAX_THREADS=10 \
  -e GITHUB_APP_ID=$GITHUB_APP_ID \
  -e GITHUB_APP_SECRET=$GITHUB_APP_SECRET \
  -e LOCAL_STORAGE_FOLDER=$LOCAL_STORAGE_FOLDER_CLIENT \
  -v $INFRA_FOLDER:/infra \
  -v $LOCAL_STORAGE_FOLDER_HOST:$LOCAL_STORAGE_FOLDER_CLIENT \
  -p 3000:3000 \
  --name ottwatch-web \
  ottwatch-prod bin/rails server

