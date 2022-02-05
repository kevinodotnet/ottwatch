#!/bin/bash

# create/run the container, or if it already exists, restart the existing one
# use 'docker container rm ottwatch-dev' to reset from the beginning

echo ""
echo "/etc/init.d/mysql start"
echo ""
echo "alias m='mysql ottwatch_dev'"
echo "mysql ottwatch_dev < db/ottwatch_v1_snapshot.sql"
echo ""
echo "drop table candidate_returns;"
echo "drop table candidates;"
echo "drop table candidate_donations;"
echo "drop table candidate_returns;"
echo "alter table candidate rename to candidates;"
echo "alter table candidate_donation rename to candidate_donations;"
echo "alter table candidate_return rename to candidate_returns; "
echo "alter table election rename to elections;"
echo ""

docker run --name ottwatch-dev -p 33000:3000 -v `pwd`:/ottwatch -i -t ottwatch-dev || \
  docker start -i ottwatch-dev
