#!/bin/bash

instance=$1
if [ -z "${instance}" ]; then
  instance="ottwatch-dev"
fi

docker exec -it $instance /bin/bash

