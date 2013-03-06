<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

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
  $starttime = preg_replace("/ pm$/","am",$starttime);
  $starttime = strftime("%Y-%m-%d %H:%M:%S",strtotime($starttime));

  # is this guid in the database already
  $mdb = getDatabase()->one('select id from meeting where rssguid = :rssguid ', array(':rssguid' => $guid));
  if ($mdb['id']) {
    # continue;
    continue;
  }
  $mdb = getDatabase()->one('select id,rssguid from meeting where meetid = :meetid ', array(':meetid' => $meetid));
  if ($mdb['id']) {
	  getDatabase()->execute(' delete from meeting where meetid = :meetid ', array(':meetid' => $meetid));
  }

  print "$category :: $starttime :: $title\n";

  $meetingid = getDatabase()->execute('
		insert into meeting (rssguid,meetid,title,category,starttime,created,updated) 
		values (:rssguid,:meetid,:title,:category,:starttime,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP); ', array(
    'rssguid' => $guid,
    'meetid' => $meetid,
    'title' => $title,
    'category' => $category,
    'starttime' => $starttime,
  ));
	# import the items for the new meeting.
  MeetingController::downloadAndParseMeeting($meetingid);
}

function getFile ($meetid,$itemid,$fileid) {
	global $OTTVAR;
  if (!file_exists("$OTTVAR/meetings/$meetid/$itemid/$fileid.pdf")) {
		print "      downloading\n";
	  $url = "http://app05.ottawa.ca/sirepub/view.aspx?cabinet=published_meetings&fileid=$fileid";
	  $html = `wget -qO - '$url'`;
	  # GET:  <script>document.location = 'cache/2/urdcrgg4d4kra23egdhjlg20/468760225201302453793.PDF';</script>
	  # MAKE: http://app05.ottawa.ca/sirepub/cache/2/ekaza2ovgebjwdj3y2fegh0x/4688002252013024625547.PDF
	  $cache = $html;
	  $cache = preg_replace("/.*cache/","http://app05.ottawa.ca/sirepub/cache",$cache);
	  $cache = preg_replace("/'.*/","",$cache);
	  $pdf = `wget -qO - '$cache'`;
	  `mkdir -p $OTTVAR/meetings/$meetid/$itemid`;
	  file_put_contents("$OTTVAR/meetings/$meetid/$itemid/$fileid.pdf",$pdf);
	}
  if (!file_exists("$OTTVAR/meetings/$meetid/$itemid/$fileid.txt")) {
		print "      converting\n";
	  `pdftotext $OTTVAR/meetings/$meetid/$itemid/$fileid.pdf`;
	}
  $txt = file_get_contents("$OTTVAR/meetings/$meetid/$itemid/$fileid.txt");
	$lines = explode("\n",$txt);
	$paras = array();
	$para = "";
	foreach ($lines as $line) {
		$line = preg_replace("/\r/","",$line);
		$line = preg_replace("/\n/","",$line);
		print ">>$line<<\n";
		if (preg_match("/^\S*$/",$line)) {
			# trim
			$para = preg_replace("/^\s*/","",$para);
			$para = preg_replace("/\s*$/","",$para);
			if ($para != '') {
				$paras[] = $para;
			}
			$para = "";
			continue;
		}
		$para .= " ";
		$para .= $line;
	}
	foreach ($paras as $p) {
		print "##########################\n";
		print ">>$p<<\n";
	}
	exit;
}

?>
