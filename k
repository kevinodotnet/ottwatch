
# Daily backup to S3
0 1 * * * /mnt/www/bin/snapshot-websites.sh

# OttawaCentreGreens
*/10 * * * * wget -qO /dev/null 'http://ottawacentregreens.ca/lists/admin/?page=processqueue&login=admin&password=zepasswerd' 

# Martin
0 5 * * * wget -O - -q -t 1 "http://martincanning.ca/cron.php?cron_key=WFBB4VMqwELVqSK0M-vtSn-_8ORBmU5kfdTngBxgFFQ"
*/30 * * * * /mnt/www/martincanning.ca/civi_cron.sh

# Jeff
15 5 * * * wget -O - -q -t 1 "http://jeffleiper.ca/cron.php?cron_key=EAdhpSA3Kp5PhkbfB4QUy4xqAsRNkjC6V5cf0zLt9q8"

# Meetings
00,30 * * * * php /mnt/www/ottwatch/bin/meeting-parser.php
00 5 * * * php /mnt/www/ottwatch/bin/meeting-parser.php hardScan
05,35 8-17 * * * php /mnt/www/ottwatch/bin/meeting-tweeter.php
15,45 8-16 * * * php /mnt/www/ottwatch/bin/devapp-tweeter.php
20,50 * * * * php /mnt/www/ottwatch/bin/lobby-scaper.php 30
#25,55 7-18 * * * php /mnt/www/ottwatch/bin/lobby-tweeter2.php

# Oh man
# */15 * * * * /home/ubuntu/thetab
