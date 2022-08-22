#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker container rm ottwatch-web-v1

sudo docker run \
  --restart always \
  -it \
  --network prodweb \
  -e DB_HOST=$DB_HOST \
  -e DB_NAME=$DB_NAME \
  -e DB_USER=$DB_USER \
  -e DB_PASS=$DB_PASS \
  -v $INFRA_FOLDER:/infra \
  -p 3001:80 \
  --name ottwatch-web-v1 \
  ottwatch-prod-v1 /bin/bash

