#!/bin/bash

# echo ""
# echo "/etc/init.d/mysql start"
# echo "alias m='mysql ottwatch_dev'"
# echo "mysql ottwatch_dev < db/ottwatch_v1_snapshot.sql"
# echo ""

# docker container rm ottwatch-v1-dev

docker run \
  --name ottwatch-v1-dev \
  -v `pwd`:/ottwatch \
  -i -t \
  -p 8080:80 \
  -v `pwd`/archive/www:/var/www/html \
  ottwatch-base-v1 || \
  docker start -i ottwatch-v1-dev