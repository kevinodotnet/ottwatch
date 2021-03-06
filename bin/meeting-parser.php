<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../vendor");
require_once('include.php');
require_once('twitteroauth.php');
require_once('autoload.php');


if (count($argv) > 1) {

  if ($argv[1] == 'bylawGetReminder') {
		bylawGetReminder();
		return;
	}

  if ($argv[1] == 'findNonRssMeetings') {
		MeetingController::findNonRssMeetings();
		return;
	}
  if ($argv[1] == 'createMeeting') {
		array_shift($argv); #php
		array_shift($argv); #this php file
		$meetid = array_shift($argv);
		$starttime = array_shift($argv);
		$title = array_shift($argv);
		$category = array_shift($argv);

		$guid = $meetid;

		# 6999 2016-01-13 "Committee of Adjustment Panel 1 - 2016-01-13" COA1
		MeetingController::createOrUpdateMeeting($meetid,$guid,$starttime,$title,$category);
	  MeetingController::downloadAndParseMeeting($meetid);
		return;
	}

  if ($argv[1] == 'coaMeetingScrapeAndParse') {
		$items = DevelopmentAppController::apiScrapeCoaSireForItemIds();
		#file_put_contents("j.json",json_encode($items)); $items = json_decode(file_get_contents("j.json"));
		foreach ($items as $i) {
			$r = getDatabase()->one(" select count(1) c from item where itemid = {$i['itemid']} ");
			if ($r['c'] == 0) {
				$item = MeetingController::apiScrapeItem($i['itemid']);
				$meetid = $item['meetid'];
				// item not found, so the meeting for this COA does not exist, so go get it.
				$guid = $meetid;
				$starttime = $item['meetdate'];
				$title = 'Committee of Adjustment Panel '.$i['panel'].' - '.$i['date'];
				$category = 'COA'.$i['panel'];
				MeetingController::createOrUpdateMeeting($meetid,$guid,$starttime,$title,$category);
				MeetingController::downloadAndParseMeeting($meetid);
			} else {
				# item was found in database; assumed to be unchanged
				# print $i['itemid'] . " was found...\n";
			}
		}
  	DevelopmentAppController::coaAgendaToDevApp();
		exit;
	}

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
			select 
				m.*
			from meeting m
			where 
				youtube like 'http%'
				and (youtubestate is null or youtubestate != 'ready')
				and datediff(youtubeset,now()) <= -1
		");

		foreach ($rows as $r) {
	    $id = $r['youtube'];

			getDatabase()->execute(" update meeting set youtubestate = 'ready' where id = {$r['id']} ");
      $title = meeting_category_to_title($r['category']);
      $path = "/meetings/{$r['category']}/{$r['meetid']}";
      syndicate("Video: {$title} on {$r['starttime']}",$path,$r['youtube']);

			continue;

# 	    $id = preg_replace("/.*\?v=/","",$id);
# 			$user_email = OttWatchConfig::YOUTUBE_USER;
# 			$user_passwd = OttWatchConfig::YOUTUBE_PASS;
# 			$cmd = " PYTHONPATH=$dirname/../lib/gdata/src python ";
# 			$cmd .= " $dirname/../lib/youtube-upload/youtube_upload/youtube_upload.py ";
# 			$cmd .= " --email=$user_email --password=$user_passwd ";
# 			$cmd .= " --check-status=$id ";
# 
# 			$state = `$cmd`;
# 			$state = trim($state);
# 
# 			getDatabase()->execute(" update meeting set youtubestate = :state where id = :id ",array('id'=>$r['id'],'state'=>$state));
# 
# 			if ($state == 'ready') {
# 			}

		}

		# Look for new meeting videos on recent meetings.
		$rows = getDatabase()->all(" 
			select * 
			from meeting 
			where 
				starttime < current_timestamp 
				and (youtube is null or youtube = '')
				and datediff(current_timestamp,starttime) < 60
				and category not like 'COA%'
			order by starttime desc
		");
		foreach ($rows as $m) {
			MeetingController::getVideo($m['id']);
		}
    return;
	}
  if ($argv[1] == 'getVideoStart') {
		# skip two meetings that just aren't happy
		$sql = " 
			select id,meetid,youtubeset
			from 
				meeting 
			where 
				datediff(now(),youtubeset) < 30 
				and youtubestate = 'ready' 
				and (youtubestart is null or youtubestart = 0) 
				and id not in (943,935)
			order by id desc
		 ";
				// datediff(now(),youtubeset) < 30 
		$rows = getDatabase()->all("$sql");
		foreach ($rows as $r) {
			$id = $r['id'];
	    $start = MeetingController::getVideoStart($id);
			print "$id :: $start\n";
			if ($start > 0) {
				db_update('meeting',array('id'=>$id,'youtubestart'=>$start),'id');
				$r['new_start_value'] = $start;
				$r['UPDATED'] = 1;
  			pr($r);
			}
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
# keep track of all meetids in the RSS (to find deleted meetings)

# get RSS of all meetings
$data = `wget -qO - http://app05.ottawa.ca/sirepub/rss/rss.aspx | head -1`; # file_put_contents("rss.rss",$data);
#$data = file_get_contents("rss.rss");
$xml = simplexml_load_string($data);
if (!is_object($xml)) {
  # network bubble; ignore
  return;
}
$items = $xml->xpath("//item");
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

	#print "$category :: $title :: $link\n";
	MeetingController::createOrUpdateMeeting($meetid,$guid,$starttime,$title,$category);
  MeetingController::downloadAndParseMeeting($meetid);

}

# Delete meetings that (a) have not started yet and (b) are no longer in the RSS. It means
# they were cancelled, or were tests in 2050 and beyond.
$rows = getDatabase()->all(" 
	select * from meeting 
	where 
		starttime > CURRENT_TIMESTAMP 
		and category not in ('COA1','COA2','COA3') 
		and meetid not in (".implode(',',$meetids).") 
	order by starttime ");
foreach ($rows as $r) {
	if (preg_match('/manual-/',$r['rssguid'])) { continue; } # do not delete rss-maual finds
	print "\nDELETING LOST FUTURE MEETING\n";
	pr($r);
	getDatabase()->execute(" delete from meeting where id = :id ",array('id'=>$r['id']));
}

return;

function bylawGetReminder() {
	$rows = getDatabase()->all(" select meetid,left(starttime,10) starttime from meeting where category = 'City Council' and datediff(now(),starttime) = 3 limit 1 ");
	if (count($rows) > 0) {
		$r = $rows[0];
		$to = 'kevino@kevino.net';
		$subject = "Enacted bylaws request";
		$b = getDatabase()->one(" select bylawnum m from bylaw order by bn_year desc, bn_num desc limit 1 ");
		$body = "Hello,

Can you please send me the bylaws enacted during the City Council meetings, or under delegated authority, after {$b['m']}?

Many thanks.

Cheers,
Kevin.";

		sendEmail($to,$subject,$body);
		exit;
	}
}



