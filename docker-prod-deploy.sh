#!/bin/bash

cd `dirname $0`

. ~/src/infra/ottwatch.sh

echo "############################"
date
echo "Building..."
echo ""
./docker-prod-build.sh

echo ""
echo "############################"
date
echo "Stopping..."
echo ""
./docker-prod-stop.sh

echo ""
echo "############################"
date
echo "Migrating..."
echo ""
./docker-prod-exec.sh bin/rails db:migrate:primary

echo ""
echo "############################"
date
echo "Sidekiq..."
echo ""
./docker-prod-sidekiq.sh

echo ""
echo "############################"
date
echo "Web..."
echo ""
./docker-prod-web.sh

echo ""
echo "############################"
date
echo "Done..."
echo ""
