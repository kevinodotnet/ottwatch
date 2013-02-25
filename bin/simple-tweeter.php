<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
ob_start();

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');
require_once('twitteroauth.php');

# get RSS of all meetings
$data = `wget -qO - http://app05.ottawa.ca/sirepub/rss/rss.aspx | head -1`;
$xml = simplexml_load_string($data);
$items = $xml->xpath("//item");

foreach ($items as $i) {

  $category = $i->xpath("category"); $category = $category[0];
  $title = $i->xpath("title"); $title = $title[0];
  $pubDate = $i->xpath("pubDate"); $pubDate = $pubDate[0];
  $guid = $i->xpath("guid"); $guid = $guid[0];
  $link = $i->xpath("link"); $link = $link[0];
  $guidmd5 = md5($guid);

  $title = preg_replace("/ AM$/"," am",$title);
  $title = preg_replace("/ PM$/"," pm",$title);
  $title = preg_replace("/ am$/","am",$title);
  $title = preg_replace("/ pm$/","pm",$title);
  $meetingDate = explode(" - ",$title);
  $meetingDate = $meetingDate[1];

	# URL is not Internet ready from the RSS
  $link = preg_replace("/.*sirepub/","http://app05.ottawa.ca/sirepub",$link);
	# links to MINUTES and SUMMARY replaced with Agenda because sometimes
	# they are not available yet, though are referenced in RSS. Flip to 
	# agenda means link always works, and user figure itout after the fact
  $link = preg_replace("/doctype=MINUTES/","doctype=AGENDA",$link);
  $link = preg_replace("/doctype=SUMMARY/","doctype=AGENDA",$link);

  $tweet = "$category on $meetingDate is updated $link";

  if (file_exists("$OTTVAR/$guidmd5")) {
    # this meeting has been tweeted already
    continue;
  }

	print "Sending $tweet\n";

	file_put_contents("$OTTVAR/$guidmd5",$tweet);
	#tweet($tweet,1);
}

$output = ob_get_contents();
ob_end_clean();
$now = strftime("%Y%m%d_%H%M%S",time());
file_put_contents("$OTTVAR/ranat_meetings_$now",$output);
print "$output\n";

#    [title] => TRC - 2012-Dec-05 12:00 pm
#    [link] => http://sire/sirepub/mtgviewer.aspx?meetid=2283&doctype=MINUTES
#    [description] => SimpleXMLElement Object
#        (
#        )
#
#    [category] => TRC
#    [pubDate] => Wed, 06 Feb 2013 19:22:17 GMT
#    [guid] => 2283 TRC 2013-Feb-06 2:22:17 PM

?>
