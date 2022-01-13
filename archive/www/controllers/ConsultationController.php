<?php

class ConsultationController {

	public static function checkPublicNotices() {
		$url = 'http://ottawa.ca/en/city-hall/accountability-and-transparency/public-meetings-and-notices';
		$html = file_get_contents($url);
#		$html = self::cleanCityHtml2($html);

		$html = preg_replace("/\r/","",$html);
		$html = preg_replace("/\n/","",$html);
		$html = preg_replace("/<br[^>]*>/"," ",$html);
		$html = preg_replace("/&nbsp;/"," ",$html);

		#$html = preg_replace("/<h/","\n<h",$html);
		#$html = preg_replace("/<div/","\n<div",$html);
		$html = preg_replace("/<article/","\n<article",$html);
		$html = preg_replace("/<\/article>/","</article>\n",$html);
		$articles = array();
		foreach (explode("\n",$html) as $l) {
			if (!preg_match("/<article/",$l)) { continue; }
			if (!preg_match("/node-ottawa-article/",$l)) { continue; }
			$articles[] = $l;
		}
		if (count($articles) != 2) {
			print "BAD count for public notices page\n";
			return;
		}

		$html = $articles[1];
    $md5 = md5($html);

		$html = preg_replace("/<article/","\n<article",$html);
		$html = preg_replace("/<div/","\n<div",$html);
		$html = preg_replace("/<p/","\n<p",$html);
		$html = preg_replace("/<h/","\n<h",$html);
		$html = preg_replace("/<li/","\n<li",$html);

		$prevmd5 = getvar('public-meetings-and-notices.md5');

		#print "md5: $md5 prevmd5: $prevmd5\n";
		if ($md5 != $prevmd5) {
			print "changed!\n";
			print "md5: $md5\n";
			print "prevmd5: $prevmd5\n";
    	file_put_contents(OttWatchConfig::FILE_DIR."/consultationmd5/".$md5,$html);
			md5hist_insert(array('curmd5'=>$md5,'prevmd5'=>$prevmd5));
			md5hist_fix();
			md5hist_fix();
			md5hist_fix();
			md5hist_fix();
			md5hist_fix();

			$md5url = "http://ottwatch.ca/md5hist/$md5";
			syndicate('Public Notices page has changed',$md5url,$md5url);
			setvar('public-meetings-and-notices.md5',$md5);
		}
		#print $html;

    #$xml = simplexml_load_string($articles[1]);
		#pr($xml);
	}

  public static function cleanCityHtml2($html) {
		$html = preg_replace("/\n/"," ",$html);
		$html = preg_replace("/\r/"," ",$html);
		$html = preg_replace("/\t/"," ",$html);
		$html = preg_replace("/  */"," ",$html);
		$html = preg_replace("/^.*<body/","<html><body>",$html);
		$html = preg_replace("/”/","\"",$html);
		$html = preg_replace("/&raquo;/"," ",$html);
		$html = preg_replace("/&copy;/"," ",$html);
		$html = preg_replace("/&nbsp;/"," ",$html);
		$html = strip_tags($html,"<html><body><table><tr><td><a><div><h1><h2><h3><h4>");

		# $l = explode("\n",$html); $x = 1; foreach ($l as $ll) { print ">>> $x <<< \n\n $ll\n"; $x++; }

		return $html;
	}

