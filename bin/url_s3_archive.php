<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

if (count($argv) > 1) {

  if ($argv[1] == 'urlScan') {
		urlScan();
		return;
	}

} 

print "Bad argv\n";
return;

/*
mysql> desc md5hist;
+---------+--------------+------+-----+-------------------+----------------+
| Field   | Type         | Null | Key | Default           | Extra          |
+---------+--------------+------+-----+-------------------+----------------+
| id      | mediumint(9) | NO   | PRI | NULL              | auto_increment |
| curmd5  | varchar(50)  | YES  |     | NULL              |                |
| prevmd5 | varchar(50)  | YES  |     | NULL              |                |
| created | datetime     | YES  |     | CURRENT_TIMESTAMP |                |
| url     | varchar(512) | YES  |     | NULL              |                |
| s3url   | varchar(512) | YES  |     | NULL              |                |
+---------+--------------+------+-----+-------------------+----------------+
6 rows in set (0.00 sec)
*/

function urlConsume($url) {


	$urlmd5 = md5($url);
	$tmpfile = OttWatchConfig::TMP."/url_consume_$urlmd5";

	print "consume: '$url'\n";
	print "tmpfile: $tmpfile\n";

	# download the file to a tmp location, calculate md5 contents of file
	file_put_contents($tmpfile, fopen($url, 'rb'));
	$filemd5 = md5_file($tmpfile);
	print "filemd5: $filemd5\n";

	$s3Url = "s3://s3.ottwatch.ca/urlcache/$urlmd5";

	print "s3Url: $s3Url\n";

	return;

	$mr = getDatabase()->all(" select * from md5hist where url = :href order by created desc limit 1 ",array('href'=>$url));
	if (!isset($mr['id'])) {

		`aws s3 cp --acl public-read '$tmpfile' $s3Url`;



		# URL has never been downloaded/cached
		print "new file\n";
		db_insert("md5hist",array(
			'curmd5'=>$filemd5,
			'prevmd5'=>'',
			'url'=>$url
		));
	} 

	print "existing!\n";

	db_insert("md5hist",array('curmd5'=>$md5,'prevmd5'=>$prevMD5));
	file_put_contents(OttWatchConfig::FILE_DIR."/consultationmd5/".$md5,$html);

	pr($mr);
}


function urlScan() {

	$rows = getDatabase()->all(" select * from devappfile f order by created desc limit 10 ");
	foreach ($rows as $r) {
		pr($r);
		urlConsume($r['href']);
		break;
	}

}

function checkUrl ($url) {


	print "$url\n";


}

