<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

`rm -rf t`;
`mkdir t`;
`wget -qO t/t.zip http://data.ottawa.ca/en/storage/f/2014-09-24T142916/2014_VSD_Final.zip`;
`unzip -q t/t.zip -d t`;

$mysql = ' MYSQL:"'.OttWatchConfig::DB_NAME.',host='.OttWatchConfig::DB_HOST.',user='.OttWatchConfig::DB_USER.',password='.OttWatchConfig::DB_PASS.',port=3306" ';
$cmd = " ogr2ogr -t_srs 'EPSG:4326' -f 'MySQL' {$mysql} t/2014_VSD_Final.shp -overwrite -lco SPATIAL_INDEX=NO -nln polls_2014 ";
print `$cmd`;

`rm -rf t`;

