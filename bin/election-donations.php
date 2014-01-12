<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

$total = 0;
$matched = 0;

	$rows = getDatabase()->all(" select * from candidate_donation where location is null ");
	$rows = getDatabase()->all(" select * from candidate_donation where id = 3547 ");
	foreach ($rows as $row) {
		$total ++;
		if (setDonorLocation($row)) {
			$matched ++;
		}
		print "($matched/$total/".count($rows).") {$row['id']} {$row['address']} - {$row['postal']} - {$row['name']} - {$row['amount']}\n";
	}

function setDonorLocation($row) {
	
	$debug = 0;

	$postal = $row['postal'];
	$addr = strtoupper($row['address']);

	if ($debug) { print ">>> $addr >>> $postal <<<\n"; }

	# group by because some addresses have multiple property parcels.
	$sql = "
		select 
			min(ottwatchid) ottwatchid,ADDRESS_NUMBER, ROAD_NAME , POSTAL_CODE
		from geo_property 
		where 
			postal_code = '$postal' 
		group by ROAD_NAME, ADDRESS_NUMBER, POSTAL_CODE
		order by ROAD_NAME, ADDRESS_NUMBER, POSTAL_CODE
	";
	$props = getDatabase()->all($sql);
	$prop_matches = filterPropertyMatches($row,$props);
	if (count($prop_matches) == 1) {
		# best case yo!
		$sql = " update candidate_donation set location = ( select centroid(shape) from geo_property where ottwatchid = {$prop_matches[0]['ottwatchid']} ) where id = {$row['id']} ";
		print "$sql\n";
		exit;
		getDatabase()->execute($sql);
		return true;
	} else {
		if ($debug) { print "NO JOY: ".count($prop_matches)." matches\n"; }
	}

	return false;
}

function filterPropertyMatches($row,$props) {
	$debug = 0;
	$postal = $row['postal'];
	$addr = strtoupper($row['address']);
	$prop_matches = array();
	foreach ($props as $p) {
		if ($debug) { pr($p); }
		$p['ROAD_NAME'] = trim($p['ROAD_NAME']);
		if ($debug) { print " >>> (match: $postal) >>> {$p['ADDRESS_NUMBER']} >>> {$p['ROAD_NAME']} >>> {$p['POSTAL_CODE']} >>> "; }
		if (preg_match("/.*{$p['ADDRESS_NUMBER']}.*/",$addr)) {
			if ($debug) { print "ADDRESS_NUMBER_MATCH "; }
			# address of property is somewhere in donor address!
			if (preg_match("/.*{$p['ROAD_NAME']}.*/",$addr)) {
				if ($debug) { print " ROAD_NAME_MATCH "; }
				# name of road is in there too! best case for a match!
				$prop_matches[] = $p;
			}
		}
		if ($debug) { print "\n"; }
	}
	return $prop_matches;
}

?>
