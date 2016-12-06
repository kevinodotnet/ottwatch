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
#$tweet = tweet_txt_and_url("Public Notices & Meetings updated",$url);
$tweet = tweet_txt_and_url("Hi everyone just ignore this it's a test of a really long tweet because I'm playing with stuff and want to make sure I didn't break anything kthxbye",$url);
print "$tweet\n";
tweet($tweet);
return;

@$html = file_get_contents($url);
if (strlen($html) == 0) {
	return;
}
$html = ConsultationController::getCityContent($html,'');
$html = strip_tags($html);
$html = preg_replace('/\r/',"",$html);
$html = preg_replace('/\n/',"",$html);
$html = preg_replace('/\t/',"",$html);
$html = preg_replace('/ */',"",$html);
$html = preg_replace('/[^0-9a-zA-Z]*/',"",$html);
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

