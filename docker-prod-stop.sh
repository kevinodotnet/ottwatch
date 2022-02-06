#!/bin/bash

. ~/src/infra/ottwatch.sh

sudo docker stop ottwatch-web
sudo docker stop ottwatch-sidekiq
