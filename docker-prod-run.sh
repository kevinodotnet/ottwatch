#
# Build the image
#   docker build -t ottwatch-prod -f Dockerfile.prod .
#
# Get a shell:
#   sudo docker exec -it ottwatch-prod /bin/bash
#
# Start it again:
#   docker start -i ottwatch-prod

. ~/src/infra/ottwatch.sh

sudo docker stop ottwatch-web
sudo docker container rm ottwatch-web

sudo docker run \
	-d \
	-e RAILS_ENV=production \
	-e DB_HOST=$DB_HOST \
	-e DB_NAME=$DB_NAME \
	-e DB_USER=$DB_USER \
	-e DB_PASS=$DB_PASS \
	-e RAILS_MASTER_KEY=$RAILS_MASTER_KEY \
	-p 3000:3000 \
	--name ottwatch-web \
	ottwatch-prod

