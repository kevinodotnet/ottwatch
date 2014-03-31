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

$url = "http://ottawa.ca/en/city-hall/accountability-and-transparency/public-meetings-and-notices/notices";
$html = file_get_contents($url);
$html = ConsultationController::getCityContent($html,'');
$md5 = md5($html);
$prevMD5 = getvar('public-meetings-and-notices.md5');

if ($md5 == $prevMD5) {
  # no change, no do anything
  return;
}

# save for delta research later
file_put_contents(OttWatchConfig::FILE_DIR.'/public_notices_'.time().'_'.$md5.'.html',$html);
setvar('public-meetings-and-notices.md5',$md5);

# help a brother out
print "\n\n";
print "diff ".OttWatchConfig::FILE_DIR.'/public_notices*'.$prevMD5.'* '.OttWatchConfig::FILE_DIR.'/public_notices*'.$md5."*\n";
print "\n\n";

# tweet that public notices page has updated
$tweet = tweet_txt_and_url("Public Notices & Meetings updated",$url);
tweet($tweet);

?>
