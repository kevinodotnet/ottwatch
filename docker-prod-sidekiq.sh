#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker run \
  --restart always \
  -d \
  --network prodweb \
  -e RAILS_ENV=production \
  -e DB_HOST=$DB_HOST \
  -e DB_NAME=$DB_NAME \
  -e DB_USER=$DB_USER \
  -e DB_PASS=$DB_PASS \
  -e REDIS_URL=$REDIS_URL \
  -e TWITTER_POST_ACCESS_TOKEN=$TWITTER_POST_ACCESS_TOKEN \
  -e TWITTER_POST_CONSUMER_KEY=$TWITTER_POST_CONSUMER_KEY \
  -e TWITTER_POST_CONSUMER_SECRET=$TWITTER_POST_CONSUMER_SECRET \
  -e TWITTER_POST_TOKEN_SECRET=$TWITTER_POST_TOKEN_SECRET \
  -e RAILS_MASTER_KEY=$RAILS_MASTER_KEY \
  --name ottwatch-sidekiq \
  ottwatch-prod \
  bundle exec sidekiq -q ottwatch_production_default

