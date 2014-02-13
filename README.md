ottwatch
========

Code for <a href="http://ottwatch.ca">ottwatch.ca</a> - parts of which should be compatible with any SIREPUB municipal records management system

Installation
============

You will need PHP and MySQL 5.6 (beta right now). The newest MySQL is needed for the new GIS features that
finally put it on par with PostGRES. I use PHP 5.3.10 and MySQL 5.6.10

The contents of /www is the root of the website. Everything else is used for background tasks and cron jobs.

Configuration is provided in lib/config.php. A sample file is provided in lib/config-sample.php

Scraping is performed by files in /bin. See the "Crontab" section below for ottwatch.ca's setup.

Database
========

After creating a MySQL database the schema can be created via:

  % mysql < lib/schema.sql

I've also provided SQL files for the opendata used so you don't have to worry about using "ogr2ogr" to convert from
SHP files to Mysql GIS objects. You can load each of those as well with:

  % for i in lib/opendata_*sql; do echo mysql < $i; done

Crontab
=======

Here is the crontab I use on ottwatch.ca to scrape data and also post it to Twitter

  # Scape the SIREpub RSS feed every 30 minutes looking for new meeting details
  
  00,30 * * * * php /mnt/www/ottwatch/bin/meeting-parser.php
  
  # Once a day hard-scrape upcoming meetings even if the RSS doesn't indicate changes
  
  00 5 * * * php /mnt/www/ottwatch/bin/meeting-parser.php hardScan
  
  # Tweet meeting updates during daytimes only
  
  05,35 8-17 * * * php /mnt/www/ottwatch/bin/meeting-tweeter.php
  
  # Scape and tweet about development applications, daytime only
  
  15,45 8-16 * * * php /mnt/www/ottwatch/bin/devapp-tweeter.php
  
  # Scrape the lobbyist registry, going back 30 days
  
  20,50 * * * * php /mnt/www/ottwatch/bin/lobby-scaper.php 30
  
  # Tweet the lobbyist registry, daytime only
  
  25,55 7-18 * * * php /mnt/www/ottwatch/bin/lobby-tweeter2.php


	# Current crontab as of Jan-21-2014
	*/5 * * * * php /mnt/www/ottwatch/bin/media-rss.php
	*/5 * * * * php /mnt/www/ottwatch/bin/syndicate.php
	00,30 * * * * php /mnt/www/ottwatch/bin/meeting-parser.php
	05,35 * * * * php /mnt/www/ottwatch/bin/meeting-tweeter.php
	00 5,15,19,22 * * * php /mnt/www/ottwatch/bin/meeting-parser.php hardScan
	00 8-18 * * * php /mnt/www/ottwatch/bin/meeting-parser.php getVideos
	00 8-18/2 * * * php /mnt/www/ottwatch/bin/meeting-parser.php getVideoStart
	00 7,19 * * * php /mnt/www/ottwatch/bin/opendata.php 
	15,45 * * * * php /mnt/www/ottwatch/bin/devapp-tweeter.php
	20,50 * * * * php /mnt/www/ottwatch/bin/lobby-scaper.php 30
	25,55 * * * * php /mnt/www/ottwatch/bin/lobby-tweeter2.php
	00 8 * * * php /mnt/www/ottwatch/bin/election-countdown.php
	0 7-21/2 * * * php /mnt/www/ottwatch/bin/consultations-parser.php
	0 * * * * php /mnt/www/ottwatch/bin/public-notice-tweeter.php 
	*/5 * * * * php /mnt/www/ottwatch/bin/page-monitor.php http://ottawa.ca/en/city-hall/your-city-government/elections/councillor
	*/5 * * * * php /mnt/www/ottwatch/bin/page-monitor.php http://ottawa.ca/en/city-hall/your-city-government/elections/mayor
	*/5 * * * * php /mnt/www/ottwatch/bin/disqus.php

Contributions
=============

Many parts of this code are Ottawa specific, but the "Meeting" portions should be adaptable for any city that 
uses the SIREpub system. Good luck! I'm happy to merge "pull requests" if you send them. But really, probably
the best path forward for other cities is to fork OttWatch into a generic SIREpub application and make that
one better (and city generic).

License
=======

* All files are Copyright 2013 - Kevin O'Donnell unless otherwise stated, and are licensed under the Apache Licence, Version 2.0, see http://www.apache.org/licenses/LICENSE-2.0
* Bootstrap is re-distributed under the Apache Licence, Version 2.0, see http://twitter.github.io/bootstrap/
* Ephiphany is re-distributed with permission, see https://github.com/jmathai/epiphany
* HighRoller is re-distributed with permission, see https://github.com/jmaclabs/HighRoller
* PHPoAuthLib is re-distributed with permission, see https://github.com/Lusitanian/PHPoAuthLib
* Snapshots of Ottawa Open Data files (lib/opendata_*) re-distributed with permission, see http://ottawa.ca/en/open-data-terms-use
* CkEditor is re-distributed with permission, see https://github.com/ckeditor/ckeditor-releases
* Springy is re-distributed with permission, see https://github.com/dhotson/springy

About Me
========

I live in Ottawa, like Open Data, and am neck-deep in pushing for a more sustainable city, environment,
province and planet. For a living I write code. For my future I'm Deputy Leader of the Green Party 
of Ontario. Deets here: http://kevino.ca and http://www.gpo.ca/about/deputy-leader/kevin-odonnell

