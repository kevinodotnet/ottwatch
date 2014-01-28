<?php

class ConsultationController {

  // main entry point for crawling public consultations

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
      if ($row['created'] == $row['updated']) {
				// welcome to the world!
        $tweet = "NEW Consultation: {$row['title']}";
      } elseif ($row['cupdated'] == 1) {
				// consultation row is updated (as opposed to present because of join to updated document)
        $tweet = "Consultation updated: {$row['title']}";
			} else {
				// one or more documents inside a consultation is updated
        $tweet = "Consultation sub-page(s) updated: {$row['title']}";
			}
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
		<b>Last updated</b>: <?php print ($row['delta'] == 0 ? '<span style="color: #ff0000;">today</span>' : $row['delta'].' days(s) ago'); ?><br/>

		<h3>Documents at a glance</h3>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
	  <tr>
	  </tr>
    <?php
    $docs = getDatabase()->all(" select *,datediff(CURRENT_TIMESTAMP,updated) delta from consultationdoc where consultationid = :id order by updated desc ",array('id'=>$row['id']));
    foreach ($docs as $doc) {
      ?>
	    <tr>
	    <td><?php print ($doc['delta'] == 0 ? '<span style="color: #ff0000;">today</span>' : $doc['delta'].' days(s) ago'); ?></td>
	    <td><a target="_blank" href="<?php print $doc['url']; ?>"><?php print $doc['title']; ?></a></td>
	    </tr>
      <?php
    }
    ?>
    </table>

    <div class="row-fluid">
   
    <div class="span6">
    <?php
    $frameSrc = "{$row['id']}/content";
    ?>
    <h3>Overview</h3>
    <i>
    The overview provided below may have formatting and readability problems.
    You're better off <a target="_new" href="<?php print $row['url']; ?>">viewing the actual page on ottawa.ca</a> instead.</i>
    <iframe src="<?php print $frameSrc ?>" style="margin-top: 10px; width: 100%; height: 600px; border: 2px solid #000000;"></iframe>
    </div>

    <div class="span6">
    <h3>Discuss</h3>
    <?php disqus(); ?>
    </div>

    </div><!-- row -->

    <?php
    bottom();
  }

  public static function showMain() {
    top();
    ?>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <tr>
    <th>Consultation/Document Title</th>
    <th>Updated (days ago)</th>
    <!-- <th>Created</th> -->
    <th>Category</th>
    </tr>
    <?php
    $rows = getDatabase()->all(" 
    select 
      c.*,
      datediff( CURRENT_TIMESTAMP, c.updated) delta
    from consultation c
      left join (select consultationid,max(updated) docupdated from consultationdoc group by consultationid) d on d.consultationid = c.id
    order by 
      case when d.docupdated is null then c.updated else greatest(c.updated,d.docupdated) end desc,
			title
    ");
    foreach ($rows as $row) {
      ?>
	    <tr>
	    <th><a target="_blank" href="<?php print $row['id']; ?>"><?php print $row['title']; ?></a></th>
	    <td><?php 
				if ($row['delta'] == 0) {
					?>
					<span style="color: #ff0000;">today</span>
					<?php
				} else {
					print $row['delta']; 
				}
			?></td>
	    <td><?php print $row['category']; ?></td>
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
    bottom();
  }

  public static function crawlConsultations() {
    # start at the stop level consultation listing.
    $html = file_get_contents("http://ottawa.ca/en/city-hall/public-consultations");
    $html = strip_tags($html,"<a>");
    $matches = array();
    preg_match_all("/<a[^h]+href=\"([^\"]+)\"[^>t]+title=\"([^\"]+)\"[^<]+<\/a>/",$html,$matches);
    #print_r($matches);
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
    #print "CATEGORY: $category\n";
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
      if ($row['md5'] != $contentMD5) {
        print "consultation.id = {$row['id']} md5 changed: {$row['md5']} $contentMD5\nurl: $url\n\n";
        getDatabase()->execute(" update consultation set md5 = :md5, updated = CURRENT_TIMESTAMP where id = :id ",array('id'=>$row['id'],'md5'=>$contentMD5));
      }
    } else {
      $id = db_insert("consultation",array( 'category'=>$category, 'title'=>$title, 'url'=>$url, 'md5'=>$contentMD5)); 
      print "consultation.id = {$id} is new\nurl: $url\n\n";
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
  }

  // crawl all links within the consultation page itself; might be links to other ottawa.ca/drupal nodes
  // or to PDFs

  public static function crawlConsultationLink ($parent, $title, $url) {
    #print "    LINK: $title\n";

    $data = @file_get_contents($url);
		if ($data === FALSE) { 
			# probably 403 or 404 error codes; ignore
			return;
		}

    if (preg_match('/<title>Documents<\/title>/',$data)) {
			# we ended up on documents.ottawa.ca; TODO 2014-01-19 crawl forward again to the actual PDF page, etc
			#print "\n\n\n";
			#print $data;
			#print "\n\n\n";
			#print "SKIPPING documents.ottawa HTML page";
			#exit;
			return;
    }

    $md5 = md5($data);
    if (preg_match('/<html/',$data)) {
      $data = self::getCityContent($data,'');
      $md5 = md5($data);
    }

    $row = getDatabase()->one(" select * from consultationdoc where url = :url ",array('url'=>$url));
    if ($row['id']) {
      if ($row['md5'] != $md5) {
        print "consultation.id = {$parent['id']} doc.id = {$row['id']} md5 changed: {$row['md5']} $md5\nurl: $url\n\n";
        getDatabase()->execute(" update consultationdoc set md5 = :md5, updated = CURRENT_TIMESTAMP where id = :id ",array('id'=>$row['id'],'md5'=>$md5));
      }
    } else {
      $id = db_insert("consultationdoc",array('consultationid'=>$parent['id'],'title'=>$title, 'url'=>$url, 'md5'=>$md5)); 
      print "consultation.id = {$parent['id']} doc.id = {$id} is new\nurl: $url\n\n";
    }

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

?>
