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

if ($action == 'intersection') {
	try {
	$intersection = $argv[2];
	$intersection = explode(' @ ',$argv[2]);
	if (count($intersection) != 2) {
		print "bad intersection\n";
		return;
	}

	$street1 = $intersection[0];
	$street2 = $intersection[1];
	$street1 = preg_replace("/'/","''",$street1);
	$street2 = preg_replace("/'/","''",$street2);

	$sql = "
select 
    case 
        when st_distance(startpoint(s1.shape),startpoint(s2.shape)) < 1 then astext(startpoint(s1.shape))
        when st_distance(startpoint(s1.shape),endpoint(s2.shape)) < 1 then astext(startpoint(s1.shape))
        when st_distance(endpoint(s1.shape),startpoint(s2.shape)) < 1 then astext(endpoint(s1.shape))
        when st_distance(endpoint(s1.shape),endpoint(s2.shape)) < 1 then astext(endpoint(s1.shape))
        else null end as POINT,
    st_distance(startpoint(s1.shape),startpoint(s2.shape)) d1,
    st_distance(startpoint(s1.shape),endpoint(s2.shape)) d2,
    astext(startpoint(s1.shape)) s1,
    st_distance(endpoint(s1.shape),startpoint(s2.shape)) d3,
    st_distance(endpoint(s1.shape),endpoint(s2.shape)) d4,
    astext(endpoint(s1.shape)) s2,
    s1.ottwatchid,
    s1.ROAD_NAME_FULL,
    s2.ottwatchid,
    s2.ROAD_NAME_FULL 
from geo_street_names_20160217 s1 
    join geo_street_names_20160217 s2 on s1.ROAD_NAME_FULL like '$street1%' and s2.ROAD_NAME_FULL like '$street2%' 
where
    st_distance(startpoint(s1.shape),startpoint(s2.shape)) < 1
    or st_distance(startpoint(s1.shape),endpoint(s2.shape)) < 1
    or st_distance(endpoint(s1.shape),startpoint(s2.shape)) < 1
    or st_distance(endpoint(s1.shape),endpoint(s2.shape)) < 1
limit 1
;
	";

	$row = getDatabase()->one($sql);
	$latlon = getLatLonFromPoint($row['POINT']);
	$latlon = mercatorToLatLon($latlon['lon'],$latlon['lat']);
	#pr($latlon);
	print "{$latlon['lat']}\t{$latlon['lon']}\n";
	#pr($row);
	      } catch (Exception $e) {
					print "exception\n";
				}
}

