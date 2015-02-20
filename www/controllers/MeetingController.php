<?php

# NOTES:
# VIDEO FILES are 'http://ca.sirecdn.net/SIRE/Ottawa/City%20Council/2472/393-m3u8-aapl.ism/QualityLevels(464000)/Fragments(Video=39600000000,format=m3u8-aapl)'

#define('DATE_ICAL', 'Ymd\THis\Z');
define('DATE_ICAL', 'Ymd\THis');

MeetingController::formatMotion("foo");

class MeetingController {

  static public function reportCloseVotes() {
    top();

    ?>
    <div class="row-fluid">
    <div class="span4">
    <h1>Close Votes Report</h1>
    </div>
    <div class="span8">
    <p class="lead">
    This report shows votes cast at council or at a committee meeting. It is ordered from "closest vote" first to "uncontroversial" last.
    Only votes cast in the 2014-2018 term are shown here.
    </p>
    </div>
    </div>
    <?php

    $sql = " select itemvoteid, ";

    $rows = getDatabase()->all(" select distinct(vote) v from itemvotecast ");
    foreach ($rows as $r) {
      $sql .= " sum(case when vote = '{$r['v']}' then 1 else 0 end)/count(1) {$r['v']}, ";
    }

    $sql .= "
        ((sum(case when vote = 'y' then 1 else 0 end)/count(1))
        - (sum(case when vote = 'n' then 1 else 0 end)/count(1))) abs,
    ";

    $sql .= " count(1) as count ";
    $sql .= " from itemvotecast ivc ";
		$sql .= "   join itemvote iv on iv.id = ivc.itemvoteid ";
		$sql .= "   join item i on i.id = iv.itemid ";
		$sql .= "   join meeting m on m.id = i.meetingid and m.starttime >= '2014-12-01' ";
    $sql .= " group by itemvoteid ";
    $sql .= " order by 
      abs(
        (sum(case when vote = 'y' then 1 else 0 end)/count(1))
        - (sum(case when vote = 'n' then 1 else 0 end)/count(1))
      )
    ";

    $rows = getDatabase()->all($sql);
    ?>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <tr>
    <th>Closeness</th>
    <th>Yes%</th>
    <th>No%</th>
    <th>Motion</th>
    <th>Meeting</th>
    </tr>
    <?php
    foreach ($rows as $r) {

			if ($r['y'] == 0 || $r['n'] == 0) { continue; } // consensus

      $itemvote = getDatabase()->one(" select * from itemvote where id = {$r['itemvoteid']}");
      $item = getDatabase()->one(" select * from item where id = {$itemvote['itemid']}");
      $meeting = getDatabase()->one(" select * from meeting where id = {$item['meetingid']}");
      // if ($meeting['category'] != 'City Council') { continue; }

      if ($r['abs'] > 0) {
        print "<tr class=\"success\">";
      } else {
        print "<tr class=\"error\">";
      }
      ?>

      <?php
      print "<td>"; print abs($r['abs']); print "</td>";
      print "<td>"; printf("%.2f%%", $r['y'] * 100); print "</td>"; 
      print "<td>"; printf("%.2f%%", $r['n'] * 100); print "</td>"; 
      print "<td>"; 
      print "<b><a href=\"/meetings/votes/{$itemvote['id']}\">".$item['title']."</a></b>";
      print "<br/>";
      print $itemvote['motion'];
      print "</td>";
      print "<td><nobr>"; 
      print meeting_category_to_title($meeting['category']);
      print "</nobr><br/>";
      print "<nobr>".substr($meeting['starttime'],0,10)."</nobr>";
      print "</td>";
      ?>
      </tr>
      <?php
    }
    ?>
    </table>
    <?php

    bottom();
  }

  static public function voteDisplay ($id) {

		$vote = getDatabase()->one(" select * from itemvote where id = :id ",array('id'=>$id));
		$i = getDatabase()->one(" select * from item where id = :id ",array('id'=>$vote['itemid']));
		$m = getDatabase()->one(" select * from meeting where id = :id ",array('id'=>$i['meetingid']));
		$text = self::formatMotion($vote['motion']);
    $category = meeting_category_to_title($m['category']);
    $casts = getDatabase()->all(" select * from itemvotecast where itemvoteid = :id order by itemvoteid,vote,id ",array('id'=>$vote['id']));

		top(substr($text,0,50));

    ?>

		<div class="row-fluid">
		<div class="span6">
		<p class="lead"><?php print $text; ?></p>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
		<tr>
		<td>
		<h5>Yes</h5>
    <?php
    foreach ($casts as $c) {
      if ($c['vote'] != 'y') { continue; }
      print "<a href=\"".OttWatchConfig::WWW."/meetings/votes/member/".urlencode($c['name'])."\">".$c['name']."</a><br/>";
    }
    ?>
		</td>
		<td>
		<h5>No</h5>
    <?php
    foreach ($casts as $c) {
      if ($c['vote'] != 'n') { continue; }
      print "<a href=\"".OttWatchConfig::WWW."/meetings/votes/member/".urlencode($c['name'])."\">".$c['name']."</a><br/>";
    }
    ?>
		</td>
		<td>
		<h5>Absent/Recused/Other</h5>
    <?php
    foreach ($casts as $c) {
      if ($c['vote'] == 'y' || $c['vote'] == 'n') { continue; }
      print "<a href=\"".OttWatchConfig::WWW."/meetings/votes/member/".urlencode($c['name'])."\">".$c['name']."</a><br/>";
    }
    ?>
		</td>
		</tr>
		</table>
		<?php disqus(); ?>
		</div>
		<div class="span6">
		<p>Vote recorded from <b><?php print $category; ?></b> on <?php print substr($m['starttime'],0,10); ?> regarding 
		agenda item <b><?php print $i['title']; ?></b>.</p>
		<p><a href="<?php print OttWatchConfig::WWW."/meetings/{$m['category']}/{$m['meetid']}"; ?>">Full meeting details</a>.</p>
		<?php
		if ($m['youtubestate'] == 'ready') {
			print "<center>\n";
	    print self::getYoutubeEmbedCode($m['youtube']);
			print "</center>\n";
		}
		?>
		</div>
		</div><!-- //row -->

		</div>
		</div><!-- /row -->

		<?php
		bottom();
	}

