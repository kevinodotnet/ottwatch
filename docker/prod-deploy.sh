#!/bin/bash

# BUILD_NO_CACHE=--no-cache ./prod-deploy.sh

cd `dirname $0`

. ~/src/infra/ottwatch-snack.sh

echo "############################"
date
echo "Building..."
echo ""
./prod-build.sh

echo ""
echo "############################"
date
echo "Stopping..."
echo ""
./prod-stop.sh

echo ""
echo "############################"
date
echo "Migrating..."
echo ""
./prod-exec.sh bin/rails db:migrate:primary

echo ""
echo "############################"
date
echo "Web..."
echo ""
./prod-web.sh

echo ""
echo "############################"
date
echo "Done..."
echo ""
