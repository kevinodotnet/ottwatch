<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');
require_once('twitteroauth.php');

if (count($argv) > 1) {

  if ($argv[1] == 'scanOpenForUpdates') {
		Ott311Controller::scanOpenForUpdates();
		return;
	}

  if ($argv[1] == 'latest') {
		Ott311Controller::scanLatest();
		return;
	}

  if ($argv[1] == 'scanOld') {
		Ott311Controller::scanOld();
		return;
	}

  if ($argv[1] == 'scan') {
		Ott311Controller::scan($argv[2]);
		return;
	}

  if ($argv[1] == 'getTwitterMentions') {
		Ott311Controller::getTwitterMentions();
		return;
	}

  if ($argv[1] == 'url') {
		$url = $argv[2];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		$headers = array( 'api_key: '.OttWatchConfig::OTTAPI_KEY);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec ($ch);

		print "$json";

		#$data = json_decode($json);
		#pr($data);
		#print "\n";
		return;

	}
  return;
}

