<?php

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)."/../lib"));
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)."/../www"));
require_once('include.php');

if (@$argv[1] == 'geoOttawaImport') {
	array_shift($argv);
	array_shift($argv);
	$table = array_shift($argv);
	$files = $argv;
	OpenDataController::geoOttawaImport($table,$files);
	return;
}

OpenDataController::scanOpenData();

# TODO: move the below to a function

  $last = getvar('opendatasyndicate.last');
  if ($last == '') {
    # defaulting now NOW
    $last = time();
  }
  # update the touch time to NOW, even if we fail to tweet.
  setvar('opendatasyndicate.last',time());

  $rows = getDatabase()->all(" 
    select 
      d.title,
      f.name, f.format, f.url
    from 
      opendatafile f
      join opendata d on d.id = f.dataid
    order by f.updated desc
    where f.updated > from_unixtime(:last)
    ",array('last'=>$last));

  if (count($rows) > 5) {
    # syndicate a round-up
    $message = count($rows) . " #opendata files have been updated: ";
    syndicate($message,'/opendata/');
    return;
  } 

  # syndicate each file since it's not too many
  foreach ($rows as $r) {
		$d = json_decode($r['title']);
		if (isset($d->en)) {
	    $message = "#Opendata: {$d->en} - {$r['name']} ({$r['format']}) updated";
		} else {
	    $message = "#Opendata: {$r['title']} - {$r['name']} ({$r['format']}) updated";
		}
    if ($r['title'] == $r['name']) {
      $message = "#Opendata: {$r['name']} ({$r['format']}) updated";
    }
    $url = $r['url'];
    syndicate($message,'/opendata/',$url);
  }
    