  public static function crawlProjects() {

		$projects = array();

		$url = 'https://ottawa.ca/2/en/city-hall/public-engagement/public-engagement-project-search';
		#$url = 'http://ottawa.ca/2/en/city-hall/public-engagement/public-engagement-project-search?page=1';
		while ($url != '') {
			$html = file_get_contents($url);
	    $xml = simplexml_load_string(self::cleanCityHtml2($html));
			#print "\n\n\nURL $url\n\n\n";
			$url = '';
	
			$as = $xml->xpath('//a');
#			pr($as);
	
			$go = 0;
			$getNext = 0;
			foreach ($as as $a) {
	
				#print "getNext: $getNext go:$go ".$a['href']."\n";
				if (preg_match('/feedback/',$a['href'])) { continue; }

				if ($getNext == 1) {
	
					#print "NEXT? -----> "; print $a['href']; print " ".$a."\n";
					$text = ''.$a;
	
					if (preg_match('/^next .*/',$text)) {
						$url = 'http://ottawa.ca' . $a['href'];
						break;
					}
					continue;
				}

	
				if ($a['href'] == '/2/en/city-hall/public-engagement') {
					#print "\nGO!\n";
					$go = 1; continue; 
				}
				if ($a['href'] == '') { continue; }
				if ( preg_match('/^.*public-engagement\/public-engagement-project-search/',$a['href'])
						&& preg_match('/.*first$/',''.$a) ) {
					$getNext = 1;
					$go = 0;
					continue;
				}
				if (preg_match('/^.*public-engagement\/public-engagement-project-search\?page/',$a['href'])) {
					$getNext = 1;
					$go = 0;
					continue;
				}
				if (!$go) { continue; }

				$href = ''.$a['href'];
				if (preg_match('/^\//',$href)) {
					$href = 'http://ottawa.ca' . $a['href'];
				}
				if (preg_match('/^http/',$href)) {
					$projects[] = $href;
				}
	
			}
			#$url = '';
		}

		foreach ($projects as $url) {
			self::crawlProject($url);
		}
	}

  public static function crawlProject($url) {
		@$html = file_get_contents($url);
		if (strlen($html) == 0) { return; } // no content
    $html = self::cleanCityHtml2($html);
		if (!preg_match('/Get more detailed information on the project/',$html)) {
			return;
		}
		$xml = simplexml_load_string($html);

		$h1s = $xml->xpath("//h1");
		foreach ($h1s as $h1) {
			if ($h1['id'] = 'page-title') {
				$title = ''.$h1;
			}
		}

    $row = getDatabase()->one(" select * from consultation where url = :url ",array('url'=>$url));
		$new = 0;
    if (!$row['id']) {
			db_insert("consultation",array( 'category'=>'default', 'title'=>$title, 'url'=>$url, 'md5'=>'')); 
      syndicate("NEW consultation: $title",$url,$url);
		} 

    $row = getDatabase()->one(" select * from consultation where url = :url ",array('url'=>$url));
		#pr($row);

		$divs = $xml->xpath('//div');
		$phone = '';
		$email = '';
		for ($x = 0; $x < count($divs); $x++) {
			#print "[$x]: >>>".$divs[$x]."<<<\n";
			if (preg_match('/Phone:/',$divs[$x])) { $phone = trim($divs[$x+2]); }
			if (preg_match('/Email:/',$divs[$x])) {
				$div = $divs[$x+1];
				$div = simplexml_load_string($div->asXML());
				$as = $div->xpath('//a');
				$as = $as[0];
				$email = ''.$as;
			}
		}

		$row['phone'] = $phone;
		$row['email'] = $email;
		db_update("consultation",$row,'id');

#		print "-------------------------------------\n";
#		print "\n\n$html\n\n";
#		print "-------------------------------------\n";

# 		$as = $xml->xpath('//a');
# 
# 		foreach ($as as $a) {
# 			$text = ''.$a;
# 			if ($text == 'Get more detailed information on the project') {
# 				#pr($a);
# 				print "\n\n{$a['href']}\n\n";
# 			}
# 			#print $a->href."\n";
# 		}
# 		#print ''.$xml."\n";

	}

