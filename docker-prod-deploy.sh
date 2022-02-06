#!/bin/bash

. ~/src/infra/ottwatch.sh

./docker-prod-build.sh

./docker-prod-stop.sh

./docker-prod-exec.sh bin/rails db:migrate

./docker-prod-sidekiq.sh
./docker-prod-web.sh

