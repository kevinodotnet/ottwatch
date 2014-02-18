<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');
require_once('twitteroauth.php');

$datetime1 = date_create('2014-10-27');
$datetime2 = date_create('now');
$diff = date_diff($datetime1,$datetime2);
$days = $diff->days;

if ($days > 0 && $days % 25 == 0) {
  tweet("$days days until election day #ottvote");
}

?>
