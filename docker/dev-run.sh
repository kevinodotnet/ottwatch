#!/bin/bash

cd `dirname $0`

# create/run the container, or if it already exists, restart the existing one
# use 'docker container rm ottwatch-dev' to reset from the beginning

cat << EOF
#
# when container is run/re-started:
#
/etc/init.d/mysql start

#
# on first run of container:
#
cd ottwatch; bin/rails db:setup
EOF

instance=$1
if [ -z "${instance}" ]; then
  instance="ottwatch-dev"
fi

port=$2
if [ -z "${port}" ]; then
  port=33000
fi

docker run --name $instance -p $port:3000 -v `pwd`/..:/ottwatch -i -t ottwatch-dev || \
  docker start -i $instance

