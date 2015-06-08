<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

$url = $argv[1];
$table = $argv[2];
$key = $argv[3];
#$url = "http://maps.ottawa.ca/arcgis/rest/services/Property_Parcels/MapServer/2";

scrapeLayer($url,$table,$key);

# addLatLonShape($table);

function addLatLonShape($table) {

	$sql = " alter table $table add shapeLatLon geometry not null ";
	# getDatabase()->execute($sql);

	$sql = " select id,s,left(s,3) l from ( select ottwatchid id,astext(shape) s from $table where astext(shapeLatLon) is null ) t limit 100 ";
	# $sql = " select id,s,left(s,3) l from ( select ottwatchid id,astext(shape) s from $table where road_name = 'WESTHAVEN' ) t ";
	$rows = array(1,2,3);
	while (count($rows) > 0) {
		print "\nselecting... ";
		$rows = getDatabase()->all($sql);
		print "count: ".count($rows);
		foreach ($rows as $r) {
			$s = $r['s'];
			if ($r['l'] == 'POL') {
				# LINESTRING(-8434311.4633 5664761.0459,-8434280.3563 5664749.374,-8434246.5154 5664726.2891,-8434167.5739 5664670.0518)
				$s = str_replace('POLYGON((','',$s);
				$s = str_replace('))','',$s);
				$newpoints = array();
				foreach (explode(',',$s) as $p) {
					$mm = explode(' ',$p);
					$ll = mercatorToLatLon($mm[0],$mm[1]);
					$newpoints[] = $ll['lon'].' '.$ll['lat'];
				}
				$shapeValue = " GeomFromText(' POLYGON(( ";
				$shapeValue .= implode(',',$newpoints);
				$shapeValue .= " )) ') ";
	
				print ' ';
				print $r['id'];
				getDatabase()->execute(" update $table set shapeLatLon = $shapeValue where ottwatchid = ".$r['id']);
			} else if ($r['l'] == 'LIN') {
				# LINESTRING(-8434311.4633 5664761.0459,-8434280.3563 5664749.374,-8434246.5154 5664726.2891,-8434167.5739 5664670.0518)
				$s = str_replace('LINESTRING(','',$s);
				$s = str_replace(')','',$s);
				$newpoints = array();
				foreach (explode(',',$s) as $p) {
					$mm = explode(' ',$p);
					$ll = mercatorToLatLon($mm[0],$mm[1]);
					$newpoints[] = $ll['lon'].' '.$ll['lat'];
				}
				$shapeValue = " GeomFromText(' LINESTRING( ";
				$shapeValue .= implode(',',$newpoints);
				$shapeValue .= " ) ') ";
	
				print ' ';
				print $r['id'];
				getDatabase()->execute(" update $table set shapeLatLon = $shapeValue where ottwatchid = ".$r['id']);
			} else {
				print "ERROR";
				pr($r);
				exit;
			}
		}
	}

}

function c_file_get_contents($url) {
	$m = md5($url);
	$f = "cache_$m";
	print "$f :: $url\n";
	if (file_exists($f)) {
		return `gzip -cd $f`;
	}
	$d = file_get_contents($url);
	file_put_contents($f,gzencode($d));
	return $d;
}

