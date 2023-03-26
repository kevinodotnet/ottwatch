#!/bin/bash

./docker-prod-stop.sh
./docker-prod-web.sh
./docker-prod-sidekiq.sh

