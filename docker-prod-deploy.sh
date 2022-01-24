#!/bin/bash

# reminder:
# sudo docker exec -it ottwatch-XXX /bin/bash

. ~/src/infra/ottwatch.sh

./docker-prod-build.sh

./docker-prod-stop.sh

sudo docker container rm ottwatch-web
sudo docker container rm ottwatch-migrate

#
# TODO: automate running the MIGRATE image
#

./docker-prod-web-run.sh

