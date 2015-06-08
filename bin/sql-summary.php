<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

$table = $argv[1];

$row = getDatabase()->one(" select * from $table limit 1 ");
foreach ($row as $k => $v) {
	if ($k == 'ottwatchid') { continue; }
	if ($k == 'shape') { continue; }
	if (preg_match('/shape/i',$k)) { continue; }
	if ($k == 'Shape') { continue; }
	if ($k == 'OBJECTID') { continue; }

	print "\n\n-----[ $k ]-----\n";

	$sql = " select $k,count(1) c from $table group by $k limit 50 ";
	$rows = getDatabase()->all($sql);
	foreach ($rows as $r) {
		$padding = 25-strlen($r[$k]);
		print "{$r[$k]}";
		for ($x = 0; $x < $padding; $x++) { print " "; }
		print "{$r['c']}\n";
	}

}

