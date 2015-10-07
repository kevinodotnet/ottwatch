<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

$table = $argv[1];

# find lat/lon of all intersections based on road segment data

$rows = getDatabase()->all("
	select 
		s1.ROAD_NAME_FULL s1_name,
		s1.RD_SEGMENT_ID s1_segment,
		s1.subclass s1_subclass,
		s2.ROAD_NAME_FULL s2_name,
		s2.RD_SEGMENT_ID s2_segment,
		s2.subclass s2_subclass,
		s2.road_name_id,
		st_distance(startpoint(s1.shape),startpoint(s2.shape)) d1,
		st_distance(startpoint(s1.shape),endpoint(s2.shape)) d2,
		st_distance(endpoint(s1.shape),startpoint(s2.shape)) d3,
		st_distance(endpoint(s1.shape),endpoint(s2.shape)) d4,
		astext(startpoint(s1.shape)) startpoint,
		astext(endpoint(s1.shape)) endpoint
	from $table s1
		join $table s2 on 
			(
			s2.road_name_id = s1.from_rd_id 
			or s2.road_name_id = s1.to_rd_id
			)
			and s2.subclass in ('COLLECTOR','ARTERIAL','MAJCOLLECTOR')
	where 
		s1.rd_segment_id = '___2STKC';
");
			# and s1.road_name_full <= s2.road_name_full
			/*

		s1.subclass in ('COLLECTOR','ARTERIAL','MAJCOLLECTOR')

		and 
		least(
			st_distance(startpoint(s1.shape),startpoint(s2.shape)),
			st_distance(startpoint(s1.shape),endpoint(s2.shape)),
			st_distance(endpoint(s1.shape),startpoint(s2.shape)),
			st_distance(endpoint(s1.shape),endpoint(s2.shape))
		) < 100
		*/

foreach ($rows as $r) {
	# use the four 'distance' columns to determine the intersection point
	pr($r);
	if ($r['d1'] < 10 || $r['d2'] < 10) {
		$point = $r['startpoint'];
	} else {
		$point = $r['endpoint'];
	}
	print "\n -- $point --\n";
	$mercator = getLatLonFromPoint($point);
	$latlon = mercatorToLatLon($mercator['lon'],$mercator['lat']);
	pr($latlon);

	print implode("\t",array(
		"OUTPUT",
		$r['s1_name'],
		$r['s2_name'],
		"{$latlon['lat']} {$latlon['lon']}",
		$r['s1_subclass'],
		$r['s2_subclass'],
		"\n"));

}


