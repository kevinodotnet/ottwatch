<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

if ($argv[1] == 'readAgenda') {
  $date = $argv[2];
  $panel = $argv[3];
  DevelopmentAppController::scrapeCommitteeOfAdjustment($date,$panel);
  return;
}

