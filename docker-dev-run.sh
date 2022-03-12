#!/bin/bash

# create/run the container, or if it already exists, restart the existing one
# use 'docker container rm ottwatch-dev' to reset from the beginning

echo ""
echo "/etc/init.d/mysql start"
echo "bin/rails db:setup"
echo ""

docker run --name ottwatch-dev -p 33000:3000 -v `pwd`:/ottwatch -i -t ottwatch-dev || \
  docker start -i ottwatch-dev

