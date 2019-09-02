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


