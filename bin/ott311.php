<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

if (count($argv) > 1) {

  if ($argv[1] == 'scanOpenForUpdates') {
		Ott311Controller::scanOpenForUpdates();
		return;
	}

  if ($argv[1] == 'latest') {
		Ott311Controller::scanLatest();
		return;
	}

  if ($argv[1] == 'scan') {
		Ott311Controller::scan($argv[2]);
		return;
	}
  return;
}

