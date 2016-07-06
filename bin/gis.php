<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

$action = $argv[1];

if ($action == 'mercToLatLon') {
	$latlon = mercatorToLatLon($argv[2],$argv[3]);
	pr($latlon);
	print "\n";
	print "https://www.google.ca/maps?q={$latlon['lat']}+{$latlon['lon']}\n";
	return;
}