  static public function dump() {
    top();
    $rows = getDatabase()->all(" 
      select *
      from meeting 
      where datediff(now(),starttime) > -100
      order by starttime desc 
    ");
    foreach ($rows as $m) {
      $category = meeting_category_to_title($m['category']);

      print "<h3>$category : {$m['starttime']}</h3>\n";

      print "Agenda\n";
      $url_en = "http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype=AGENDA";
      $url_fr = "http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype=AGENDA2";
      print "<a href=\"$url_en\">English</a>\n";
      print "<a href=\"$url_fr\">French</a>\n";
      print "<br/>\n";

      print "Minutes\n";
      $url_en = "http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype=MINUTES";
      $url_fr = "http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype=MINUTES2";
      print "<a href=\"$url_en\">English</a>\n";
      print "<a href=\"$url_fr\">French</a>\n";
      print "<br/>\n";
      print "<br/>\n";

      $items = getDatabase()->all(" select * from item where meetingid = :meetingid ",array('meetingid'=>$m['id']));
      print "<b>Items (".count($items).")</b>";
      print "<ul>\n";
      foreach ($items as $i) {
        $item_url = "http://app05.ottawa.ca/sirepub/item.aspx?itemid={$i['itemid']}";
        print "<li>";
        print "<a href=\"$item_url\">{$i['title']}</a>\n";
        $files = getDatabase()->all(" select * from ifile where itemid = :itemid ",array('itemid'=>$i['id']));
        if (count($files) > 0) {
          print "<ul>\n";
          foreach ($files as $f) {
            $file_url = "http://app05.ottawa.ca/sirepub/view.aspx?cabinet=published_meetings&fileid={$f['fileid']}";
            print "<li>\n";
            print "<a href=\"$file_url\">{$f['title']}</a>\n";
            print "</li>\n";
          }
          print "</ul>\n";
        }

        print "</li>\n";
      }
      print "</ul>\n";
    }
    bottom();
  }

  static public function votesIndex() {
    top();
    ?>

    <div class="row-fluid">
    <div class="span6">
    Choose the name of a council or committee member to see their entire voting history.
    <p/>
    <ul>
    <?php
    $rows = getDatabase()->all(" select distinct(name) from itemvotecast order by name ");
    foreach ($rows as $c) {
      print "<li><a href=\"".OttWatchConfig::WWW."/meetings/votes/member/".urlencode($c['name'])."\">".$c['name']."</a></li>\n";
    }
    ?>
    </ul>
    </div>
    <div class="span6">
    <a href="/meetings/votes/report/closeVotes">Close Votes Report</a>
    </div>
    </div><!-- row -->
    <?php
    bottom();
  }

  static public function votesMember($name) {
    top($name . " Voting History");

		$category = $_GET['category'];

		$v = array('name'=>$name);
		$categoryWhere = '';
		if ($category != '') {
			$v['category'] = $category;
			$categoryWhere = ' and category = :category ';
		}

		$sql = "
      select 
        ivc.vote,
        iv.id voteid,
        iv.motion,
        i.title,
        m.title as meetingtitle,
        m.category,
        left(m.starttime,10) starttime,
        m.meetid,
        ivt.*
      from itemvotecast ivc 
        join itemvote iv on iv.id = ivc.itemvoteid
        join itemvotetab ivt on ivt.itemvoteid = ivc.itemvoteid
        join item i on i.id = iv.itemid
        join meeting m on m.id = i.meetingid
      where 
        ivc.name = :name
				$categoryWhere
      order by
        m.starttime desc,
        ivc.id
		";
    $votes = getDatabase()->all($sql,$v);

    $againstMajorityTotal = 0;
    $totalYesNo = 0;
    foreach ($votes as $v) {
      if ($v['vote'] == 'y' && $v['passed'] == 0) { $againstMajorityTotal ++; continue; }
      if ($v['vote'] == 'n' && $v['passed'] == 1) { $againstMajorityTotal ++; continue; }
      if ($v['vote'] == 'a') { continue; } // does not count against
      $totalYesNo++;
    }

    ?>
    <div class="row-fluid">
    <div class="span4">
    <h1><?php print $name; ?>: Voting History</h1>
    </div>
    <div class="span8">
    <p class="lead">
    <?php
    print "From <b>".$votes[count($votes)-1]['starttime']."</b> to <b>".$votes[0]['starttime']."</b>, out of ".$totalYesNo." votes of 'Yes' or 'No', $name voted against the majority $againstMajorityTotal times (".sprintf("%.2f",100*$againstMajorityTotal/$totalYesNo)."%)<br/>";
    print "</p>";
    print "<!-- \n AGAINST_REPORT,$name,$totalYesNo,$againstMajorityTotal,".sprintf("%.2f",100*$againstMajorityTotal/$totalYesNo)."% \n --> \n";
    if ($againstMajorityTotal > 0) {
      print "Jump to those votes with these links: ";
	    $index = 0;
	    foreach ($votes as $v) {
	      $keep = 0;
	      if ($v['vote'] == 'y' && $v['passed'] == 0) { $keep = 1; }
	      if ($v['vote'] == 'n' && $v['passed'] == 1) { $keep = 1; }
	      if ($keep == 0) { continue; }
	      print "<a href=\"#voteid{$v['voteid']}\">here</a> ";
	    }
    }
    ?>
    </div>
    </div>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <?php
    $prevmeetid = '';
    foreach ($votes as $v) {
      if ($prevmeetid != $v['meetid']) {
        ?>
        <tr>
        <td colspan="4" style="background: #f0f0f0;">
        <h5><?php print meeting_category_to_title($v['category']); ?></h5>
        <a href="<?php print OttWatchConfig::WWW."/meetings/{$v['category']}/{$v['meetid']}"; ?>"><?php print $v['starttime']; ?></a>
        </td>
        </tr>
	      <tr>
	      <th style="width: 10%;">Yes/No/Absent</th>
	      <th style="width: 10%;">Motion Passed</th>
	      <th style="width: 45%;">Motion</th>
	      <th style="width: 45%;">Meeting Item</th>
	      </tr>
        <?php
      }
      $prevmeetid = $v['meetid'];
      $againstMajority = 0;
      if ($v['vote'] == 'y' && $v['passed'] == 0) { $againstMajority = 1; }
      if ($v['vote'] == 'n' && $v['passed'] == 1) { $againstMajority = 1; }
      if ($v['vote'] == 'y') { $v['vote'] = 'Yes'; }
      if ($v['vote'] == 'n') { $v['vote'] = 'No'; }
      if ($v['vote'] == 'a') { $v['vote'] = 'Absent'; }
      if ($v['passed'] == '0') { $v['passed'] = 'No'; }
      if ($v['passed'] == '1') { $v['passed'] = 'Yes'; }

      if ($againstMajority) {
        print "<tr class=\"error\">";
      } else {
        print "<tr>";
      }
      ?>
      <td style="width: 10%;"><a name="voteid<?php print $v['voteid']; ?>"></a><?php print $v['vote']; ?></td>
      <td style="width: 10%;"><?php print $v['passed']; ?></td>
      <td style="width: 45%;">
			<?php print $v['motion']; ?>
			<a href="<?php print OttWatchConfig::WWW."/meetings/votes/{$v['voteid']}"; ?>"><i class="icon-share"></i> details</a>
			</td>
      <td style="width: 35%;"><?php print $v['title']; ?></td>
      </tr>
      <?php
    }

    ?>
    </table>
    <?php

    bottom();
  }

  static public function getYoutubeEmbedCode($url) {
    # convert from a URL to a youtbue watch page to the equivalent embed code.
    # URL looks like this: http://www.youtube.com/watch?v=zwCZQV-D4J4
    $id = $url;
    $id = preg_replace("/.*\?v=/","",$id);

    # reach back into the database for the youtubestart, if available
    $row = getDatabase()->one(" select * from meeting where youtube = :url ",array('url'=>$url));
    if (isset($row['id'])) {
      if (isset($row['youtubestart'])) {
        return '<iframe width="640" height="480" src="http://www.youtube.com/embed/'.$id.'?rel=0&start='.$row['youtubestart'].'" frameborder="0" allowfullscreen></iframe>';
      }
    }

    return '<iframe width="640" height="480" src="http://www.youtube.com/embed/'.$id.'?rel=0" frameborder="0" allowfullscreen></iframe>';
  }

  static public function getVideoStart($id) {
		global $dirname; # set by bin/XXXX.php scripts, UGLY, should probably be passed in as FAR or a DEFINE

		$debug = 0;

		$m = getDatabase()->one(" select * from meeting where id = :id ",array('id'=>$id));
		if (!$m['id']) {
			# can't get video for a meeting we don't know about
			return -1;
		}

		$meetid = $m['meetid'];

		# Get the full HTML for the meeting (all frames and details)
    $url = "http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$meetid}&doctype=AGENDA";
    $html = `wget -qO - '$url'`; // file_get_contents($url);

		if (false && $debug) {
		print "\n\n-----------------------------------------\n\n";
		print "$html\n";
		print "\n\n-----------------------------------------\n\n";
		}

    $tmp = preg_grep('/g_locationPrimary/',explode("\n",$html));
    if (count($tmp) == 0) {
			# print "NO video details found (g_locationPrimary)\n";
      // no video
			return -1;
    }

		# Extract just the URL from the HTML line that matched
    foreach ($tmp as $k => $v) { $tmp = $v; }
    $tmp = preg_replace('/.*http/','http',$tmp);
    $tmp = preg_replace('/".*/','',$tmp);
    $isplUrl = $tmp;
    $baseUrl = preg_replace('/\/[^\/]+$/','/',$isplUrl);

		# Extract the first REF from ISPL xml document
		# print "Loading video ISPL file: $isplUrl\n";
    $spl = `wget -qO - '$isplUrl'`;
		if ($spl == '') {
			# print "ISPL file not found or is not XML\n";
			return -1;
		}

		if ($debug) {
		print "\n\n-----------------------------------------\n\n";
		print "url: $isplUrl\n";
		print "spl: $spl\n";
		print "\n\n-----------------------------------------\n\n";
		}

    $xml = simplexml_load_string($spl);
    $start = $xml->xpath("//meta[@name='trim']/@start");
    if (count($start) > 0) {
      $start = '' . $start[0];
    } else {
      $start = 0;
    }
		return $start;
	}

  static public function getVideo($id) {
		global $dirname; # set by bin/XXXX.php scripts, UGLY, should probably be passed in as FAR or a DEFINE

		$debug = 0;

		$m = getDatabase()->one(" select * from meeting where id = :id ",array('id'=>$id));
		if (!$m['id']) {
			# can't get video for a meeting we don't know about
			return -1;
		}

		$meetid = $m['meetid'];

		# Get the full HTML for the meeting (all frames and details)
    $url = "http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$meetid}&doctype=AGENDA";
    $html = `wget -qO - '$url'`; // file_get_contents($url);

		if ($debug) {
		print "\n\n-----------------------------------------\n\n";
		print "$html\n";
		print "\n\n-----------------------------------------\n\n";
		}

    $tmp = preg_grep('/g_locationPrimary/',explode("\n",$html));
    if (count($tmp) == 0) {
			# print "NO video details found (g_locationPrimary)\n";
      // no video
			return -1;
    }

		# Extract just the URL from the HTML line that matched
    foreach ($tmp as $k => $v) { $tmp = $v; }
    $tmp = preg_replace('/.*http/','http',$tmp);
    $tmp = preg_replace('/".*/','',$tmp);
    $isplUrl = $tmp;
    $baseUrl = preg_replace('/\/[^\/]+$/','/',$isplUrl);

		# Extract the first REF from ISPL xml document
		# print "Loading video ISPL file: $isplUrl\n";
    $spl = `wget -qO - '$isplUrl'`;
		if ($spl == '') {
			# print "ISPL file not found or is not XML\n";
			return -1;
		}

		if ($debug) {
		print "\n\n-----------------------------------------\n\n";
		print "url: $isplUrl\n";
		print "spl: $spl\n";
		print "\n\n-----------------------------------------\n\n";
		}

    $xml = simplexml_load_string($spl);
    $start = $xml->xpath("//meta[@name='trim']/@start");
    if (count($start) > 0) {
      $start = '' . $start[0];
    } else {
      $start = 0;
    }

		$totalFrags = array();
    $refs = $xml->xpath('//ref/@src'); 
		# pr($refs);
		foreach ($refs as $ref) {
      $ref = ''.$ref[0]; //$ref = $ref['src']; $ref = $ref[0];

			# Skip to the seemlingly consisten 'bitrate specific' manifest file
	    # Manifest             'http://ca.sirecdn.net/SIRE/Ottawa/City Council/2472/393.ism/manifest'
	    # Example video chunk: 'http://ca.sirecdn.net/SIRE/Ottawa/City Council/2472/393-m3u8-aapl.ism/QualityLevels(4	4000)/Fragments(Video=39	00000000,format=m3u8-aapl)'
	    # ISM2                 'http://ca.sirecdn.net/SIRE/Ottawa/City Council/2472/393-m3u8-aapl.ism/QualityLevels(4	4000)'
	    $ism2 = $baseUrl . $ref;
	    $ism2 = preg_replace('/\.ism/','-m3u8-aapl.ism',$ism2);
	    $ism2 = preg_replace('/manifest/','',$ism2);
	    $ism2 .= 'QualityLevels(464000)';
	    $manifest = `wget -qO - '$ism2/manifest(format=m3u8-aapl)'`;
	
			if ($debug) {
			print "\n\n-----------------------------------------\n\n";
	    print "url: $ism2/manifest(format=m3u8-aapl)\n";
			print "manifest: $manifest\n";
			print "\n\n-----------------------------------------\n\n";
			}
	
	    $frags = preg_grep('/^Fragments/',explode("\n",$manifest));
			if (count($frags) > 0) {
		    foreach ($frags as $frag) {
		      $frag = preg_replace("/\n/","",$frag);
		      $frag = preg_replace("/\r/","",$frag);
		      $fragUrl = "$ism2/$frag";
					$totalFrags[] = $fragUrl;
		    }
			}
		}
		if (count($totalFrags) == 0) {
			if ($debug) { print "No fragments\n"; }
			return -1;
		}

		# ###############################################################
		# FROM here down the video is expected to be present and work, so
		# produce some debug output. SHould only be present when an actual
		# video is found/downloaded/uploaded.
		# ###############################################################

		print "FOUND video for {$m['category']} on {$m['starttime']} id: {$m['id']} meetid: {$m['meetid']}\n\n";

		# Ensure no other process starts in on this file.
		getDatabase()->execute(" update meeting set youtube = 'SAVING', youtubestate = 'SAVING' where id = :id ",array('id'=>$m['id']));

		# This manifest file has all the fragment and timeoffset details. One HTTP request per
		# Append each chunk to the overall video file
    $video_file = OttWatchConfig::TMP."/video_{$meetid}.mp2t";
		touch($video_file);
		unlink($video_file);

		print "Saving to $video_file ".count($totalFrags)." chunks\n";
    $chunk = 0;
		print "\n";
		foreach ($totalFrags as $fragUrl) {
			$chunk++;
			if ($debug) { 
				print "[$chunk/".count($totalFrags)."] $fragUrl\n"; 
			} else {
				if ($chunk % 100 == 0) { print " $chunk"; }
			}
	    $data = `wget -qO - '$fragUrl'`;
			# abort on empty data file chunks?
			if (strlen($data) == 0) {
				print "\nChunk $chunk had zero size; aborting\n";
				getDatabase()->execute(" update meeting set youtube = null, youtubestate = null where id = :id ",array('id'=>$m['id']));
				@unlink($video_file);
				return;
			}
			file_put_contents($video_file,$data,FILE_APPEND);
		}

		# ensure filesize is non-zero
		if (filesize($video_file) == 0) {
			# video not ready; try again some other day?
			print "\nFile size of $video_file is zeo... resetting for next time\n";
			getDatabase()->execute(" update meeting set youtube = null, youtubestate = null where id = :id ",array('id'=>$m['id']));
			unlink($video_file);
			return;
		}


		# Mark the video URL as 'uploading' so that we know we've attempted it once
		getDatabase()->execute(" update meeting set youtube = 'UPLOADING', youtubestate = 'UPLOADING' where id = :id ",array('id'=>$m['id']));

		# Use external Youtube-Upload python to push to Youtube
		$user_email = OttWatchConfig::YOUTUBE_USER;
		$user_passwd = OttWatchConfig::YOUTUBE_PASS;

		# video info
    $meetingDate = explode(" - ",$m['title']);
    $meetingDate = $meetingDate[1];
  	$link = OttWatchConfig::WWW."/meetings/meetid/".$m['meetid'];
    $title = "Ottawa ".meeting_category_to_title($m['category'])." - $meetingDate";
    $desc = meeting_category_to_title($m['category'])." on $meetingDate. Full details: $link";

		$cmd = " PYTHONPATH=$dirname/../lib/gdata/src python ";
		$cmd .= " $dirname/../lib/youtube-upload/youtube_upload/youtube_upload.py ";
		$cmd .= " --email=$user_email --password=$user_passwd ";
		$cmd .= " '--title=$title' '--description=$desc' ";
		$cmd .= " --category=News --keywords='ottwatch, Ottawa City Council' ";
		$cmd .= " $video_file ";

		if ($debug) {
		print "$cmd\n";
		}

		# print $cmd; return;
		$youtube_url = `$cmd`;
		$youtube_url = preg_replace("/\n/","",$youtube_url);
		$youtube_url = preg_replace("/\r/","",$youtube_url);
		# dont need this now
		unlink($video_file);

		if ($youtube_url == '') {
			# mark as ERROR so we don't keep trying over and over again on this video; something must be wrong.
			getDatabase()->execute(" update meeting set youtubeset = current_timestamp, youtube = 'ERROR', youtubestate = 'ERROR' where id = :id ",array('id'=>$m['id']));
		} else {
  		getDatabase()->execute(" update meeting set youtubestart = $start, youtubeset = current_timestamp, youtube = :url, youtubestate = 'UPLOADED' where id = :id ",array('id'=>$m['id'],'url'=>$youtube_url));
      print "\n";
      print "Uploaded: $youtube_url\n";
      print "\n";
    }

  }

  static public function formatMotion($motion) {
    # needs fixin
    return $motion;

    $motion = preg_replace("/^Motion To: /","",$motion); # useless preamble
    $motion = preg_replace("/WHEREAS/","<br/><b>WHEREAS</b>",$motion);
    $motion = preg_replace("/; BE IT RESOLVED/",";<br/><b>BE IT RESOLVED</b>",$motion);
    $motion = preg_replace("/THEREFORE BE IT RESOLVED/","<br/><b>THEREFORE BE IT RESOLVED</b>",$motion);
    $motion = preg_replace("/^<br\\/>/","",$motion); # strip opening BR that we might have added
    $motion = preg_replace("/<br\\/>/","<br/>\n",$motion); # strip opening BR that we might have added
    return $motion;
  }

  static public function calendarView () {
		top();
?>
<iframe 
	src="https://www.google.com/calendar/embed?height=600&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=cereh4hjdjfg99pmur7bkffcg9k83pnh%40import.calendar.google.com&amp;color=%235229A3&amp;ctz=America%2FMontreal" style=" border-width:0" width="100%" height="600" frameborder="0" scrolling="no"></iframe>
<?php
		bottom();
	}

  static public function calendar () {
	header('Content-type: text/calendar; charset=utf-8');
	header('Content-Disposition: inline; filename=ottwatch.ics');
	print "BEGIN:VCALENDAR\r\n";
	print "VERSION:2.0\r\n";
	print "PRODID:-//OttWatch//NONSGML OttWatch//EN\r\n";
  $rows = getDatabase()->all(" select meetid,category,(starttime + interval 4 hour) starttime,(starttime + interval 5 hour) endtime from meeting order by starttime desc ");
  foreach ($rows as $r) {
   	$link = OttWatchConfig::WWW."/meetings/meetid/".$r['meetid'];
    $title = meeting_category_to_title($r['category']);
    print "BEGIN:VEVENT\r\n";
    print "DTSTAMP:".date(DATE_ICAL, strtotime($r['starttime']))."\r\n";
    print "DTSTART:".date(DATE_ICAL, strtotime($r['starttime']))."\r\n";
    print "DTEND:".date(DATE_ICAL, strtotime($r['endtime']))."\r\n";
    print "UID:{$r['meetid']}@ottwatch.ca\r\n";
    print "DESCRIPTION:$link\r\n";
    print "URL:$link\r\n";
    print "SUMMARY:$title\r\n";
    print "END:VEVENT\r\n";
  }
		print "END:VCALENDAR\r\n";
    return;
  }

  # new/updated meeting tweeter
  static public function tweetNewMeetings() {

    $last = getvar('meetingtweet.last');
    if ($last == '') {
      # defaulting now NOW
      $last = time();
    }

    # update the touch time to NOW, even if we fail to tweet.
    setvar('meetingtweet.last',time());

    # find all meetings that have yet to happen, that have been updated
    # since last tweet meetings run.
    #
    # added "120" filter because sometimes Ottawa.ca puts in meetings that
    # are years away - probably for testing stuff.
    $rows = getDatabase()->all(" 
      select * 
      from meeting 
      where 
        updated >= from_unixtime(:last)
        and starttime >= CURRENT_TIMESTAMP
        and datediff(starttime,CURRENT_TIMESTAMP) < 120
      order by created 
      ",array( 'last'=>$last)); 

    # assemble a new list of tweets, key=url
    $tweets = array();
    $posts = array();
    foreach ($rows as $r) {

			# id] => 370
			# rssguid] => 2477 City Council 2013-Mar-22 4:34:47 PM
			# meetid] => 2477
			# starttime] => 2013-03-27 10:00:00
			# title] => City Council - 2013-Mar-27 10:00 am
			# category] => City Council
			# created] => 2013-03-24 13:25:33
			# updated] => 2013-03-24 13:25:55
			# contactName] => 
			# contactEmail] => 
			# contactPhone] => 
			# members] =>j 

      $title = $r['title'];
		  $title = preg_replace("/ AM$/"," am",$title);
		  $title = preg_replace("/ PM$/"," pm",$title);
		  $title = preg_replace("/ am$/","am",$title);
		  $title = preg_replace("/ pm$/","pm",$title);

      $meetingDate = explode(" - ",$title);
      $meetingDate = $meetingDate[1];

    	$path = "/meetings/meetid/".$r['meetid'];
    	$link = OttWatchConfig::WWW."/meetings/meetid/".$r['meetid'];

      if ($r['created'] == $r['updated']) {
        $tweet = "New Meeting: ".meeting_category_to_title($r['category'])." on $meetingDate";
      } else {
        $tweet = "Meeting: ".meeting_category_to_title($r['category'])." on $meetingDate is updated";
      }

      $tweets[$link] = $tweet;
      $posts[$path] = $tweet;
    }

    # keying by URL guarantees we don't double-tweet because of multiple new activities 
    # on the same lobbyfile
    foreach ($posts as $path => $text) {
      syndicate($text,$path);
    }
    #foreach ($tweets as $url => $text) {
    #  $tweet = tweet_txt_and_url($text,$url);
    #  # allow duplicates because subsequent tweets about the same file
    #  # will be the same, but spaced in time according to the lobbyist
    #  # activity dates
    #  tweet($tweet);
    #}
    
  }

  # trim file titles if they match the item title
  static public function trimFileTitle ($itemTitle,$fileTitle) {
    $o = $fileTitle;
    $a = explode(" ",$itemTitle);
    $b = explode(" ",$fileTitle);
    while (count($b)) {
      $a[0] = preg_replace("/[^a-zA-Z0-9]*/","",$a[0]);
      $b[0] = preg_replace("/[^a-zA-Z0-9]*/","",$b[0]);
      if ($a[0] == $b[0]) {
        array_shift($a);
        array_shift($b);
        continue;
      }
      if (preg_match("/^{$b[0]}/",$a[0])) {
        array_shift($b);
        continue;
      }
      if ($b[0] == '-') {
        array_shift($b);
        continue;
      }
      break;
    }
    $fileTitle = implode(" ",$b);
    return $fileTitle;
  }

  # for a given fileid, resolve the "cache" trick, then proxy download the real PDF
  static public function getFileCacheUrl ($fileid,$desc) {

		$file = getDatabase()->one(" 
			select
				f.id,
				f.created created,
				f.title filetitle,
				i.title itemtitle,
				m.meetid,
				m.starttime,
				m.category
			from 
				ifile f
				join item i on i.id = f.itemid
				join meeting m on m.id = i.meetingid
			where 
				f.fileid = :fileid
		",array('fileid'=>$fileid));

		if (!isset($file['id'])) {
			# the fileid is now dead, as happens from time to time.
			# give a best effort warning based on the DESC that we might now about
			top();
			?>
			<h1>This file is no longer available</h1>
			<p>
			Most likely, the "fileid" has simply changed to something else, as happens from time to time as
			city agendas are updated before and after a meeting.
			</p>
			<?php if ($desc != '') { ?>
			<p>
			This file description may help you locate the relevant meeting date / item, so you can lookup 
			that meeting in <a href="/meetings/">the list of all meetings</a>.<br/>
			</p>
			<p>
			<b>Description:</b> <?php print htmlentities($desc); ?>
			</p>
			<?php } ?>
			<?php
			bottom();
			return;
		}

		# the fileid exists

    $meetingtitle = meeting_category_to_title($file['category']);
		$dbdesc = "File: {$file['filetitle']} Item: {$file['itemtitle']} Meeting: {$meetingtitle} Date: {$file['starttime']}";
		$dbdesc = preg_replace('/[: -\.\(\)]/','_',$dbdesc);
		$dbdesc = preg_replace('/__/','_',$dbdesc);
		$dbdesc = preg_replace('/__/','_',$dbdesc);
		$dbdesc = preg_replace('/__/','_',$dbdesc);
		$dbdesc = preg_replace('/__/','_',$dbdesc);

		if ($desc != $dbdesc) {
			# coming in with mis-matched description; send them to the correc tone, which will be another HTTP GET but
			# the next hit will get past this IF.
			header("Location: /meetings/file/{$fileid}/{$dbdesc}");
			return;
		}

    # get the data
    $curl = 'http://app05.ottawa.ca/sirepub/view.aspx?cabinet=published_meetings&fileid=' . $fileid;
    $odata = file_get_contents($curl);
    if (!preg_match('/script/',$odata)) {
			top();
      ?>
      <h1>Error</h1>
			<p>
      The file is not currently accessible due to an error on ottawa.ca
			</p>
			<p>
			This usually happens when a meeting is updated and the "file id numbers" change.
			</p>
			<p>
			You can try again later, or go back to the meeting list and see if OttWatch has
			updated the file numbers after re-scanning ottawa.ca.
			</p>
			<p>
			If the problem persists, please email or tweet me.
			</p>
			<?php if ($desc != '') { ?>
			<p>
			This file description may help you locate the relevant meeting date / item, so you can lookup 
			that meeting in <a href="/meetings/">the list of all meetings</a>.<br/>
			</p>
			<p>
			<b>Description:</b> <?php print htmlentities($desc); ?>
			</p>
			<?php } ?>
      <?php
			bottom();
      return;
    }

    $data = preg_replace("/';.*/","",$odata);
    $data = preg_replace("/.*'/","",$data);
    $url = "http://app05.ottawa.ca/sirepub/$data";

    # redirect
    # <script>document.location = 'cache/2/lkwtpr5l2u0ppewlizialyuu/4692203012013020316562.PDF';</script>
    # header("Location: $url");
    # return;

    # pass it through
		$opts = array(
		  'http'=>array(
		    'method'=>"GET",
		  )
		);
		$context = stream_context_create($opts);
		$fp = fopen($url, 'r', false, $context);
    header("Content-Type: application/pdf");
		fpassthru($fp);
		fclose($fp);
    return;
    ## get the real PDF and echo it back.
  }

  static public function getDocumentUrl ($meetid,$doctype) {
    return "http://app05.ottawa.ca/sirepub/agview.aspx?agviewmeetid={$meetid}&agviewdoctype={$doctype}";
  }

  static public function getItemUrl ($itemid) {
    return "http://app05.ottawa.ca/sirepub/agdocs.aspx?doctype=agenda&itemid=".$itemid;
  }

  static public function getMeetingUrl ($id) {
    global $OTT_WWW;
    $row = getDatabase()->one(" select id,category from meeting where id = :id ",array("id" => $id));
    if (!$row['id']) {
      return "";
    }
    return "$OTT_WWW/meetings/{$row['category']}/{$row['id']}";
  }

  static public function meetidForward ($meetid) {
    $m = getDatabase()->one(" select * from meeting where meetid = :meetid ",array("meetid" => $meetid));
    if (!$m['id']) {
      error404();
      return;
    }
    header("Location: ../{$m['category']}/{$m['meetid']}");
  }

#  static public function itemFiles ($category,$id,$itemid,$format) {
#    $item = getDatabase()->all(" select * from item where id = :id ",array('id' => $itemid));
#    $files = getDatabase()->all(" select * from ifile where itemid = :id order by id ",array('id' => $itemid));
#    if ($format == 'files.json') {
#      print json_encode($files);
#      return;
#    }
#    foreach ($files as $f) {
#      $url = "http://app05.ottawa.ca/sirepub/view.aspx?cabinet=published_meetings&fileid={$f['fileid']}";
#      print "<i class=\"icon-file\"></i> <a target=\"_blank\" href=\"{$url}\">".self::trimFileTitle($item['title'],$f['title'])."</a><br/>\n";
#    }
#  }

  static public function meetingDetails_new ($category,$meetid,$itemid) {

    $m = getDatabase()->one(" 
      select * 
			from meeting 
			where meetid = :meetid ",
			array("meetid" => $meetid)
		);
    if (!$m['id']) {
      self::doList($category);
      return;
    }

    $title = meeting_category_to_title($m['category']);
    $focusFrameSrc = self::getDocumentUrl($meetid,'AGENDA');
		$focusFrameSrcBase = self::getDocumentUrl($meetid,'AGENDA');

    $items = getDatabase()->all(" select * from item where meetingid = :meetingid order by id ",array("meetingid"=>$m['id']));

    top3($title . " on " . substr($m['starttime'],0,10));


		?>
		<div class="row">
		<?php
		# generate item and file list
		{
		?>
		<div class="col-sm-4">
    <div id="agendanav" style="overflow:scroll; height: 620px;">

		<h3 class="text-center"><?php print $title; ?></h3>

		<div class="row" style="margin-bottom: 10px;">
		<div class="col-sm-6 text-center"><?php print "".substr($m['starttime'],0,10); ?></div>
		<div class="col-sm-6 text-center"><?php print "<a href=\"http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid=".$meetid."&doctype=AGENDA\"> view on ottawa.ca <small><i class=\"fa fa-external-link\"></i></small></a>"; ?></div>
		</div>

		<p>If you have comments regarding items on this agenda, <a href="#contactDiv">see below for councillor and meeting coordinator contact information</a>.
		It is important that you raise your voice between elections.</p>

    <table id="itemTable" class="table table-bordered table-hover table-condensed" style="width: 95%;">
		<tr>
			<th>Item/File</th>
			<th>Ext.</th>
		</tr>
		<?php
		foreach ($items as $i) {
			?>
			<tr>
			<td style="text-transform: capitalize;">
      <?php print "<a href=\"javascript:focusOnItem({$i['itemid']})\">".strtolower($i['title'])."</a>"; ?>
			</td>
			<td><?php print " <a target=\"_blank\" href=\"http://app05.ottawa.ca/sirepub/item.aspx?itemid={$i['itemid']}\"><small><i class=\"fa fa-external-link \"></i></small></a>"; ?></td>
			</tr>
			<?php

      $files = getDatabase()->all(" select * from ifile where itemid = :itemid order by id ",array("itemid"=>$i['id']));
      if (count($files) > 0) {
        foreach ($files as $f) {
          $ft = self::trimFileTitle($i['title'],$f['title']);
          $fileurl = OttWatchConfig::WWW . "/meetings/file/" . $f['fileid'];
          print "
					<tr>
					<td style=\"padding-left: 20px;\">
					<i class=\"fa fa-file-text\"></i>
					{$ft}
					</td>
					<td>
					<a target=\"_blank\" href=\"{$fileurl}\"><i class=\"fa fa-external-link\"></i></a>
					</td>
					</tr>
					";
        }
      }
		}
		?>
		</table>
		</div><!-- .scroll -->
		</div>
		<?php } ?><!-- .generate nav -->

		<div class="col-sm-8">
			<h3 id="agendaDiv" class="text-center">Agenda</h3>
      <p class="hidden-sm hidden-md hidden-lg text-center"><a href="javascript:scrollToItems()">Back to Item Display</a></p>
	    <iframe id="focusFrame" src="<?php print $focusFrameSrc; ?>" style="width: 100%; height: 600px; border: 2px solid #c0c0c0;"></iframe>
		</div>

		</div><!-- .row -->

		<!-- /////////// Public Delegation Details -->

		<?php
    $members = $m['members'];
    if ($m['category'] == 'City Council') {
      $rows = getDatabase()->all(" select * from electedofficials order by last, first ");
    } else if ($members != '') {
      $members = json_decode($members);
      $rows = getDatabase()->all(" select * from electedofficials where id in (".implode(",",$members).") order by last, first ");
    } else {
      $rows = array();
    }
    $emails = array();
    if ($m['contactEmail'] != '') {
      $emails[] = $m['contactEmail'];
		}
    foreach ($rows as $r) {
      $emails[] = $r['email'];
		}

		$subject = "Comment on ".meeting_category_to_title($m['category'])." - ".substr($m['starttime'],0,10);
		$body = "Please accept the following comments with regard to the ".meeting_category_to_title($m['category'])." meeting on ".substr($m['starttime'],0,10)."\n\n";
    $cmtmailto = 
      "mailto:".strtolower(implode(",",$emails)).
      "?Subject=".urlencode($subject).
      "&Body=".urlencode($body);
		?>


		<div class="row">
		<div class="col-sm-4">
			<h3 id="contactDiv" class="text-center">Public Comment Contact Information</h3>
		</div>
		</div>

		<div class="row">
		<div class="col-sm-4">
	    <p>
	    Everyone is entitled to attend committee meetings and provide a verbal statement (up to 5 minutes long)
	    before Councillors vote on each agenda item. If you are not able to attend a meeting, you can also email your comments to councillors.
			Either way, your statements become part of the official record - and this
	    is the single most important step to shaping the outcome of your city.
	    </p>
	
			<p><a target="_blank" href="<?php print $cmtmailto; ?>">Click this "mailto:" link</a>, or cut-and-paste the following email addresses to send your comments:
			<blockquote><?php print strtolower(implode(", ",$emails)); ?>
			</blockquote>
			</p>
		</div>
		<div class="col-sm-8">
			<b>Committee Members:</b>
	    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
	      <tr>
	      <th>Name</th>
	      <th>Last</th>
	      <th>Email</th>
	      <th>Phone</th>
	      </tr>
	    <?php
	    foreach ($rows as $r) {
	      ?>
	      <tr>
	      <td><?php print "{$r['first']}"; ?></td>
	      <td><?php print "{$r['last']}"; ?></td>
	      <td><?php print "{$r['email']}"; ?></td>
	      <td><?php print "{$r['phone']}"; ?></td>
	      </tr>
	      <?php
	    }
	    ?>
	    </table>
		</div>
		</div><!-- /row -->


		<!-- END /////////// Public Delegation Details -->

		<script>
    function scrollToItems() {
	    $('html, body').animate({
				scrollTop: $("#itemTable").offset().top
			}, 100);
		}
    function focusOnItem(id,title) {
      $('#focusFrame').attr('src','<?php print $focusFrameSrcBase; ?>#Item' + id);
	    $('html, body').animate({
				scrollTop: $("#agendaDiv").offset().top
			}, 100);
      return;
		}
		</script>

		<?php

		bottom3();
	}

  static public function meetingDetails ($category,$meetid,$itemid) {

		if ($_GET['new'] == 1) {
			self::meetingDetails_new($category,$meetid,$itemid);
			return;
		}

    $m = getDatabase()->one(" 
      select 
        *,
        case when starttime <= date_sub(CURRENT_TIMESTAMP,interval 2 day) then 1 else 0 end as started
      from meeting where meetid = :meetid ",array("meetid" => $meetid));
    if (!$m['id']) {
      # meeting ID was not found
      self::doList($category);
      return;
    }

    $focusFrameSrc = self::getDocumentUrl($meetid,'AGENDA');
		$focusFrameSrcBase = self::getDocumentUrl($meetid,'AGENDA');
		if ($m['minutes']) {
			# this is cauing problems when minutes=1 gets marked early
#	    $focusFrameSrc = self::getDocumentUrl($meetid,'MINUTES');
#			$focusFrameSrcBase = self::getDocumentUrl($meetid,'MINUTES');
		}

    $isStarted = $m['started'];
    $summarySrc = self::getDocumentUrl($meetid,'SUMMARY');
    $title = meeting_category_to_title($m['category']);
    if ($itemid != '') {
      $item = getDatabase()->one(" select * from item where itemid = :itemid ",array("itemid"=>$itemid));
      $title = $item['title'];
      $focusFrameSrc .= "#Item$itemid";
    }

    # display list of items, and break out with the files too
    $items = getDatabase()->all(" select * from item where meetingid = :meetingid order by id ",array("meetingid"=>$m['id']));
    top($title . " on " . substr($m['starttime'],0,10));

    # any places for this meeting?
    $places = getDatabase()->all( " 
      select 
        concat(p.rd_num,' ', r.rd_name,' ',r.rd_suffix,' ',coalesce(r.rd_directi,'')) addr,
        astext(p.shape) point,
        i.itemid,
        p.rd_num,
        r.rd_name,
        r.rd_suffix,
        r.rd_directi
      from places p
        join roadways r on p.roadid = r.OGR_FID
        join item i on p.itemid = i.id
      where p.itemid in (select id from item where meetingid = :meetingid) ",array("meetingid"=>$m['id']));
    # LEFT hand navigation, items and files links

		foreach ($places as &$p) {
			$sql = " select * from devapp where address like '%{$p['rd_num']} ".mysql_escape_string($p['rd_name'])."%' limit 1 ";
			$p['devapps'] = getDatabase()->all($sql);
		}
    ?>

    <script>
    function focusOn(type,id,title) {

      if (type == 'item') {
        // move agenda to an item, and refocus on agenda tab just in case user is currently browsing
        // a different tab
        $('#focusFrame').attr('src','<?php print $focusFrameSrcBase; ?>#Item' + id);
        $('#tablist a[href="#tabagenda"]').tab('show'); 
        return;
      }

      d = document.getElementById('tabfile'+id);
      if (d) {
        // already added, just flip to tab
        $('#tablist a[href="#tabfile'+id+'"]').tab('show'); 
      } else {
        // add new tab
        maxtitle = 30;
        if (title.length > maxtitle) {
          title = title.substring(0,maxtitle-3)+'...';
        }

        url = '<?php print OttWatchConfig::WWW; ?>/meetings/file/' + id;
        frameurl = 'http://docs.google.com/viewer?url='+escape(url)+'&embedded=true';
        $('#tablist').append('<li><a href="#tabfile'+id+'" data-toggle="tab">'+title+'</a></li>');
        tabcontent = '';
        tabcontent = tabcontent + '<div class="tab-pane active in" id="tabfile'+id+'">';
        tabcontent = tabcontent + '<iframe id="focusFrame" src="'+frameurl+'" style=" border: 0px; border-left: 1px solid #000000; width: 100%; height: 600px;"></iframe>';
        tabcontent = tabcontent + '</div>';
        $('#tabcontent').append(tabcontent);
        $('#tablist a[href="#tabfile'+id+'"]').tab('show'); 
      }

    }
    </script>

    <div class="row-fluid">

    <!-- column 1 -->
    <div class="span4">

		<center><b><i><p><a href="?new=1" style="color: #ff3333;">(test out the new version of this page)</a></p></i></b></center>

    <div style="float:right; padding-right: 10px;">
    <?php
    renderShareLinks("City meeting: $title","/meetings/{$category}/{$meetid}");
    ?>
    </div>

    <?php
    print "<b>$title</b><br/>";
    print "<small>".substr($m['starttime'],0,10)." ";
    print "<a href=\"http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid=".$meetid."&doctype=AGENDA\"><i class=\"icon-share\"></i> View on Ottawa.ca</a>";
    print "</small>";
    ?>

    <div style="padding: 5px; 0px;">
    <?php
    ?>
    </div>

    <div id="agendanav" style="overflow:scroll; height: 620px;">
    <?php
    foreach ($items as $i) {
      if ($i['title'] == '') {
        // ODD, parser is broken in some way; meh
        continue;
      }
      #print "<pre>"; print print_r($i); print "</pre>";
      print "<b><a href=\"javascript:focusOn('item',{$i['itemid']})\">{$i['title']}</a></b> ";
      print "<a href=\"http://app05.ottawa.ca/sirepub/item.aspx?itemid={$i['itemid']}\"><i class=\"icon-share-alt\"></i></a>";
      print "<br/>\n";
      $files = getDatabase()->all(" select * from ifile where itemid = :itemid order by id ",array("itemid"=>$i['id']));
      if (count($files) > 0) {
        foreach ($files as $f) {
          $ft = self::trimFileTitle($i['title'],$f['title']);
          $fileurl = OttWatchConfig::WWW . "/meetings/file/" . $f['fileid'];
          print "<small><a target=\"_blank\" href=\"{$fileurl}\"><i class=\"icon-share-alt\"></i></a> <a href=\"javascript:focusOn('file',{$f['fileid']},'{$ft}')\"><i class=\"icon-edit\"></i> {$ft}</small></a><br/>\n";
        }
      }
			# devapps?
			$touched = array();
			foreach ($places as $p) {
				if ($p['itemid'] != $i['itemid']) {
					continue;
				}
				foreach ($p['devapps'] as &$d) {
					if (in_array($d['id'],$touched)) {
						continue;
					}
					$touched[] = $d['id'];
          print "<small><a target=\"_blank\" href=\"/devapps/{$d['devid']}\"><i class=\"icon-share-alt\"></i> Possibly related development application: {$d['devid']}</a></small><br/>\n";
				}
			}
      print "<br/>\n";
    }
    ?>
    </div>

    </div>

    <?php 
    // Is there a voting history
    $votes = getDatabase()->all(" select * from itemvote where itemid in (select id from item where meetingid = :meetingid) order by id ",array("meetingid"=>$m['id']));
    ?>

    <!-- column 2 -->
    <div class="span8" style=" border: 0px; border-left: 1px solid #000000; height: 620px;">

    <ul id="tablist" class="nav nav-tabs">
    <li><a href="#tabagenda" data-toggle="tab">Agenda</a></li>
    <?php if (preg_match('/http/',$m['youtube'])) { ?>
    <li><a href="#tabvideo" data-toggle="tab">Video</a></li>
    <?php } ?>
    <?php if (count($votes) > 0) { ?>
    <li><a href="#tabvotes" data-toggle="tab">Votes</a></li>
    <?php } ?>
    <?php if ($isStarted && !$m['minutes']) { ?>
    <li><a href="#tabsummary" data-toggle="tab">Summary</a></li>
    <?php } ?>
    <li><a href="#tabmap" data-toggle="tab">Map</a></li>
    <li><a href="#tabdelegation" data-toggle="tab"><big><b>Public Delegations</b></big></a></li>
    <li><a href="#tabcomments" data-toggle="tab">Comments</a></li>
    </ul>

    <div id="tabcontent" class="tab-content">

    <div class="tab-pane active in" id="tabagenda">
    <iframe id="focusFrame" src="<?php print $focusFrameSrc; ?>" style="width: 100%; height: 600px; border: 0px;"></iframe>
    </div><!-- /tab -->

    <div class="tab-pane" id="tabsummary">
    <iframe src="<?php print $summarySrc; ?>" style="width: 100%; height: 600px; border: 0px;"></iframe>
    </div><!-- /tab -->

    <div class="tab-pane" id="tabvideo">
    <center>
    <?php print self::getYoutubeEmbedCode($m['youtube']); ?>
    </center>
    </div>

    <div class="tab-pane" id="tabvotes">
    <div style="padding: 10px; padding-top: 0px; overflow: scroll; height: 600px;">
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <?php
    $lastitemtitle = '';
    foreach ($votes as $vote) {
      $itemtitle = '';
      foreach ($items as $i) {
        if ($i['id'] == $vote['itemid']) {
          $itemtitle = $i['title'];
        }
      }
      if ($lastitemtitle != $itemtitle) {
        // http://app05.ottawa.ca/sirepub/agdocs.aspx?doctype=minutes&itemid=301229
        ?>
        <tr>
        <td colspan="4"><h4>ITEM: <?php print "$itemtitle"; ?><h4></td>
        </tr>
		    <tr>
		    <th style="width: 40%;">Motion</th>
		    <th style="width: 20%;">Yes</th>
		    <th style="width: 20%;">No</th>
		    <th style="width: 20%;">Absent</th>
		    </tr>
        <?php
      }

      $casts = getDatabase()->all(" select * from itemvotecast where itemvoteid = :id order by itemvoteid,vote,id ",array('id'=>$vote['id']));
      ?>
      <tr>
      <td style="vertical-align: top;">
			<?php print self::formatMotion($vote['motion']); ?><br/>
			<a href="<?php print OttWatchConfig::WWW."/meetings/votes/{$vote['id']}"; ?>"><i class="icon-share"></i> pop-out</a>
			</td>
      <td style="vertical-align: top;">
      <?php
      foreach ($casts as $c) {
        if ($c['vote'] != 'y') { continue; }
        print "<a href=\"".OttWatchConfig::WWW."/meetings/votes/member/".urlencode($c['name'])."\">".$c['name']."</a><br/>";
      }
      ?>
      </td>
      <td style="vertical-align: top;">
      <?php
      foreach ($casts as $c) {
        if ($c['vote'] != 'n') { continue; }
        print "<a href=\"".OttWatchConfig::WWW."/meetings/votes/member/".urlencode($c['name'])."\">".$c['name']."</a><br/>";
      }
      ?>
      </td>
      <td style="vertical-align: top;">
      <?php
      foreach ($casts as $c) {
        if ($c['vote'] != 'a') { continue; }
        print "<a href=\"".OttWatchConfig::WWW."/meetings/votes/member/".urlencode($c['name'])."\">".$c['name']."</a><br/>";
      }
      ?>
      </td>
      </tr>
      <?php
      $lastitemtitle = $itemtitle;
    }
    ?>
    </table>
    </div>
    </div><!-- /tab -->

    <div class="tab-pane" id="tabmap">
    <div id="map_canvas" style="width:100%; height:590px;">
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
      <script>

      var firstResize = 1;
      $('a[data-toggle="tab"]').on('shown', function (e) {
        if (e.target.text == 'Map') {
          if (firstResize == 1) {
            google.maps.event.trigger(map, 'resize');
            map.panTo(new google.maps.LatLng(45.420833,-75.59));
            firstResize = 0;
          }
        }
      });
        var mapOptions = { 
          center: new google.maps.LatLng(45.420833,-75.59), 
          zoom: 10, 
          mapTypeId: google.maps.MapTypeId.ROADMAP 
        };
        infowindow = new google.maps.InfoWindow({ content: '' });
        map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
        <?php
        foreach ($places as $p) {
          $title = '';
          foreach ($items as $tempi) {
            if ($tempi['itemid'] == $p['itemid']) {
              $title = $tempi['title'];
            }
          }
					$p['addr'] = preg_replace("/'/",'',$p['addr']);
          # use anonymous functions so variable scope is not insane
					# [addr] => 265 CARLING AVE 
					# [point] => POINT(-75.6998273 45.4011291)
					# [itemid] => 2399
          $loc = getLatLonFromPoint($p['point']);
          # print "<b><a href=\"javascript:focusOn('item',{$i['itemid']})\">{$i['title']}</a></b><br/>\n";
          ?>
          (function(){
		        myLatlng = new google.maps.LatLng(<?php print $loc['lat']; ?>,<?php print $loc['lon']; ?>);
			      marker = new google.maps.Marker({ position: myLatlng, map: map, title: '<?php print $p['addr']; ?>' }); 
            google.maps.event.addListener(marker, 'click', (function(marker) {
              return function() {
                infowindow.setContent(
			            '<div>' + 
			            '<b><?php print $p['addr']; ?></b> ' + 
		              '<a href="javascript:focusOn(\'item\',<?php print $p['itemid']; ?>)">(Goto Agenda)</a><br/>' +
                  '<?php print preg_replace("/'/","",$title); ?>' +
			            '</div>'
                );
                infowindow.open(map, marker);
              }
            })(marker));
          })();
          <?php
        }
        ?>
      </script>
    </div>
    </div><!-- /tab -->

    <div class="tab-pane fade" id="tabcomments">
    <div style="padding: 10px; padding-top: 0px; overflow: scroll; height: 600px;">
    <h3>This is an informal chat area</h3>
    The purpose of this page is to let residents comment and interact with eachother on the meeting's topics. 
    <b>BUT</b> it is not monitored by the City, so what you say here may not influence any decisions or votes.
    To have your opinion officially heard, use the <b>Public Delegation</b> tab to email the meeting
    coordinator, and through them, the relevant commmittee members.
    <p/>
    <?php disqus(); ?>
    </div>
    </div><!-- /tab -->

    <div class="tab-pane fade" id="tabdelegation">

    <div style="padding: 10px; padding-top: 0px; overflow: scroll; height: 600px;">
    <h3>What is a "Public Delegation"?</h3>
    <p>
    Everyone is entitled to attend committee meetings and provide a verbal statement (up to 5 minutes long)
    before Councillors vote on each agenda item. If you are not able to attend a meeting, you can also email
    your comments to councillors. Either way, your statements become part of the official record - and this
    is the single most important step to shaping the outcome of your city.
    </p>

    <?php
    ob_start();
    ?>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
      <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Ward</th>
      <th>Office</th>
      </tr>
    <?php
    $members = $m['members'];
    if ($m['category'] == 'City Council') {
      $rows = getDatabase()->all(" select * from electedofficials ");
    } else if ($members != '') {
      $members = json_decode($members);
      $rows = getDatabase()->all(" select * from electedofficials where id in (".implode(",",$members).") ");
    } else {
      $rows = array();
    }
    $emails = array();
    foreach ($rows as $r) {
      $emails[] = $r['email'];
      ?>
      <tr>
      <td><b><?php print "{$r['last']}, {$r['first']}"; ?></b></td>
      <td><?php print "{$r['email']}"; ?></td>
      <td><?php print "{$r['phone']}"; ?></td>
      <td><?php print "{$r['ward']}"; ?> (Ward <?php print "{$r['wardnum']}"; ?>)</td>
      <td><?php print "{$r['office']}"; ?></td>
      </tr>
      <?php
    }
    ?>
    </table>
    <?php
    $membersTable = ob_get_contents();
    ob_end_clean();

    $cmtmailto = 
      "mailto:".implode(",",$emails).
      "?Subject={$m['title']} on ".substr($m['starttime'],0,10).
      "&Body=".urlencode("Please accept the following comments with regard to the ".meeting_category_to_title($m['category'])." meeting on ".substr($m['starttime'],0,10)."\n\n");

    $date = substr($m['starttime'],0,10);
    $title = meeting_category_to_title($m['category']);
    $subject = urlencode("Public Delegation for $title on $date");
    $mailto = "mailto:{$m['contactEmail']}?Subject=$subject";
    if ($m['contactName'] != '') {
      // add coordinator email to full committee distribution
      $cmtmailto = preg_replace('/mailto:/',"mailto:{$m['contactEmail']},",$cmtmailto);
	    ?>
	    <p>
	    This meeting's coordinator is:
	    <blockquote><b><?php print $m['contactName']; ?></b><br/>
	    <a target="_blank" href="<?php print $mailto; ?>"><?php print "{$m['contactEmail']}"; ?></a><br/>
	    <?php print $m['contactPhone']; ?></blockquote>
	    </p>
	    <?php 
    } else {
      ?>
	    <p>
	    This meeting's coordinator <b>was not autodetected - oops</b>! But you can read it yourself - it's at the top of the Agenda.
      </p>
	    <?php 
    }
    ?>
    <p>Click here to <a target="_blank" href="<?php print $cmtmailto; ?>">email the councillor & coordinator directly</a>. Here is their full contact information:</p>

    <?php print $membersTable; ?>

    <h3>What should I say? I'm scared! I'm not an expert!</h3>
    <p>
    Here's a little secret: councillors aren't experts either, so don't let that stop you. They are also
    regular people and benefit from feedback from the public on the decisions they are about to make.
    </p>
    <p>
    Anything you choose to write or say as a "public delegation" is good. But if you want some tips, here they are.
    Don't feel limited to these points though - you have rights in this democracy - use them!
    </p>
    <ul>
    <li><b>Be respectful and concise</b>: Make your point in the first paragraph, and leave the proof till later. You have the right to say your peace, but truth be told, I don't know if councillors read these things. So shorter is better.</li>
    <li><b>Come in person if at all possible</b>: Public delegations by email are good, but showing up in person is better. Councillors may skip reading the emails, but they can't avoid listening to you for five minutes if you can make it.</li>
    <li><b>Don't pick on staff</b>: Often staff seem to do things that the public dislikes, but that's because staff only do what Council has instructed them to do in the first place. So if you have a beef, make it a beef with Councillors. (and remember, respectful and concise)</li>
    <li><b>Don't be shy</b>: 
    If you're reviewed the Lobbyist Registry you'll know that business has no shame in pushing agendas at City Hall. If you're reading this, you are the 1 in 5,000 who might take action and change Ottawa's future. 
    So don't be shy or nervous about it! "Power to the people" and all that good stuff, you know?
    </li>
    </ul>

    </div>
    </div><!-- /tab -->


    </div><!-- /tabcontent -->

    </div>

    </div>
    <?php
    bottom();

  }

  static public function doList ($category) {
    global $OTT_WWW;
    top();
    if ($category == 'all' || $category == '') { 
      $category = ''; 
      $title = 'All Categories';
    } else {
      $title = meeting_category_to_title($category);
    }
    ?>

<div id="navbar-example" class="navbar navbar-static">
<div class="navbar-inner">
<div class="container" style="width: auto;">

<ul class="nav" role="navigation">
<a class="brand" href="#">Filter</a>
<li class="dropdown">
  <a id="drop1" href="#" class="dropdown-toggle" data-toggle="dropdown"><?php print $title; ?> <b class="caret"></b></a>
  <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
    <?php
    $rows = getDatabase()->all(" select category,title from category order by title ");
    foreach ($rows as $r) { 
      ?>
      <li role="menuitem"><a href="<?php print urlencode($r['category']); ?>"><?php print $r['title']; ?></a></li>
      <?php
    }
    ?>
    <li role="menuitem" class="divider"></li>
    <li role="menuitem"><a href="all">All Meetings</a></li>
  </ul>
</li>
<!--
<li class="dropdown">
  <a id="drop2" href="#" class="dropdown-toggle" data-toggle="dropdown">Date <b class="caret"></b></a>
  <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
    <li role="menuitem" class="divider"></li>
    <li role="menuitem"><a href="all">All Dates</a></li>
  </ul>
</li>
-->
</ul>

</div>
</div>
</div> 

    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <?php
    $rows = getDatabase()->all(" 
      select 
        m.id,m.category,id,meetid,date(starttime) starttime,
				youtube,youtubestate,youtubestart
      from meeting m 
        left join category c on c.category = m.category 
      where 
        datediff(starttime,CURRENT_TIMESTAMP) < 120 ".
        ($category == '' ? '' : ' and c.category = :category ') ."
      order by 
        starttime desc ",
      array('category' => $category));
			?>
			<tr>
			<th>Date</th>
			<th>Agenda</th>
			<th>Category</th>
			<th>Items</th>
			<th>Video</th>
			</tr>
			<?php
    foreach ($rows as $r) { 
      $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$r['meetid']}&doctype");
      $myurl = htmlspecialchars($OTT_WWW."/meetings/{$r['category']}/{$r['meetid']}");
      ?>
	    <tr>
	      <td style="width: 90px; text-align: center;"><?php print $r['starttime']; ?></td>
	      <td style="width: 90px; text-align: center;"><a class="btn btn-mini" href="<?php print $myurl; ?>">Agenda</a></td>
	      <td>
        <?php print meeting_category_to_title($r['category']); ?>
        </td>
	      <td>
        <?php
        $count = getDatabase()->one(" select count(1) c from item where meetingid = :id ",array('id'=>$r['id']));
        print "{$count['c']} items";
        ?>
        </td>
				<td>
					<?php
					if ($r['youtubestate'] == 'ready') {
						if ($r['youtubestart'] > 0) {
							print "<a target=\"_blank\" href=\"{$r['youtube']}&t={$r['youtubestart']}s\">Youtube</a>\n";
						} else {
							print "<a target=\"_blank\" href=\"{$r['youtube']}\">Youtube</a>\n";
						}
					} else {
						print "Not available";
					}
					?>
				</td>
	    </tr>
      <?php
    }
    ?>
    </table>
    <?php
    bottom();
  }

  /*
   */
  static public function downloadAndParseMeeting ($id) {

    $m = getDatabase()->one(" select * from meeting where meetid = :id or id = :id ",array('id'=>$id));
    if (!$m['id']) { 
      print "downloadAndParseMeeting for $id :: NOT FOUND\n";
      return; 
    }
		$id = $m['id'];

    # detect 'diff' in items/files
    $orig_items = getDatabase()->all(" select * from item where meetingid = $id ");
    $orig_files = getDatabase()->all(" select * from ifile where itemid in (select id from item where meetingid = $id) ");

    $agenda = file_get_contents(self::getDocumentUrl($m['meetid'],'MINUTES')); 
		if (preg_match('/The file could not be found/',$agenda)) {
      # need to use the agenda
      $agenda = file_get_contents(self::getDocumentUrl($m['meetid'],'AGENDA')); 
    } else {
      # mark that we are in MINUTES mode for this meeting now
	    getDatabase()->execute(" update meeting set minutes = 1 where id = $id ");
    }

    # charset issues
    $agenda = mb_convert_encoding($agenda,"ascii");

    # XML issues
    $agenda = preg_replace("/&nbsp;/"," ",$agenda);
	  $lines = explode("\n",$agenda);

    # get members information
    $members = array();
    $raw = '';
    for ($x = 0; $x < count($lines); $x++) {
      $raw .= $lines[$x];
      if (preg_match("/DECLARATIONS/i",$lines[$x])) {
				break;
      }
    }
    $raw = preg_replace("/[^a-zA-Z-]+/"," ",$raw);
    $raw = preg_replace("/  /"," ",$raw);
    $raw = preg_replace("/  /"," ",$raw);
    $raw = preg_replace("/  /"," ",$raw);
    $raw = preg_replace("/  /"," ",$raw);
    $raw = explode(" ",$raw);
    for ($x = 0; $x < (count($raw)-1); $x++) {
      $a = $raw[$x];
      $b = $raw[$x+1];
      $row = getDatabase()->one(" select * from electedofficials where lower(last) = :last and lower(left(first,1)) = :first ",array("first"=>$a,"last"=>$b));
      if ($row['id']) {
        $members[] = $row['id'];
      }
    }
    getDatabase()->execute(" update meeting set members = :members where id = :id ",array(
      'members'=> json_encode($members),
      'id' => $id
    ));

    # get coordinator information
    $coordName = '';
    $coordPhone = '';
    $coordEmail = '';
    for ($x = 0; $x < count($lines); $x++) {
      $matches = array();
      $lines[$x] = preg_replace("/\r/","",$lines[$x]);
      if (preg_match("/(.*), Committee Coordinator/",$lines[$x],$matches)) {
        $coordName = $matches[1];
        $coordPhone = $lines[$x+1];
        $coordEmail = $lines[$x+2];
        $coordPhone = preg_replace("/<.*/","",$coordPhone);
        $coordEmail = preg_replace("/<.*/","",$coordEmail);
        break;
      }
      if (preg_match("/(.*), Coordinator/",$lines[$x],$matches)) {
        $coordName = $matches[1];
        $coordPhone = $lines[$x+1];
        $coordEmail = $lines[$x+2];
        $coordPhone = preg_replace("/<.*/","",$coordPhone);
        $coordEmail = preg_replace("/<.*/","",$coordEmail);
        break;
      }
    }

    if (preg_match('/>/',$coordName)) {
      $coordName = '';
    }
    if (!preg_match('/613-/',$coordPhone)) {
      $coordPhone = '';
    }
    if (!preg_match('/ottawa.ca/',$coordEmail)) {
      $coordEmail = '';
    }
    getDatabase()->execute(" update meeting set contactName = :name, contactEmail = :email, contactPhone = :phone where id = :id ",array(
      'name' => $coordName,
      'phone' => $coordPhone,
      'email' => $coordEmail,
      'id' => $id
    ));

		#
		# ITEM parsing
		#

    getDatabase()->execute(" delete from item where meetingid = :id ",array('id'=>$id));

		$tmp = strip_tags($agenda,'<a>');
		$tmp = preg_replace("/name=Item\d+/"," ",$tmp);
		$tmp = preg_replace("/\n/"," ",$tmp);
		$tmp = preg_replace("/\r/"," ",$tmp);
		$tmp = preg_replace("/<a/","\n<a",$tmp);
		$tmp = preg_replace("/a>/","a>\n",$tmp);
		$tmp = preg_replace("/\t/"," ",$tmp);
		$tmp = preg_replace("/  /"," ",$tmp);
		$tmp = preg_replace("/  /"," ",$tmp);
		$tmp = preg_replace("/  /"," ",$tmp);
		$tmp = preg_replace("/  /"," ",$tmp);
    $tmp = preg_replace("/target=pubright/",'',$tmp);
		#print $tmp; print "\n\n";
		$xml = simplexml_load_string("<foo>{$tmp}</foo>");
		$anchors = $xml->xpath("//a"); 
		foreach ($anchors as $a) {
			$title = $a[0].'';
      $title = preg_replace("/ \? /"," - ",$title);
      $title = preg_replace("/\?/","'",$title);
			$href = $a['href'];
			if (isset($href)) {
				$itemid = preg_replace('/.*itemid=/','',$href);
	  	  $dbitemid = getDatabase()->execute('insert into item (meetingid,itemid,title) values (:meetingid,:itemid,:title) ', array(
	  	    'meetingid' => $id,
	  	    'itemid' => $itemid,
	  	    'title' => $title,
	  	  ));
			}
		}

    # scrape out item IDs, and titles.
		if (false) {
			# OLD ITEM CODE that is oh-so-embarrasing to look at now. Replaced with new block above.
    $add = 0;
    $spool = array();
	  foreach ($lines as $line) {
      $line = preg_replace("/\r/","",$line);
	    if (preg_match("/itemid=/",$line)) {
        $add = 1;
	      $itemid = $line;
	      $itemid = preg_replace("/.*itemid=/","",$itemid);
	      $itemid = preg_replace('/".*/',"",$itemid);
	      $items[] = $itemid;
        array_push($spool,$line);
        continue;
	    }
	    if ($add && preg_match("/a>/",$line)) {
        $add = 0;
        array_push($spool,$line);
        $raw = implode(' ',$spool);
        if (!preg_match('/<a/',$raw)) {
          $snippet = "<a ".$raw."\n";
        } else {
          $snippet = $raw;
        }
        $snippet = preg_replace("/name=Item\d+><\/a>/","",$snippet);
        $snippet = preg_replace("/<\/a>.*/","</a>",$snippet);
        $snippet = preg_replace("/target=pubright/",'',$snippet);
        $snippet = preg_replace("/lang=[^>]*/",'',$snippet);
        $snippet = preg_replace("/\n/",' ',$snippet);
        $snippet = preg_replace("/\r/",' ',$snippet);
        $snippet = preg_replace("/<i>/","",$snippet);
        $snippet = preg_replace("/<br[^>]*>/"," ",$snippet);
        $snippet = preg_replace("/<\/i>/","",$snippet);
        $snippet = preg_replace("/  /"," ",$snippet);
        $snippet = preg_replace("/<h\d>/i","",$snippet);
        $snippet = preg_replace("/<\/h\d>/i","",$snippet);
        $snippet = preg_replace("/  /"," ",$snippet);
        $snippet = preg_replace("/  /"," ",$snippet);
        $snippet = preg_replace("/  /"," ",$snippet);
        $snippet = preg_replace("/<b>/"," ",$snippet);
        $snippet = preg_replace("/<\/b>/"," ",$snippet);
        $snippet = preg_replace("/align=left/"," ",$snippet);
        $snippet = preg_replace("/align=right/"," ",$snippet);
        $snippet = preg_replace("/align=centre/"," ",$snippet);
        $snippet = preg_replace("/align=center/"," ",$snippet);
        $snippet = preg_replace("/<a <\/span><\/span><\/a>/","<a></a> ",$snippet);
        # WARNING, bad snippet >> <a </span></span></a>  <<
        # WARNING, bad snippet >> <a name=Item110633></a>  <<

        $xml = simplexml_load_string($snippet);
				if (!is_object($xml)) {
					print "WARNING, bad snippet >> $snippet <<\n";
					print "WARNING, RAW WAS THIS>> $raw <<\n";
					$title = '<i class="icon-warning-sign"></i> Doh! title autodection failed';
				} else {
	        $title = $xml->xpath("//span"); 
          if (count($title) == 0) {
            # fall back to <a>
	          $title = $xml->xpath("//a"); 
            $title = $title[0].'';
          } else {
            $title = $title[0].'';
          }

	        # charset problems
	        $title = preg_replace("/ \? /"," - ",$title);
	        $title = preg_replace("/\?/","'",$title);
	        $title = preg_replace("/  /"," ",$title);
	        $title = preg_replace("/  /"," ",$title);
	        $title = preg_replace("/  /"," ",$title);
	        $title = preg_replace("/  /"," ",$title);
	        $title = preg_replace("/  /"," ",$title);
	        $title = preg_replace("/  /"," ",$title);
	        $title = preg_replace("/''/","'",$title);
	        $title = preg_replace("/''/","'",$title);
	        $title = preg_replace("/''/","'",$title);
	        $title = preg_replace("/''/","'",$title);
	        $title = preg_replace("/''/","'",$title);
	        $title = preg_replace("/''/","'",$title);
	        $title = preg_replace("/''/","'",$title);
	        $title = preg_replace("/''/","'",$title);
	        $title = preg_replace("/''/","'",$title);
	        $title = preg_replace("/''/","'",$title);
	        $title = preg_replace("/''/","'",$title);
	        $title = preg_replace("/''/","'",$title);
          $title = trim($title);
	
	        # fix open/close brace, and spaces next to braces
	        #$title = preg_replace("/^\(/","",$title);
	        #$title = preg_replace("/\)\s*$/","",$title);
	        $title = preg_replace("/\( +/","(",$title);
	        $title = preg_replace("/ +\)/",")",$title);

				}
        if (strlen($title) > 100) {
          $title = substr($title.'...',0,95);
        }
        if ($title != '') {
		  	  $dbitemid = getDatabase()->execute('insert into item (meetingid,itemid,title) values (:meetingid,:itemid,:title) ', array(
		  	    'meetingid' => $id,
		  	    'itemid' => $itemid,
		  	    'title' => $title,
		  	  ));
        }
        $spool = array();
      }
      if ($add) {
        array_push($spool,$line);
      }
	  }
		}
    
    # purge existing files; not needed as delete ITEM cascades
    # getDatabase()->execute(" delete from ifile where itemid in (select id from item where meetingid = :id) ",array('id'=>$id));

    # go back to the database to build up the "items" in this meeting, and then go grab
    # all the files too.
    $items = getDatabase()->all(' select * from item where meetingid = :id ', array('id' => $id));

	  foreach ($items as $item) {

      # look for references to addresses in the item title
      $words = explode(" ",$item['title']);
      for ($x = 0; $x < (count($words)-1); $x++) {
        $number = $words[$x];
        if (!preg_match("/\d+/",$number)) {
         continue;
        }
        # try name=X=1 and also name="(x+1) (x+1)"
        for ($y = 1; $y <= 2; $y++) {
          $name = '';
          for ($z = 0; $z < $y; $z++) {
  	        if (isset($words[$x+1+$z])) {
    	        $name .= $words[$x+1+$z]." ";
            }
          }
          $name = trim($name);
	        $roads = getDatabase()->all("
	          select *
			      from roadways 
			      where 
			        rd_name = upper(:name) 
			        and (
			          (:number % 2 = left_from % 2 and (:number between cast(left_from as unsigned) and cast(left_to as unsigned)))
			          or (:number % 2 = left_from % 2 and (:number between cast(left_to as unsigned) and cast(left_from as unsigned)))
			          or (:number % 2 = right_from % 2 and (:number between cast(right_from as unsigned) and cast(right_to as unsigned)))
			          or (:number % 2 = right_from % 2 and (:number between cast(right_to as unsigned) and cast(right_from as unsigned)))
			        )
	          ",array(
		          'number' => $number,
		          'name' => $name
	        ));
	        if (count($roads) > 0) {
            #TODO: what if match may? should probably disambituate based on suffix.
            $road = $roads[0];
	          # print "[$x]: ".$words[$x]." -- ".$words[$x+1]." matched ".count($roads)." roads \n";
				    $placeid = getDatabase()->execute(" insert into places (roadid,rd_num,itemid) values (:roadid,:rd_num,:itemid) ",array(
				      'roadid' => $road['OGR_FID'],
				      'rd_num' => $number,
				      'itemid' => $item['id'],
				    ));
            $geo = getAddressLatLon($number,$name);
            if ($geo->status == 'OK') {
              $lat = $geo->results[0]->geometry->location->lat;
              $lon = $geo->results[0]->geometry->location->lng;
  				    getDatabase()->execute(" update places set shape = PointFromText('POINT($lon $lat)') where id = $placeid ");
            }
	        }
        }
      }

	    $html = file_get_contents(self::getItemUrl($item['itemid']));
      self::parseVotingResults($item,$html);
		  $lines = explode("\n",$html);
	    $files = array();
		  foreach ($lines as $line) {
		    if (preg_match("/fileid=/",$line)) {
          $line = preg_replace("/\n/"," ",$line);
          $line = preg_replace("/\r/","",$line);

          $title = $line;
          $title = preg_replace("/.*&nbsp;/","",$title);
          $title = preg_replace("/<.*/","",$title);

		      $fileid = $line;
		      $fileid = preg_replace("/.*fileid=/","",$fileid);
		      $fileid = preg_replace('/".*/',"",$fileid);

          # print "    file: $title\n";
		  	  getDatabase()->execute('insert into ifile (itemid,fileid,title,created,updated) values (:itemid,:fileid,:title,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP) ', array(
		  	    'itemid' => $item['id'],
		  	    'fileid' => $fileid,
		  	    'title' => $title,
		  	  ));
		    }
		  }
	  }


    # detect 'diff' in items/files
    $now_items = getDatabase()->all(" select * from item where meetingid = $id ");
    $now_files = getDatabase()->all(" select * from ifile where itemid in (select id from item where meetingid = $id) ");

    $origitemids = array();
    foreach ($orig_items as $i) { $origitemids[] = $i['itemid']; }
    $nowitemids = array();
    foreach ($now_items as $i) { $nowitemids[] = $i['itemid']; }

    $title = $m['title'];
	  $title = preg_replace("/ AM$/"," am",$title);
	  $title = preg_replace("/ PM$/"," pm",$title);
	  $title = preg_replace("/ am$/","am",$title);
	  $title = preg_replace("/ pm$/","pm",$title);
    $meetingDate = explode(" - ",$title);
    $meetingDate = $meetingDate[1];

#    if (count($origitemids) > 0) {
#      # not a "new" meeting
#	    $newitems = array_diff($nowitemids,$origitemids);
#	    if (count($newitems) > 0) {
#        foreach ($newitems as $n) {
#          $row = getDatabase()->one(" select * from item where itemid = $n ");
#          if ($row['id']) {
#            $title = $row['title'];
#            $itemid = $row['itemid'];
#            $tweet = "New mtg item: {$row['title']} - ".meeting_category_to_title($m['category'])." on $meetingDate";
#          	$link = OttWatchConfig::WWW."/meetings/meetid/".$m['meetid'];
#            #$tweet = tweet_txt_and_url($tweet,$link);
#            if (preg_match('/^ADJOURNMENT$/i',$title)) { continue; }
#            if (preg_match('/^COMMUNICATIONS$/i',$title)) { continue; }
#            if (preg_match('/^CONFIRMATION OF MINUTES$/i',$title)) { continue; }
#            if (preg_match('/DECLARATION OF INTERES/',$title)) { continue; }
#            if (preg_match('/DECLARATIONS OF INTEREST/',$title)) { continue; }
#            print "SKIPPING (but would have sent) $tweet\n"; 
#          }
#        }
#	    }
#    }

  }

  static public function downloadAndParseFile ($id) {

    # DEBUG ONLY
    # getDatabase()->execute(" update ifile set md5 = null where id = :id ",array("id"=>$id));

    $file = getDatabase()->one(" select * from ifile where id = :id ",array("id"=>$id));
    if (!$file['id']) {
      print "Fileid not found: $id\n";
      return;
    }

    # get the file data
    print "downloading file: $id\n";
    $pdf = file_get_contents(OttWatchConfig::WWW . "/meetings/file/".$file['fileid']);
    #file_put_contents("tmp.pdf",$pdf);
    #$pdf = file_get_contents("tmp.pdf");

    $md5 = md5($pdf);
    if ($md5 == $file['md5']) {
      print "md5 is the same as existing file, no need to update\n";
      return;
    }
    getDatabase()->execute(" update ifile set md5 = :md5 where id = :id ",array("md5"=>$md5,"id"=>$id));

    # save to VAR, split into individual pages
    print "  saving to disk\n";
    $saveas = OttWatchConfig::FILE_DIR."/pdf/".$file['fileid'].'.pdf';
    file_put_contents($saveas,$pdf);

    # get the text, page by page
    print "  converting to text\n";
    $txtfile = OttWatchConfig::FILE_DIR."/pdf/".$file['fileid'].'.txt';
    `pdftotext -layout -nopgbrk $saveas`;
    $txt = file_get_contents($txtfile);
    getDatabase()->execute(" update ifile set txt = :txt where id = :id ",array("txt"=>$txt,"id"=>$id));

    # now do word analysis
    print "  inserting words\n";
    getDatabase()->execute(" delete from ifileword where fileid = :id ",array("id"=>$id));
    $lines = explode("\n",$txt);
    $docoffset = 0;
    for ($x = 0; $x < count($lines); $x++) {
	    $wordlist = preg_split("/[^a-zA-Z0-9]+/",$lines[$x]);
      for ($y = 0; $y < count($wordlist); $y++) {
        if ($wordlist[$y] == '') { continue; }
        getDatabase()->execute(" insert into ifileword (fileid,word,line,offset,docoffset) values (:id,lower(:word),:line,:offset,:docoffset) ",array(
          "id" => $id,
          "word" => $wordlist[$y],
          "line" => $x,
          "offset" => $y,
          "docoffset" => $docoffset,
        ));
        $docoffset ++;
      }
    }

  }

  public function parseVotingResults($item,$html) {

		# remove shit from the HTML
		$html = preg_replace('/class=MsoPlaceholderText/','',$html);
		$html = preg_replace('/<\?xml[^>]+>/','',$html);
		$html = preg_replace('/<w:[^>]+>/','',$html);
		$html = preg_replace('/<\/w:[^>]+>/','',$html);

    if (preg_match('/Item not found/',$html)) {
      return;
    }
    if (preg_match('/No voting recorded/',$html)) {
      # nada!
      return;
    }

    # scope HTML to the voting results block.
    $html = preg_replace("/&nbsp;/"," ",$html);
    $html = preg_replace("/<br>/"," ",$html);
    $html = preg_replace("/<BR>/"," ",$html);
    $html = preg_replace("/\r/","",$html);
    $html = preg_replace("/\n/"," ",$html);
    $html = preg_replace("/align=left/"," ",$html);
    $html = preg_replace("/align=right/"," ",$html);
    $html = preg_replace("/align=centre/"," ",$html);
    $html = preg_replace("/align=center/"," ",$html);
    $html = preg_replace('/.*<table id="MotionVotesResultsTable"/',"<table ",$html);
    $html = preg_replace('/<table id="Table1".*/','',$html);

    $xml = simplexml_load_string($html);
    if (!is_object($xml)) {
      print "Error creating voting snippet\n";
      return;
    }

    # now XPATH and tease out the votes
    $tblVotes = array_shift($xml->xpath("//table[@id='tblVotes']"));
    $trs = $tblVotes->xpath("tr");
    while (count($trs) > 0) {

      $vote = array();
      $vote['motion'] = '';
      $vote['votes'] = array();

      # eat "<tr>" two at a time
      $what = array_shift($trs);
      $votes = array_shift($trs);

      $what = $what->asXML();
      $what = preg_replace('/<span.*<\/span>/','',$what); # remove "passed/failed" text from motion text
      $motion = trim(strip_tags($what));
      $motion = preg_replace('/  /',' ',$motion);
      $motion = preg_replace('/  /',' ',$motion);
      $motion = preg_replace('/  /',' ',$motion);
      $vote['motion'] = $motion;

      $votes = simplexml_load_string($votes->asXML()); # xpath doesn't scope to children or something? workaround by re-parsing
      $attendees = $votes->xpath("//td[@class='attendee']"); 
      $votefors = $votes->xpath("//td[@class='votefor' or @class='voteabsent' or @class='voteagainst']");
      if (count($attendees) != count($votefors)) {
        # should not be possible.
        print "\n\nERROR: attendees/votefors does not match; should not happen\n\n";
        pr($item);
        print "\n\n";
        return;
      }
      for ($x = 0; $x < count($attendees); $x ++) {
        $who = trim($attendees[$x]);
        $votefor = trim($votefors[$x]);
        array_push($vote['votes'],array('name'=>$who,'voted'=>$votefor));
      }

      if (strlen($motion) > 1020) {
        $motion = substr($motion,0,1020);
      }
      $voteid = getDatabase()->execute('insert into itemvote (itemid,motion) values (:itemid,:motion) ', array('itemid'=>$item['id'],'motion'=>$motion));
      foreach ($vote['votes'] as $v) {
        if ($v['voted'] == 'Yes') { $vote = 'y'; }
        else if ($v['voted'] == 'No') { $vote = 'n'; }
        else if ($v['voted'] == 'Absent') { $vote = 'a'; }
        else { $vote = 'u'; } // should never happen
        getDatabase()->execute('insert into itemvotecast (itemvoteid,vote,name) values (:itemvoteid,:vote,:name) ', array('itemvoteid'=>$voteid,'vote'=>$vote,'name'=>$v['name']));
      }
      print "\n";

    }

  }

	/*
	Sometimes fileID values on items go stale/bad. Once a day run all meetings that are yet to pass and
	rescan to ensure fileid markers are still accurate (because without RSS change trigger, this may
	not come to like and the GUI is broken.
	*/
	public function hardScan() {
		$rows = getDatabase()->all(" select id,category,starttime from meeting where starttime > CURRENT_TIMESTAMP ");
		foreach ($rows as $r) {
			self::downloadAndParseMeeting($r['id']);
		}
	}
  
}
