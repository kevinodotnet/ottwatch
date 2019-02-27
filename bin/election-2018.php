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
	printCandidates();
	print "enter candidate id: ";
	$candidateid = trim(fgets(STDIN));
	print "you chose '$candidateid'\n";
}


