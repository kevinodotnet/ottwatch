#!/bin/bash

# create/run the container, or if it already exists, restart the existing one
# use 'docker container rm ottwatch-dev' to reset from the beginning

echo ""
echo ""
echo "cd ottwatch; bin/rails db:setup"
echo "/etc/init.d/mysql start"
echo "alias m='mysql ottwatch_dev'"
echo "mysql ottwatch_dev < db/ottwatch_v1_snapshot.sql"
echo ""

docker run --name ottwatch-dev -p 33000:3000 -v `pwd`:/ottwatch -i -t ottwatch-dev || \
  docker start -i ottwatch-dev

