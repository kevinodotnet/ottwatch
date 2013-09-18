<?php

/**
  Simple script to monitor just one Ottawa.ca page: public notices and meetings.

  If any content changes, tweet 'Updated'

  That's it.
 */

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');
require_once('twitteroauth.php');

$url = "http://ottawa.ca/en/collision-information-update";
$html = file_get_contents($url);
$html = ConsultationController::getCityContent($html);
$md5 = md5($html);

$prevMD5 = getvar('collision-information.md5');

if ($md5 == $prevMD5) {
  # no change, no do anything
  return;
}

setvar('collision-information.md5',$md5);

# tweet that public notices page has updated

$tweet = tweet_txt_and_url("City has updated the 'Collision information page'",$url);
tweet($tweet);

?>
