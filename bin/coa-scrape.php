<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

if ($argv[1] == 'scrapeCommitteeOfAdjustment') {
  $date = $argv[2];
  $panel = $argv[3];
  $file = $argv[4];
  DevelopmentAppController::scrapeCommitteeOfAdjustment($date,$panel,$file);
  return;
}

print "ERROR: bad ARGV\n";

