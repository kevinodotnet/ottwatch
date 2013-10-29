<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

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
      f.*
    from 
      opendatafile f
      join opendata d on d.id = f.dataid
    where
      f.updated > from_unixtime(:last)
    order by f.updated desc
    ",array('last'=>$last));

  if (count($rows) > 10) {
    # syndicate a round-up
    $message = count($rows) . " opendata files are updated: ";
    foreach ($rows as $r) {
      $message .= "{$r['name']} ({$r['format']}), ";
    }
    $message = preg_replace("/, $/","",$message);
    syndicate($message,'/opendata/');
    return;
  } 

  # syndicate each file since it's not too many
  foreach ($rows as $r) {
    $message = "#Opendata: {$r['name']} ({$r['format']}) updated";
    $url = $r['url'];
    syndicate($message,'/opendata/',$url);
  }
    
