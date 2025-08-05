#!/bin/bash

cd `dirname $0`
cd docker

# BUILD_NO_CACHE=--no-cache ./dev-up.sh

instance="dev_ottwatch"
port=33000

docker build $BUILD_NO_CACHE -t ottwatch-base -f Dockerfile.base . && \
  docker stop -t 0 dev_ottwatch && \
  docker rm dev_ottwatch && \
  docker run -d --name dev_ottwatch -p $port:3000 -v `pwd`/..:/ottwatch -i -t ottwatch-base

cat << EOF
#
# when container is run/re-started:
#
/etc/init.d/mysql start

#
# on first run of container:
#
cd /ottwatch; bin/rails db:setup
EOF

