<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

while (true) {

	# next geo_property that is not in geo_property_zoning
	$sql = "
		select 
			p.ottwatchid
		from geo_property p
			left join geo_property_zoning pz on pz.property_id = p.ottwatchid
		where pz.property_id is null	
		limit 1
	";
	$prop = getDatabase()->one($sql);

	# take it out of the work loop
	getDatabase()->execute(" insert into geo_property_zoning (property_id) values ({$prop['ottwatchid']}) ");

	# fine zoning(s) that apply to that property
	$sql = "
		select ottwatchid 
		from geo_zoning 
		where 
			st_crosses(shape, (select shape from geo_property where ottwatchid = {$prop['ottwatchid']}) )
	";
	$zones = getDatabase()->all($sql);
	if (count($zones) > 0) {
		# delete the place holder
		getDatabase()->execute(" delete from geo_property_zoning where zoning_id = 0 and property_id = {$prop['ottwatchid']} ");
	}
	foreach ($zones as $zone) {
		getDatabase()->execute(" insert into geo_property_zoning (property_id,zoning_id) values ({$prop['ottwatchid']},{$zone['ottwatchid']}) ");
	}

	sleep(2);
}

?>
