#!/bin/bash

# reminder:
# sudo docker exec -it ottwatch-XXX /bin/bash

. ~/src/infra/ottwatch.sh

./docker-prod-build.sh

./docker-prod-stop.sh

sudo docker container rm ottwatch-web

./docker-prod-exec.sh bin/rails db:migrate
./docker-prod-start.sh

