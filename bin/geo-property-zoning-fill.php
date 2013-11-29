<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

while (true) {

	# next geo_property that is not in geo_property_zoning
	$sql = " select min(ottwatchid) ottwatchid from geo_property where ottwatchid > (select max(property_id) from geo_property_zoning) ";
	print ".";
	$prop = getDatabase()->one($sql);
	if (!isset($prop['ottwatchid'])) {
		print "\nDONE!\n";
		exit;
	}

	# take it out of the work loop
	getDatabase()->execute(" insert into geo_property_zoning (property_id) values ({$prop['ottwatchid']}) ");

	# fine zoning(s) that apply to that property
	$sql = "
		select ottwatchid 
		from geo_zoning 
		where 
			st_crosses(shape, (select shape from geo_property where ottwatchid = {$prop['ottwatchid']}) )
	";
	print "-";
	$zones = getDatabase()->all($sql);
	if (count($zones) > 0) {
		# delete the place holder
		getDatabase()->execute(" delete from geo_property_zoning where zoning_id = 0 and property_id = {$prop['ottwatchid']} ");
	}
	foreach ($zones as $zone) {
		print "!";
		getDatabase()->execute(" insert into geo_property_zoning (property_id,zoning_id) values ({$prop['ottwatchid']},{$zone['ottwatchid']}) ");
	}

	print "s";
	usleep(300000);
	print " ";
}

?>
