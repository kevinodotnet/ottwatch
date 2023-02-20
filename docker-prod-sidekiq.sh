#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker container rm ottwatch-sidekiq
sudo docker run \
  --restart always \
  -d \
  --network prodweb \
  -e DB_HOST=$DB_HOST \
  -e DB_NAME=$DB_NAME \
  -e DB_NAME_V1=$DB_NAME_V1 \
  -e DB_PASS=$DB_PASS \
  -e DB_USER=$DB_USER \
  -e GCS_KEYFILE=/infra/gcs-prodweb-service-account.json \
  -e RAILS_ENV=production \
  -e RAILS_MASTER_KEY=$RAILS_MASTER_KEY \
  -e REDIS_URL=$REDIS_URL \
  -e SENDGRID_API_KEY=$SENDGRID_PRODWEB_FULL \
  -e TWITTER_OAUTH_CLIENT_ID=$TWITTER_OAUTH_CLIENT_ID \
  -e TWITTER_OAUTH_CLIENT_SECRET=$TWITTER_OAUTH_CLIENT_SECRET \
  -e TWITTER_POST_ACCESS_TOKEN=$TWITTER_POST_ACCESS_TOKEN \
  -e TWITTER_POST_CONSUMER_KEY=$TWITTER_POST_CONSUMER_KEY \
  -e TWITTER_POST_CONSUMER_SECRET=$TWITTER_POST_CONSUMER_SECRET \
  -e TWITTER_POST_TOKEN_SECRET=$TWITTER_POST_TOKEN_SECRET \
  -v $INFRA_FOLDER:/infra \
  --name ottwatch-sidekiq \
  ottwatch-prod \
  bundle exec sidekiq -q ottwatch_production_default

