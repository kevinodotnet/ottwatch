<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

`mkdir t`;
print `unzip -q opendata_wards2010.zip -d t`;

$mysql = ' MYSQL:"'.OttWatchConfig::DB_NAME.',host='.OttWatchConfig::DB_HOST.',user='.OttWatchConfig::DB_USER.',password='.OttWatchConfig::DB_PASS.',port=3306" ';
$cmd = " ogr2ogr -t_srs 'EPSG:4326' -f 'MySQL' {$mysql} t/Wards_2010.shp -overwrite -lco SPATIAL_INDEX=NO ";

print `$cmd`;

`rm -rf t`;

