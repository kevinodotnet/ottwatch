<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

$gtfs_dir = $argv[1];
$tableprefix = $argv[2];

import_gtfs($gtfs_dir,$tableprefix);

function import_gtfs($gtfs_dir,$tableprefix) {

	$d = opendir($gtfs_dir);
	while (($f = readdir($d)) !== false) {

/*
agency
calendar
calendar_dates
edges
routes
stop_times
stops
trips
*/


		$file = "$gtfs_dir/$f";
		if (!is_file($file)) { continue; }
		if (!preg_match('/\.txt/',$f)) { continue; }

		$gttable = preg_replace("/\.txt/","",$f);


		$table = $tableprefix . "_" . preg_replace("/\.txt/","",$f);

		$f = fopen($file,'r');
		$line = preg_replace('/\xEF\xBB\xBF/','',fgets($f));
		$head = str_getcsv($line);

		print " drop table if exists $table; \n ";
		print " create table $table ( \n ";
		foreach ($head as $h) {
			print "   $h varchar(64), \n";
		}
		print "   id mediumint unsigned auto_increment, \n";
		print "   primary key (id) \n";
		print " ) ; \n"; # engine = innodb; \n";

		$sql1 = " insert into $table (".implode(',',$head).") values ( ";

		$c = 0;
		while (($line = fgets($f)) !== false) {
			$csv = str_getcsv($line);

			$sql = $sql1;

			for ($x = 0; $x < count($head); $x++) {
				if ($x > 0) {
					$sql .= ',';
				}
				$sql .= "'";
				$sql .= str_replace("'","''",$csv[$x]);
				$sql .= "'";
			}
			$sql .= " ); \n ";
			print $sql;
			# if ($c++ > 10) { break; }
		}
		fclose($f);

	}
	closedir($d);

}

