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
	if (preg_match('/^objectid.*/i',$k)) { continue; }
	if (preg_match('/^shape$/i',$k)) { continue; }
	if (preg_match('/^shape_.*/i',$k)) { continue; }

	$distinct_max = 200;
	$rows = getDatabase()->all(" select * from ( select $k k, count(1) c from $table group by $k order by count(1) desc ) t limit $distinct_max ");
	if (count($rows) >= $distinct_max) {
		print "$k\tALL_DISTINCT\t".count($rows)."\n";
		continue;
	}
	print "$k\n"; # \t".count($rows)."\n";
	foreach ($rows as $r) {
		print "\t{$r['k']}\t{$r['c']}\n";
	}
}