function scrapeLayer($metaurl,$table,$key) {

	# URL EXAMPLE:
	# http://maps.ottawa.ca/arcgis/rest/services/Property_Parcels/MapServer/2

	# get metadata for the layer
	$meta = json_decode(c_file_get_contents("{$metaurl}?f=pjson"));

	# sql table name
	# $table = "{$prefix}_".strtolower(str_replace(' ','_',$meta->name));

	$createTable = " drop table if exists $table; \n";
	$createTable .= " create table $table ( \n";
	$createTable .= "   ottwatchid mediumint unsigned auto_increment, \n";

	$types = array();

	$allfields = array();
	$shapeField = '';

	# foreach ($meta->fields as $f) { print " select {$f->name},count(1) c from $table group by {$f->name} limit 50; \n"; } exit;

	foreach ($meta->fields as $f) {

		$allfields[] = $f->name;

		$k = preg_replace('/\./','_',$f->name);

		$createTable .= "   ";
		$createTable .= "`$k`";
		$createTable .= " ";

		if ($f->type == 'esriFieldTypeDouble') {
			#$createTable .= "decimal(10,10)";
			$createTable .= "float";
		} else if ($f->type == 'esriFieldTypeGeometry') {
			$shapeField = $f->name;
			$createTable .= "geometry";
		} else if ($f->type == 'esriFieldTypeDate') {
			$createTable .= "datetime";
		} else if ($f->type == 'esriFieldTypeSmallInteger') {
			$createTable .= "bigint";
		} else if ($f->type == 'esriFieldTypeInteger') {
			$createTable .= "bigint";
		} else if ($f->type == 'esriFieldTypeOID') {
			$createTable .= "bigint";
		} else if ($f->type == 'esriFieldTypeString') {
			$createTable .= "varchar({$f->length})";
		} else {
			print "ERROR: type\n";
			pr($f);
			exit;
		}

		# esriFieldTypeSmallInteger

		$createTable .= ",\n";

		#pr($f);
	}

	$createTable .= "   primary key (ottwatchid) \n";
	$createTable .= " ) ENGINE = MYISAM ; \n"; // for spacial indexes

	getDatabase()->execute($createTable);

	$maxid = -1;

	while (true) {

		print " query: $key > $maxid";

		$url = "{$metaurl}/query";
		$url .= "?where=".urlencode("$key > $maxid");
		$url .= "&outFields=".urlencode(implode(",",$allfields));
		$url .= "&returnGeometry=false";
#		$url .= "&orderByFields=$key";
		$url .= "&returnZ=false";
		$url .= "&returnM=false";
		$url .= "&returnDistinctValues=false";
		$url .= "&f=pjson";
		$data = json_decode(c_file_get_contents($url));

		print " count: ".count($data->features)."\n";

		if (count($data->features) == 0) {
			# done, no data
			break;
		}

		foreach ($data->features as $f) {

			$attr = (array) $f->attributes;

			foreach ($meta->fields as $fi) {
				if ($fi->type == 'esriFieldTypeDate') {
					$v = $attr[$fi->name];
					$datetime = date("Y-m-d H:i:s",($v/1000));
					$attr[$fi->name] = $datetime;
					# milliseconds epoch
				}
			}

			if ($attr[$key] > $maxid) {
				$maxid = $attr[$key];
			}

			try {
				$id = db_insert($table,$attr);
			} catch (Exception $e) {
				pr($attr);
				throw($e);
			}

			if (isset($f->geometry->x)) {
				getDatabase()->execute(" update $table set $shapeField = GeomFromText(' POINT( {$f->geometry->x} {$f->geometry->y} ) ') where ottwatchid = $id ");
			}

			if (isset($f->geometry->rings)) {
				$points = array();
				foreach ($f->geometry->rings[0] as $p) {
					$points[] = implode(' ',$p);
				}
				$shapeValue = " GeomFromText(' POLYGON(( ";
				$shapeValue .= implode(',',$points);
				$shapeValue .= " )) ') ";

				$update = " update $table set $shapeField = $shapeValue where ottwatchid = $id; \n";
				getDatabase()->execute($update);
			}

			if (isset($f->geometry->paths)) {

				$points = array();
				foreach ($f->geometry->paths[0] as $p) {
					$points[] = implode(' ',$p);
				}
				$shapeValue = " GeomFromText(' LINESTRING( ";
				$shapeValue .= implode(',',$points);
				$shapeValue .= " ) ') ";

				$update = " update $table set $shapeField = $shapeValue where ottwatchid = $id; \n";
				getDatabase()->execute($update);
			}

		}

		$sleep = 10;
		print " sleep: $sleep";
		sleep($sleep);

	}

}

