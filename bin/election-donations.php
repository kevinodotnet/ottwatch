<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

if (@$argv[1] == 'setinclude') {
	$code = file_get_contents('set.include');
	eval($code);
	return;
}

$total = 0;
$matched = 0;

#$rows = getDatabase()->all(" select * from candidate_donation where city = 'Ottawa' and location is null order by updated desc ");
$rows = getDatabase()->all(" select * from candidate_donation where returnid = 18 and location is null order by rand() limit 10  ");
foreach ($rows as $row) {
	$total ++;
	if (setDonorLocation($row)) {
		$matched ++;
	}
	print "PROGRESS ($matched/$total/".count($rows).")\n";
}

function setDonorLocation($row) {

	if ($row['city'] == '') { $row['city'] = 'Ottawa'; }
	if ($row['prov'] == '') { $row['prov'] = 'Ontario'; }

	print "--------------------------------------------------------------------------------------------\n";
	print "id: {$row['id']} ADDRESS: {$row['address']} CITY: {$row['city']} POSTAL: {$row['postal']} NAME: {$row['name']}\n";
	print "\n";
	print "    http://beta.ottwatch.ca/election/donation/{$row['id']}\n";
	print "\n";
	
	$debug = 0;
	$postal = $row['postal'];
	$addr = strtoupper($row['address']);

	# group by because some addresses have multiple property parcels.
	$sql = "
		select 
			min(ottwatchid) ottwatchid,ADDRESS_NUMBER, ROAD_NAME, POSTAL_CODE
		from geo_property 
		where 
			postal_code = '$postal' 
		group by ROAD_NAME, ADDRESS_NUMBER, POSTAL_CODE
		order by address_number, ROAD_NAME, ADDRESS_NUMBER, POSTAL_CODE
	";
	$props = getDatabase()->all($sql);
	$prop_matches = filterPropertyMatches($row,$props);

	if (count($prop_matches) == 0) {
		# no short cut based on postal, so fall back to checking permutations of address parts
		# against ADDRESS_NUMBER and ROAD_NAME. Bruteforce-sih, but workable.
		$regaddr = $addr;
		$regaddr = preg_replace('/,/',' ',$regaddr);
		$regaddr = preg_replace('/-/',' ',$regaddr);
		$regaddr = preg_replace('/#/',' ',$regaddr);
		$regaddr = preg_replace('/\./',' ',$regaddr);
		$regaddr = preg_replace('/  /',' ',$regaddr);
		$parts = explode(' ',$regaddr);
		foreach ($parts as $num) {
			if (!preg_match('/^\d+$/',$num)) { continue; }
			foreach ($parts as $road) {
				if ($num != $road) {
					$road = preg_replace("/'/","''",$road);
					$sql = "
						select 
							min(ottwatchid) ottwatchid,ADDRESS_NUMBER, ROAD_NAME , POSTAL_CODE
						from geo_property 
						where 
							ADDRESS_NUMBER = $num
							and ROAD_NAME = '$road'
						group by ROAD_NAME, ADDRESS_NUMBER, POSTAL_CODE
						order by address_number, ROAD_NAME, ADDRESS_NUMBER, POSTAL_CODE
					";
					$moreros = getDatabase()->all($sql);
					foreach ($moreros as $r) {
						$props[] = $r;
					}
				}
			}
		}
		$prop_matches = filterPropertyMatches($row,$props);
	}

	if (count($prop_matches) == 1) {
		# best case yo!
		setDonationLocation($row['id'],$prop_matches[0]['ottwatchid']);
		return true;
	} else {
		foreach ($prop_matches as $p) {
			print "  pickmatch: ottwatchid: {$p['ottwatchid']} num: {$p['ADDRESS_NUMBER']} roadname: {$p['ROAD_NAME']} postal: {$p['POSTAL_CODE']}\n";
			print "    setDonationLocation({$row['id']},{$p['ottwatchid']}); \n";
		}
	}

	return false;
}

function setDonationLocation($donationid,$propertyottwatchid) {
		$geo = getDatabase()->one(" select astext(centroid(shape)) centroid from geo_property where ottwatchid = $propertyottwatchid ");
		$mercator = getLatLonFromPoint($geo['centroid']);
		$latlon = mercatorToLatLon($mercator['lon'],$mercator['lat']);
		$lat = $latlon['lat'];
		$lon = $latlon['lon'];
		$pointval = "PointFromText('POINT($lon $lat)')";
		// $sql = " update candidate_donation set location = ( select centroid(shape) from geo_property where ottwatchid = {$prop_matches[0]['ottwatchid']} ) where id = {$row['id']} ";
		$sql = " update candidate_donation set location = $pointval where id = $donationid ";
		getDatabase()->execute($sql);
}

function filterPropertyMatches($row,$props) {
	$debug = 1;
	$postal = $row['postal'];
	$addr = strtoupper($row['address']);
	$prop_matches = array();
	foreach ($props as $p) {
		$p['ROAD_NAME'] = trim($p['ROAD_NAME']);
		if ($debug) { print " checking: num: {$p['ADDRESS_NUMBER']} roadname: {$p['ROAD_NAME']} postal: {$p['POSTAL_CODE']} >>> "; }
		if (preg_match("/.*{$p['ADDRESS_NUMBER']}.*/",$addr)) {
			if (preg_match("/[0-9]{$p['ADDRESS_NUMBER']}/",$addr)) { if ($debug) { print "\n"; } continue; } //
			if (preg_match("/{$p['ADDRESS_NUMBER']}[0-9]/",$addr)) { if ($debug) { print "\n"; } continue; } //
			if ($debug) { print "addrMatch "; }
			# address of property is somewhere in donor address!
			if (preg_match("/.*{$p['ROAD_NAME']}.*/",$addr)) {
				if ($debug) { print " roadNameMatch "; }
				# name of road is in there too! best case for a match!
				$prop_matches[] = $p;
			}
		}
		if ($debug) { print "\n"; }
	}
	return $prop_matches;
}

?>
