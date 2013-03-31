
# Daily backup to S3
0 1 * * * /mnt/www/bin/snapshot-websites.sh

# Generate easy to copy snapshot backups
0 5 * * * wget -O - -q -t 1 "http://new.martincanning.ca/cron.php?cron_key=WFBB4VMqwELVqSK0M-vtSn-_8ORBmU5kfdTngBxgFFQ"
*/5 * * * * /mnt/www/martincanning.ca/civi_cron.sh

# OttWatch

# Meetings
00,30 8-17 * * * php /mnt/www/ottwatch/bin/meeting-parser.php
05,35 8-17 * * * php /mnt/www/ottwatch/bin/meeting-tweeter.php

# Development Applications
15,45 8-16 * * * php /mnt/www/ottwatch/bin/devapp-tweeter.php

# Lobbyist Registry
20,50 7-18 * * * php /mnt/www/ottwatch/bin/lobby-scaper.php 30
25,55 7-18 * * * php /mnt/www/ottwatch/bin/lobby-tweeter2.php

