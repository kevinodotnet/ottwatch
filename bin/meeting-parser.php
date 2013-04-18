<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');
require_once('twitteroauth.php');

if (count($argv) > 1) {
  if ($argv[1] == 'getMeeting') {
    $id = $argv[2];
    MeetingController::downloadAndParseMeeting($id);
  }
  if ($argv[1] == 'getFile') {
    $id = $argv[2];
    MeetingController::downloadAndParseFile($id);
  }
  return;
}

# get RSS of all meetings
$data = `wget -qO - http://app05.ottawa.ca/sirepub/rss/rss.aspx | head -1`; # file_put_contents("rss.rss",$data);
#$data = file_get_contents("rss.rss");

$xml = simplexml_load_string($data);
$items = $xml->xpath("//item");

# iterate through each meeting
foreach ($items as $i) {

	# [title] => ARAC - 2012-Jun-25 9:30 am
	# [link] => http://sire/sirepub/mtgviewer.aspx?meetid=2211&doctype=MINUTES
	# [description] => SimpleXMLElement Object ()
	# [category] => ARAC
	# [pubDate] => Thu, 01 Nov 2012 19:51:28 GMT
	# [guid] => 2211 ARAC 2012-Nov-01 3:51:28 PM

  $guid = $i->xpath("guid"); $guid = $guid[0];
  $title = $i->xpath("title"); $title = $title[0];
  $link = $i->xpath("link"); $link = $link[0];
  $category = $i->xpath("category"); $category = $category[0];

  # regex out some details and fix http refs
  $link = preg_replace("/.*sirepub/","http://app05.ottawa.ca/sirepub",$link);
  $meetid = $link;
  $meetid = preg_replace("/.*meetid=/","",$meetid);
  $meetid = preg_replace("/&.*/","",$meetid);
  # ARAC - 2012-Jun-25 9:30 am
  $starttime = $title;
  $starttime = preg_replace("/.* - 20/","20",$starttime);
  $starttime = preg_replace("/ AM$/"," am",$starttime);
  $starttime = preg_replace("/ PM$/"," am",$starttime);
  $starttime = preg_replace("/ am$/","am",$starttime);
  $starttime = preg_replace("/ pm$/","pm",$starttime);
  $starttime = strftime("%Y-%m-%d %H:%M:%S",strtotime($starttime));

  # is this guid in the database already
  $mdb = getDatabase()->one('select id from meeting where rssguid = :rssguid ', array(':rssguid' => $guid));
  if ($mdb['id']) {
    # meeting has already been parsed
    continue;
  }
  
  $mdb = getDatabase()->one('select id,rssguid from meeting where meetid = :meetid ', array(':meetid' => $meetid));
  $meetingid = $mdb['id'];
  if ($mdb['id']) {
    print "$category ($meetid) has changed guid\n";
    # meeting has changed guid, so needs rescraping.
	  getDatabase()->execute(' 
      update meeting set 
        rssguid = :rssguid,
        updated = CURRENT_TIMESTAMP
      where 
        meetid = :meetid ', array(
      ':rssguid' => $guid,':meetid' => $meetid
    ));
  } else {
    # meeting has never been seen before
    print "$category ($meetid) is new\n";
	  $meetingid = getDatabase()->execute('
			insert into meeting (rssguid,meetid,title,category,starttime,created,updated) 
			values (:rssguid,:meetid,:title,:category,:starttime,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP); ', array(
	    'rssguid' => $guid,
	    'meetid' => $meetid,
	    'title' => $title,
	    'category' => $category,
	    'starttime' => $starttime,
	  ));
  }

	# import the items for the new meeting.
  MeetingController::downloadAndParseMeeting($meetingid);
}

?>
