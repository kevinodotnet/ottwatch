<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

if ($argv[1] == 'scrapeCommitteeOfAdjustment') {
  $file = $argv[2];

	$matches = array();
	if (!preg_match('/.*coa-(\d\d\d\d-\d\d-\d\d)-panel(\d).pdf$/',$file,$matches)) {
		print "Invalid COA filename; could not parse date/panel number\n";
		exit;
	}
	$date = $matches[1];
	$panel = $matches[2];

  DevelopmentAppController::scrapeCommitteeOfAdjustment($date,$panel,$file);
  return;
}

print "ERROR: bad ARGV\n";

