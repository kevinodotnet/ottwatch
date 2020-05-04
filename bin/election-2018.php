<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

function printCandidates() {
  $sql = " select id,ward,last from candidate where electionid = 5 order by ward, last ";
  $rows = getDatabase()->all($sql);
  foreach ($rows as $c) {
  	print join($c,"\t")."\n";
  }
}

if ($argv[1] == 'createReturn') {
	$file = $argv[2];

	$rows = getDatabase()->all(" select id from candidate_return where filename = '$file'");
	if (count($rows) > 0) {
		exit(0);
	}

	preg_match('/\/([^\/]*)\.pdf/',$file,$matches);
	$parts = split('_',$matches[1]);
	$race = array_pop($parts);
	$name_in = "('".join($parts,"','")."')";

	$sql = " select id,ward,last,first from candidate where electionid = 5 and last in $name_in and first in $name_in ";
	$rows = getDatabase()->all($sql);
	if (count($rows) == 1) {
		$candidateid = $rows[0]['id'];
	} else if (count($rows) == 0) {
		printCandidates();
		print "enter candidate id: ($file)\n>> ";
		$candidateid = trim(fgets(STDIN));
	} else {
		foreach ($rows as $c) {
			print join($c,"\t")."\n";
		}
	}
	print "FOUND: candidateid: $candidateid\n";

	db_insert("candidate_return",array('candidateid'=>$candidateid,'filename'=>$file));
}


if ($argv[1] == 'foo') {
  $sql = " 
    select * 
    from candidate_donation 
    where 
      location is null 
      and city = 'Ottawa'
      and postal like 'K%'
      and created > '2019-01-01' 
      and id = 14778
    order by rand() limit 1 ";
  $row = getDatabase()->one($sql);
  $single_line = $row['address'].', '.$row['city'].', '.$row['prov'].', '.$row['postal'];
  pr($row);
  $url="https://city-of-ottawa-dev.apigee.net/gis/v1/findAddressCandidates?SingleLine=".$single_line."&outFields=User_fld&f=json";
  $json = json_decode(file_get_contents($url));
  print "\n";
  if (count($json->candidates) == 1) {
    pr($json->candidates);
    $loc = mercatorToLatLon(
      $json->candidates[0]->location->x,
      $json->candidates[0]->location->y
    );
    pr($loc);
  } else {
    print "too many/fiew:\n";
    pr($json);
  }
}

/*
<pre>Array
(
    [id] => 12722
    [returnid] => 1054
    [type] => 
    [name] => Fransham, Richard
    [address] => 788 de Salaberry St
    [city] => Ottawa
    [prov] => 
    [postal] => K1J6L1
    [amount] => 100.00
    [page] => 3
    [x] => 52
    [y] => 755
    [updated] => 2019-04-09 19:16:57
    [created] => 2019-03-31 12:30:30
    [location] => 
    [peopleid] => 
    [donorid] => 
    [donor_gender] => 
    [donation_date] => 2018-09-09
    [comment] => 
    [ward] => 
)
1</pre>