  public static function crawlEngagements() {

		$url = 'https://ottawa.ca/2/en/city-hall/public-engagement/public-engagement-event-search';
		$html = file_get_contents($url);
    $xml = simplexml_load_string(self::cleanCityHtml2($html));

		$events = array();

    $trs = $xml->xpath('//tr');
		foreach ($trs as $tr) {
	    $tr = simplexml_load_string($tr->asXML());

			$a = $tr->xpath('//a');
			if (sizeof($a) == 0) { continue; } // no links, likely <th> row
			$a = $a[0];
			$name = trim(''.$a[0]);
			$href = $a['href'];
			if (preg_match('/^\//',$href)) { $href = "http://ottawa.ca$href"; }

			$td = $tr->xpath('//td');
			$date = trim(''.$td[1]);
			#print "\n\n$date\n\n";
			$date = date_parse($date);
			#pr($date);
			$date = date('Y-m-d H:i:s', mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year'])); 

			#Tuesday, January 10, 2017 - 18:00

			$event = array(
				'title' => $name,
				'href' => $href,
				'starttime' => $date,
			);
			$events[] = $event;

			#print "$name\n$href\n$date\n\n";
			#pr($x);
		}

		foreach ($events as $e) {
			#pr($e);
			$row = getDatabase()->one(" select * from publicevent where href = :href ",array('href'=>$e['href']));
			$action = '';
			if (!$row['id']) {
				try {
		      db_insert("publicevent",$e);
					$row = getDatabase()->one(" select * from publicevent where href = :href ",array('href'=>$e['href']));
		      $message = "New consultation event: {$e['title']} ({$e['starttime']})";
		      syndicate($message,$e['href'],$e['href']);
				} catch (Exception $e) {
					pr($e);
				}
			}
		}

		#print $html;
		
	}

  public static function tweetUpdatedConsultations() {

    // only tweet updates since last tweet
    $last = getvar('consultationtweet.last');
    if ($last == '') { $last = time(); }
    setvar('consultationtweet.last',time());

		// find all consultations that have been updated, or have documents that
		// have been updated. Tweet "top-level" consultation updates first, then
		// tweet documents. 
		//
		// Done in this order because some consultations have "documents" that
		// are actually other top-level consultations. Supress those tweets
    $rows = getDatabase()->all(" 
			select 
				c.id,c.title,c.url,c.created,c.updated,
				(case when c.updated >= from_unixtime(:last) then 1 else 0 end) cupdated,
				d.id docid,d.url docurl,d.title doctitle
			from consultation c
				left join consultationdoc d on 
					d.consultationid = c.id 
					and d.updated >= from_unixtime(:last)
			where 
				c.updated >= from_unixtime(:last)
				or (d.id is not null and d.url not in (select url from consultation)) 
			order by
				case when d.id is null then 0 else 1 end,
				c.id,
				d.id
    ",array('last'=>$last));

    # tweet each one, only once
		$tweeted = array();
    foreach ($rows as $row) {
      /*
      if ($row['created'] == $row['updated']) {
				// welcome to the world!
        $tweet = "NEW Consultation: {$row['title']}";
      } elseif ($row['cupdated'] == 1) {
				// consultation row is updated (as opposed to present because of join to updated document)
			} else {
				// one or more documents inside a consultation is updated
        $tweet = "Consultation sub-page(s) updated: {$row['title']}";
			}
      */
      $tweet = "Consultation changed: {$row['title']}";
			if (isset($tweeted[$row['id']])) {
				continue;
			}
      # new style syndication;
      $message = $tweet;
      $path = "/consultations/{$row['id']}";
      syndicate($message,$path);
      # old style, still in play for now
      # $url = OttWatchConfig::WWW."/consultations/{$row['id']}";
      # $tweet = tweet_txt_and_url($tweet,$url);
			# $tweeted[$row['id']] = 1;
      # print "id:{$row['id']} WouldHaveSent: $tweet\n";
      # tweet($tweet);
    }
    
  }

  public static function showConsultationContent($id) {
    $row = getDatabase()->one(" select * from consultation where id = :id ",array('id'=>$id));
    $html = file_get_contents(OttWatchConfig::FILE_DIR."/consultationmd5/".$row['md5']);
    ?>
    <html>
    <head>
    <base href="http://ottawa.ca" target="_blank">
    <style>
    body {
      font-family: Verdana;
      font-size: 10pt;
    }
    a:hover {
      text-decoration: underline;
    }
    a {
      text-decoration: none;
    }
    </style>
    </head>
    <body>
    <?php
    print $html;
    ?>
    </body>
    </html>
    <?php
  }

  public static function showConsultation($id) {
    $row = getDatabase()->one("
	    select 
	      c.*,
	      datediff( CURRENT_TIMESTAMP, c.updated) delta
	    from consultation c
	      left join (select consultationid,max(updated) docupdated from consultationdoc group by consultationid) d on d.consultationid = c.id
	    where id = :id 
      ",array('id'=>$id));
    top($row['title']);
    ?>

		<h1><?php print $row['title']; ?></h1>
		<b>Location</b>: <a target="_blank" href="<?php print $row['url']; ?>"><?php print $row['url']; ?></a><br/>
		<b>Last updated</b>: <?php print ($row['delta'] == 0 ? '<span style="color: #ff0000;">today</span>' : $row['delta'].' days(s) ago'); ?><br/>
		<?php
		$md5 = getDatabase()->one(" select * from md5hist m1 join md5hist m2 on m2.curmd5 = m1.prevmd5 where m1.curmd5 = '{$row['md5']}' order by m1.created desc limit 1 ");
		if (isset($md5['id'])) {
			?><b>Diff to previous version</b>: <a target="_blank" href="/md5hist/<?php print $row['md5']; ?>">diff</a><br/><?php
		}
		?>

    <?php
    $docs = getDatabase()->all(" 
			select *,datediff(CURRENT_TIMESTAMP,updated) delta 
			from consultationdoc 
			where consultationid = :id 
			order by 
				deleted,
				updated desc
			",array('id'=>$row['id']));
		if (count($docs) > 0) {
			?>
			<h3>Documents at a glance</h3>
	    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
			<tr>
			<th>Modified</th>
			<th>Title</th>
			<th>Diff to previous</th>
			</tr>
			<?php
    	foreach ($docs as $doc) {
	      ?>
		    <tr>
		    <td><?php print ($doc['delta'] == 0 ? '<span style="color: #ff0000;">today</span>' : $doc['delta'].' days(s) ago'); ?></td>
		    <td>
				<?php if ($doc['deleted'] == 1) { print "(DELETED)"; } ?>
				<a target="_blank" href="<?php print $doc['url']; ?>"><?php print $doc['title']; ?></a></td>
		    <td>
				<?php
				$md5 = getDatabase()->one(" select * from md5hist m1 join md5hist m2 on m2.curmd5 = m1.prevmd5 where m1.curmd5 = '{$doc['md5']}' order by m1.created desc limit 1 ");
				if (isset($md5['id'])) {
					?><a target="_blank" href="/md5hist/<?php print $doc['md5']; ?>">diff</a><br/><?php
				} else {
					?>
					-
					<?php
				}
				?>
				</td>
		    </tr>
	      <?php
	    }
			?>
    	</table>
			<?php
		}

    bottom();
  }

  public static function showMain() {
    top3();
		$rows = getDatabase()->all(" select *,datediff(starttime,CURRENT_TIMESTAMP) from publicevent where datediff(starttime,CURRENT_TIMESTAMP) > -7 order by starttime desc ");
		if (count($rows) > 0) {
		?>
		<h1>Upcoming and Recent Public Events</h1>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
		<tr>
		<th>Date</th>
		<th>Title</th>
		</tr>

		<?php
		foreach ($rows as $r) {
			?>
			<tr>
			<td><?php print $r['starttime']; ?></td>
			<td><a href="<?php print $r['href']; ?>" target="_blank"><?php print $r['title']; ?></a></td>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
		} 
		?>
		<h1>Consultations</h1>

    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <tr>
    <th>Title</th>
    <th>Updated (days ago)</th>
    </tr>
    <?php
    $rows = getDatabase()->all(" 
    select 
      c.*,
      datediff( CURRENT_TIMESTAMP, c.updated) delta
    from consultation c
      left join (select consultationid,max(updated) docupdated from consultationdoc group by consultationid) d on d.consultationid = c.id
    order by 
			case when c.category = 'DELETED' then 1 else 0 end,
      case when d.docupdated is null then c.updated else greatest(c.updated,d.docupdated) end desc,
			title
    ");
    foreach ($rows as $row) {
      ?>
	    <tr>
	    <th><a target="_blank" href="/consultations/<?php print $row['id']; ?>"><?php print $row['title']; ?></a></th>
	    <td><?php 
				if ($row['delta'] == 0) {
					?>
					<span style="color: #ff0000;">today</span>
					<?php
				} else {
					print $row['delta']; 
				}
			?></td>
	    </tr>
      <?php
      $docs = getDatabase()->all(" select *,datediff(CURRENT_TIMESTAMP,updated) delta from consultationdoc where consultationid = :id order by updated desc ",array('id'=>$row['id']));
      foreach ($docs as $doc) {
        ?>
		    <tr>
		    <td style="padding-left: 20px;"><a target="_blank" href="<?php print $doc['url']; ?>"><?php print $doc['title']; ?></a></td>
		    <td><?php 
					if ($doc['delta'] == 0) {
						?>
						<span style="color: #ff0000;">today</span>
						<?php
					} else {
						print $doc['delta']; 
					}
				?></td>
		    <td></td>
		    </tr>
        <?php
      }
    }
    ?>
    </table>
    <?php
    bottom3();
  }

  public static function crawlConsultations() {

		# use this to find consultations that have been removed (because they won't be updated)
		getDatabase()->execute(" update consultation set category = 'DELETED'; ");

		# manually missing...
		self::crawlCategory('Bridges and Pathways','http://ottawa.ca/en/city-hall/planning-and-development/under-way');
		self::crawlCategory('Bridges and Pathways','http://ottawa.ca/en/major-projects/construction-and-infrastructure/planned-2');
		self::crawlCategory('By-law','http://ottawa.ca/en/city-hall/public-consultations/law');
		self::crawlCategory('Construction and Infrastructure projects','http://ottawa.ca/en/major-projects/construction-and-infrastructure');
		self::crawlCategory('Cycling projects','http://ottawa.ca/en/city-hall/planning-and-development/planned-0');
		self::crawlCategory('Cycling projects','http://ottawa.ca/en/underway');
		self::crawlCategory('Economic Development and Innovation','http://ottawa.ca/en/city-hall/public-consultations/economic-development-and-innovation');
		self::crawlCategory('Environment','http://ottawa.ca/en/city-hall/public-consultations/environment');
		self::crawlCategory('Municipal Addressing','http://ottawa.ca/en/municipal-addressing-0');
		self::crawlCategory('Parks and Recreation','http://ottawa.ca/en/city-hall/public-consultations/parks-and-recreation-public-consultations');
		self::crawlCategory('Planning and Infrastructure','http://ottawa.ca/en/taxonomy/term/2651');
		self::crawlCategory('Public Engagement','http://ottawa.ca/en/city-hall/public-consultations/public-engagement/public-engagement-strategy-and-consultations');
		self::crawlCategory('Public Engagement','http://ottawa.ca/en/public-engagement');
		self::crawlCategory('Roadwork','http://ottawa.ca/en/city-hall/planning-and-development/under-way-2');
		self::crawlCategory('Roadwork','http://ottawa.ca/en/major-projects/construction-and-infrastructure/planned');
		self::crawlCategory('Safety','http://ottawa.ca/en/city-hall/public-consultations/miscellaneous');
		self::crawlCategory('Sewers and Wastewater','http://ottawa.ca/en/city-hall/public-consultations/sewers-and-wastewater');
		self::crawlCategory('Sewers, water and wastewater','http://ottawa.ca/en/city-hall/planning-and-development/under-way-0');
		self::crawlCategory('Sewers, water and wastewater','http://ottawa.ca/en/major-projects/construction-and-infrastructure/planned-0');
		self::crawlCategory('Sidewalks','http://ottawa.ca/en/planned');
		self::crawlCategory('Sidewalks','http://ottawa.ca/en/underway-0');
		self::crawlCategory('Transit','http://ottawa.ca/en/city-hall/planning-and-development/planned');
		self::crawlCategory('Transit','http://ottawa.ca/en/city-hall/planning-and-development/under-way-1');
		self::crawlCategory('Transit','http://ottawa.ca/en/city-hall/public-consultations/transit');
		self::crawlCategory('Transportation','http://ottawa.ca/en/city-hall/public-consultations/transportation');
		self::crawlCategory('Water','http://ottawa.ca/en/city-hall/public-consultations/water');

		# purge any deleted (or URL renamed) consultations
		# getDatabase()->execute(" delete from consultation where category = 'DELETED'; ");

		return;

    # start at the stop level consultation listing.
    $html = file_get_contents("http://ottawa.ca/en/city-hall/public-consultations");
    $html = strip_tags($html,"<a>");
    $matches = array();
    preg_match_all("/<a[^h]+href=\"([^\"]+)\"[^>t]+title=\"([^\"]+)\"[^<]+<\/a>/",$html,$matches);
    for ($x = 0; $x < count($matches[0]); $x++) {
      if (! preg_match('/Learn/',$matches[2][$x])) { continue; }
      $matches[2][$x] = preg_replace('/Learn more about /','',$matches[2][$x]);
      $url = $matches[1][$x];
      $category = $matches[2][$x];
      self::crawlCategory($category,"http://ottawa.ca{$url}");
    }


  }

  // crawl a category for its consultations

  public static function crawlCategory ($category, $url) {
    $html = file_get_contents($url);
		$html = self::getCityContent($html,'');

    $xml = simplexml_load_string($html);
    $div = $xml->xpath('//div[@id="cityott-content"]');
    $div = simplexml_load_string($div[0]->asXML());

    $links = $div->xpath('//a');
    foreach ($links as $a) {
      $url = $a->attributes();
      if (substr($url,0,1) == '/') {
        $url = "http://ottawa.ca{$url}";
      }

      $title = $a[0];
      self::crawlConsultation($category,$title,$url);
    }

  }

  // crawl a specific consultation 

  public static function crawlConsultation ($category, $title, $url) {

    $html = @file_get_contents($url);
		if ($html === FALSE) { 
      // this is probably a one-time network error on a link that does actually
      // exist. Skip. If a consultation does link here and we can never load
      // the link then no harm, the first insert is not done until below anyway
      return;
    }
		$html = self::getCityContent($html,'');

    $contentMD5 = md5($html);
    file_put_contents(OttWatchConfig::FILE_DIR."/consultationmd5/".$contentMD5,$html);

    $row = getDatabase()->one(" select * from consultation where url = :url ",array('url'=>$url));
    if ($row['id']) {
      getDatabase()->execute(" update consultation set category = :category where id = :id ",array('id'=>$row['id'],'category'=>$category));
      if ($row['md5'] != $contentMD5) {
				print "--------------------------------------------------------------\n";
				print "$title ($category) CHANGED\n";
				print "http://ottwatch.ca/consultations/{$row['id']}\n";
        print "c.sh {$row['md5']} $contentMD5\n";
#				print "http://app.kevino.ca/ottwatchvar/consultationmd5/{$row['md5']}\n";
#				print "http://app.kevino.ca/ottwatchvar/consultationmd5/$contentMD5\n";
				print "\n";
        getDatabase()->execute(" update consultation set md5 = :md5, updated = CURRENT_TIMESTAMP where id = :id ",array('id'=>$row['id'],'md5'=>$contentMD5));
				md5hist_insert(array('curmd5'=>$contentMD5,'prevmd5'=>$row['md5']));
      }
    } else {
      $id = db_insert("consultation",array( 'category'=>$category, 'title'=>$title, 'url'=>$url, 'md5'=>$contentMD5)); 
			md5hist_insert(array('curmd5'=>$contentMD5,'prevmd5'=>''));
			print "--------------------------------------------------------------\n";
			print "$title ($category) NEW\n";
			print "http://ottwatch.ca/consultations/$id\n";
			print "\n";
		}

    # reset from database, may have updated/inserted
    $row = getDatabase()->one(" select * from consultation where url = :url ",array('url'=>$url));

    # read individual documents.
    $xml = @simplexml_load_string($html);
		if (!is_object($xml)) {
			# nothign to crawl
			return;
		}
    $div = $xml->xpath('//div[@id="cityott-content"]');
    $div = simplexml_load_string($div[0]->asXML());
    $links = $div->xpath('//a');

		getDatabase()->execute(" update consultationdoc set deleted = 1 where consultationid = {$row['id']} ");

    foreach ($links as $a) {
      $docLink = $a->attributes();
      if (substr($docLink,0,1) == '/') {
        $docLink = "http://ottawa.ca{$docLink}";
      }
      $docTitle = $a[0];

			# skip things that go outside public consultations
			if (!preg_match('/^http/',$docLink)) { continue; }
			if (preg_match('/cgi-bin\/docs.pl/',$docLink)) { continue; }
			if (preg_match('/mailto/',$docLink)) { continue; }
			if ($docLink == '') { continue; }

      self::crawlConsultationLink($row,$docTitle,$docLink);
    }

		# getDatabase()->execute(" delete from consultationdoc where title = 'DELETED' and consultationid = {$row['id']} ");

  }

  // crawl all links within the consultation page itself; might be links to other ottawa.ca/drupal nodes
  // or to PDFs

  public static function crawlConsultationLink ($parent, $title, $url) {
    #print "    LINK: $title\n";

		if ($url == 'http://ottawa.ca/en/node/298008') {
			# lansdown is not actually changing...
			return;
		}

		if (!preg_match('/\/ottawa.ca\//',$url)) {
			# do not leave ottawa.ca
			return;
		}

    $data = @file_get_contents($url);
		if ($data === FALSE) { 
			# probably 403 or 404 error codes; ignore
			return;
		}

		if (preg_match('/link.*canonical.*documents\.ottawa\.ca/',$data)) {
			# the document management pages are skipped for now.
			return;
		}

    $md5 = md5($data);
    if (preg_match('/<html/',$data)) {
      $data = self::getCityContent($data,'');
      $md5 = md5($data);
    }

    $row = getDatabase()->one(" select * from consultationdoc where url = :url ",array('url'=>$url));
    if ($row['id']) {
      getDatabase()->execute(" update consultationdoc set deleted = null, title = :title where id = :id ",array('id'=>$row['id'],'title'=>$title));
      if ($row['md5'] != $md5) {

				print "--------------------------------------------------------------\n";
				print "$title DOC CHANGED $url\n";
				print "http://ottwatch.ca/consultations/{$parent['id']}\n";
        print "c.sh {$row['md5']} $md5\n";
#				print "http://app.kevino.ca/ottwatchvar/consultationmd5/{$row['md5']}\n";
#				print "http://app.kevino.ca/ottwatchvar/consultationmd5/$md5\n";
				print "\n";

        getDatabase()->execute(" update consultationdoc set md5 = :md5, updated = CURRENT_TIMESTAMP where id = :id ",array('id'=>$row['id'],'md5'=>$md5));
				md5hist_insert(array('curmd5'=>$md5,'prevmd5'=>$row['md5']));
      }
    } else {
      $id = db_insert("consultationdoc",array('consultationid'=>$parent['id'],'title'=>$title, 'url'=>$url, 'md5'=>$md5)); 
			md5hist_insert(array('curmd5'=>$md5,'prevmd5'=>''));
			print "--------------------------------------------------------------\n";
			print "$title DOC NEW $url\n";
			print "http://ottwatch.ca/consultations/{$parent['id']}\n";
			print "\n";
    }

		$data = "DOCUMENT|$url|$title\n.$data";
    file_put_contents(OttWatchConfig::FILE_DIR."/consultationmd5/".$md5,$data);
  }

  // given an HTML page served up by ottawa.ca drupal, return just the HTML that is the content region,
  // and discard menues and navigation, etc

  public static function getCityContent ($html,$tags) {

		if (!preg_match('/cityott-content/',$html)) {
			# not a drupal node
			return $html;
		}

		$o = $html;

    # remove things that break XML parsing
    $html = preg_replace("/\n/","KEVINO_NEWLINE",$html);
    $html = preg_replace("/<head.*<body/","<body",$html);
    $html = preg_replace("/&lang=en/","",$html); # not all HTML is escaped property, avoids <a href="...&lang=en" crap
    $html = preg_replace("/<script[^<]+<\/script>/"," ",$html);
    $html = preg_replace("/\/\//","",$html);
    $html = preg_replace("/KEVINO_NEWLINE/","\n",$html);
    $html = preg_replace("/ & /"," and ",$html);
    $html = preg_replace("/<p[^>]+>/","",$html);
    $html = preg_replace("/<\/p>/","__BR__",$html);
    $html = strip_tags($html,"<div><br><a><h1><h2><h3><h4><h5>$tags");
    $html = preg_replace("/__BR__/","<br/><br/>",$html);

    # the view-dom-id CLASS changes randomly, so remove it
    # example: view-dom-id-b477d62d0bdb286acc260d50c820060d
    $html = preg_replace("/view-dom-id-[a-z0-9]+/","",$html);

    $xml = simplexml_load_string($html);
		if (!is_object($xml)) {	
			print "ERROR: is not object";
			print "-----HTML-----\n";
			print "$html";
			print "/-----HTML-----\n";
			return $html;
		}
    $div = $xml->xpath('//div[@id="cityott-content"]');
		if (count($div) == 0) {
			# return original HTML
			return $html;
		}
    $newHTML = $div[0]->asXML();
		if (preg_match('/cityott-sidebar/',$html)) {
	    $sidediv = $xml->xpath('//div[@id="cityott-sidebar"]');
			if (count($sidediv) > 0) {
		    $sideHTML = $sidediv[0]->asXML();
				$newHTML = "
					<div id=\"fakeroot\">
					<!-- NEW HTML -->
					$newHTML
					<!-- SIDEBAR HTML -->
					$sideHTML
					</div>
				";
			}
		}
		return $newHTML;
  }
  
}