<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');
require_once('twitteroauth.php');

if ($argv[1] == 'hardScan') {
	# files get added/changed in development applications from time-to-time
	# go back 2 months and hard scrape everyone
	$rows = getDatabase()->all(" select appid from devapp where updated >= DATE_SUB(NOW(), INTERVAL 2 month) order by updated limit 1 ");
	foreach ($rows as $r) {
		DevelopmentAppController::injestApplication($r['appid'],'notweets');
	}
	return;
}

$appid = $argv[1];
DevelopmentAppController::injestApplication($appid,'notweets');

?>
