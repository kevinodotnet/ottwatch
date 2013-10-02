<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');
require_once('twitteroauth.php');

if (count($argv) > 1) {
  if ($argv[1] == 'resetVideo') {
    $id = $argv[2];
    getDatabase()->execute(" update meeting set youtube = null, youtubestart = null, youtubestate = null, youtubeset = null where meetid = :id ",array('id'=>$id));
    exit;
	}
  if ($argv[1] == 'setVideoStart') {
    $id = $argv[2];
    $start = $argv[3];
    $row = getDatabase()->one(" select * from meeting where meetid = :id ",array('id'=>$id));
    if (!isset($row['id'])) {
      print "ERROR: meeting.id=$id not found\n";
      exit;
    }
    # convert from m:s to s
    $matches = array();
    if (!preg_match("/(\d+):(\d+)/",$start,$matches)) {
      print "ERROR: '$start' is malformed. Should be '<minutes>:<seconds>'\n";
      exit;
    }
    $seconds = $matches[1] * 60 + $matches[2];
    getDatabase()->execute(" update meeting set youtubestart = :start where meetid = :id ",array('id'=>$id,'start'=>$seconds));
    exit;
  }
  if ($argv[1] == 'getVideos') {

		# look for videos that have been uploaded and are now done processing (ready to tweet!)
		$rows = getDatabase()->all(" 
			select * 
			from meeting 
			where 
				youtube like 'http%'
				and (youtubestate is null or youtubestate != 'ready')
		");
		foreach ($rows as $r) {
	    $id = $r['youtube'];
	    $id = preg_replace("/.*\?v=/","",$id);

			$user_email = OttWatchConfig::YOUTUBE_USER;
			$user_passwd = OttWatchConfig::YOUTUBE_PASS;
			$cmd = " PYTHONPATH=$dirname/../lib/gdata/src python ";
			$cmd .= " $dirname/../lib/youtube-upload/youtube_upload/youtube_upload.py ";
			$cmd .= " --email=$user_email --password=$user_passwd ";
			$cmd .= " --check-status=$id ";

			$state = `$cmd`;
			$state = trim($state);

			if ($state == 'ready') {
				print "\n\n---------------------------------------------\n";
				print "READY TO TWEET\n";
				print "{$r['youtube']} transitioning from state '{$r['youtubestate']}' to '$state'\n";
				pr($r);
				print "---------------------------------------------\n\n\n";
			}

			getDatabase()->execute(" update meeting set youtubestate = :state where id = :id ",array('id'=>$r['id'],'state'=>$state));

		}
		# Look for new meeting videos on recent meetings.
		$rows = getDatabase()->all(" 
			select * 
			from meeting 
			where 
				starttime < current_timestamp 
				and (youtube is null or youtube = '')
				and datediff(current_timestamp,starttime) < 60
			order by starttime desc
		");
		foreach ($rows as $m) {
			MeetingController::getVideo($m['id']);
		}
    return;
	}
  if ($argv[1] == 'getVideo') {
    $id = $argv[2];
    MeetingController::getVideo($id);
    return;
	}
  if ($argv[1] == 'hardScan') {
    MeetingController::hardScan();
	}
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

# keep track of all meetids in the RSS (to find deleted meetings)
$meetids = array();

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
	$meetids[] = $meetid;

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
    print "$category ($meetid) has changed guid\nhttp://ottwatch.ca/meetings/meeting/{$meetid}\n";
    # meeting has changed guid, so needs rescraping.
	  getDatabase()->execute(' 
      update meeting set 
        rssguid = :rssguid,
        starttime = :starttime,
        title = :title,
        category = :category,
        updated = CURRENT_TIMESTAMP
      where 
        meetid = :meetid ', array( ':rssguid' => $guid,':meetid' => $meetid, 'starttime' => $starttime, 'title'=>$title, 'category'=>$category ));
  } else {
    # meeting has never been seen before
    print "$category ($meetid) is new\nhttp://ottwatch.ca/meetings/meeting/{$meetid}\n";
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

# Delete meetings that (a) have not started yet and (b) are no longer in the RSS. It means
# they were cancelled, or were tests in 2050 and beyond.
$rows = getDatabase()->all(" select * from meeting where starttime > CURRENT_TIMESTAMP and meetid not in (".implode(',',$meetids).") order by starttime ");
foreach ($rows as $r) {
	print "\nDELETING LOST FUTURE MEETING:\n";
	pr($r);
	getDatabase()->execute(" delete from meeting where id = :id ",array('id'=>$r['id']));

}


?>
