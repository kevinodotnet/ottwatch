<?php

class DevelopmentAppController {

	static public function coaAgendaToDevApp() {

	  Epi::init('api');
		getApi()->get('/api/scrape/item/(\d+)', array('MeetingController','apiScrapeItem'), EpiApi::external);

		$sql = "
			select
				i.id,
				i.itemid,
				i.title,
				m.meetid meetid,
				left(m.starttime,10) starttime,
				m.category
			from
				meeting m
				join item i on i.meetingid = m.id
			where
				m.category like 'COA%'
			order by
				i.id desc
			limit 50
		";
		$rows = getDatabase()->all($sql);
		foreach ($rows as $item) {
			#pr($item);
			$details = getApi()->invoke("/api/scrape/item/" . $item['itemid']);
			$devappid = $details['devappid'];
			$devappid = preg_replace('/ .*/','',$devappid);
			#pr($details);
			$devapp = getDatabase()->one(" select * from devapp where devid = :devid ",array('devid'=>$devappid));
			$addresses = array();
			if (! isset($devapp['id'])) {
				pr($item);
				$addresses[] = array(
					'addr'=>$details['addressstr']
				);
		    $id = getDatabase()->execute(" 
		      insert into devapp 
		      (address,appid,devid,ward,apptype,receiveddate,created,updated,description)
		      values
		      (:address,:appid,:devid,:ward,:apptype,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,:description)",array(
		        'devid'=> $devappid,
		        'address'=> json_encode($addresses),
		        'appid'=> 'n/a',
		        'ward' => $details['ward'],
		        'apptype' => 'coa', #$labels['Application'],
		        'description' => "CoA {$item['starttime']} panel {$item['category']}"
		    ));
				$devapp = getDatabase()->one(" select * from devapp where devid = :devid ",array('devid'=>$devappid));
			}
			$status = "CoA meeting <a href=\"http://app05.ottawa.ca/sirepub/item.aspx?itemid={$item['itemid']}\">{$item['itemid']}</a>";
			getDatabase()->execute(" delete from devappstatus where devappid = :id and statusdate = :starttime ",array('id'=>$devapp['id'],'starttime'=>$item['starttime']));
      getDatabase()->execute(" 
					insert into devappstatus (devappid,status,statusdate) values (:devappid,:status,:statusdate) 
				",array(
        'devappid' => $devapp['id'],
        'status' => $status,
        'statusdate' => $item['starttime']
      ));
		}
	}

	static public function apiScrapeCoaSireForItemIds() {
		
		$urls = array();
		$urls[] = 'http://app05.ottawa.ca/sirepub/items.aspx?stype=advanced&itemtype=-%20All%20Types%20-&meettype=Committee%20of%20Adjustment%20-%20Panel%201&meetdate=-%20All%20Dates%20-';
		$urls[] = 'http://app05.ottawa.ca/sirepub/items.aspx?stype=advanced&itemtype=-%20All%20Types%20-&meettype=Committee%20of%20Adjustment%20-%20Panel%202&meetdate=-%20All%20Dates%20-';
		$urls[] = 'http://app05.ottawa.ca/sirepub/items.aspx?stype=advanced&itemtype=-%20All%20Types%20-&meettype=Committee%20of%20Adjustment%20-%20Panel%203&meetdate=-%20All%20Dates%20-';

		$html = "";
		foreach ($urls as $u) {
			$html .= file_get_contents($u);
		}
		#file_put_contents('h',$html);
		#$html = file_get_contents('h');
		$html = strtolower($html);
		$html = strip_tags($html,'<tr><td><img>');
		$html = preg_replace("/\n/"," ",$html);
		$html = preg_replace("/\r/"," ",$html);
		$html = preg_replace("/\t/"," ",$html);
		$html = preg_replace('/&nbsp;/'," ",$html);
		$html = preg_replace("/  /"," ",$html);
		$html = preg_replace("/  /"," ",$html);
		$html = preg_replace("/  /"," ",$html);
		$html = preg_replace("/  /"," ",$html);
		$html = preg_replace("/  /"," ",$html);
		$html = preg_replace("/  /"," ",$html);
		$html = preg_replace("/<tr/","\n<tr",$html);
		$html = preg_replace("/\/tr>/","/tr>\n",$html);

		$items = array();

		foreach (explode("\n",$html) as $l) {
			if (!preg_match('/^<tr/',$l)) { continue; }
			if (!preg_match('/datagrid/',$l)) { continue; }
			if (preg_match('/meeting date/',$l)) { continue; }
			$l = preg_replace('/<tr[^>]*>/',"",$l);
			$l = preg_replace('/<td[^>]*>/',"|",$l);
			$l = preg_replace('/<\/td>/',"",$l);
			$l = preg_replace('/<\/tr>/',"",$l);
			$l = preg_replace('/<img.*gotoitem\(/',"",$l);
			$l = preg_replace('/\)" \/>/',"",$l);
			$l = preg_replace('/<img[^>]*>/',"",$l);
			$l = preg_replace('/\|\|*/',"|",$l);
			$l = html_entity_decode($l);

			$l = preg_replace('/-jan-/','-01-',$l);
			$l = preg_replace('/-feb-/','-02-',$l);
			$l = preg_replace('/-mar-/','-03-',$l);
			$l = preg_replace('/-apr-/','-04-',$l);
			$l = preg_replace('/-may-/','-05-',$l);
			$l = preg_replace('/-jun-/','-06-',$l);
			$l = preg_replace('/-jul-/','-07-',$l);
			$l = preg_replace('/-aug-/','-08-',$l);
			$l = preg_replace('/-sep-/','-09-',$l);
			$l = preg_replace('/-oct-/','-10-',$l);
			$l = preg_replace('/-nov-/','-11-',$l);
			$l = preg_replace('/-dec-/','-12-',$l);

			# ::: |343010|d08-01-15/b-00445|committee of adjustment - panel 3|2016-jan-13|21-rideau-goulbourn|6096 third line north :::
			# print "::: $l :::\n";

			$l = explode('|',$l);
			$index = 1;
			$item = array(
				'itemid' => $l[$index++],
				'devappids' => $l[$index++],
				'panel' => $l[$index++],
				'date' => $l[$index++],
				'ward' => $l[$index++],
				'addresses' => $l[$index++]
			);
			$item['api_url'] = OttWatchConfig::WWW . "/api/scrape/item/" . $item['itemid'];

			$ids = $item['devappids'];
			$ids = preg_replace("/ to /"," ",$ids);
			$ids = preg_replace("/ & /"," ",$ids);
			$ids = preg_replace("/^ */","",$ids);
			$ids = preg_replace("/ *$/","",$ids);
			$item['devappids'] = explode(" ",$ids);
			$item['ward'] = preg_replace('/ - .*/','',$item['ward']);
			$item['panel'] = preg_replace('/committee of adjustment - panel /','',$item['panel']);
			$item['sire_url'] = "http://app05.ottawa.ca/sirepub/item.aspx?itemid={$item['itemid']}";

			$items[] = $item;

		}

		return $items;

	}

  static public function scrapeCommitteeOfAdjustment($date,$panel,$file) {

		$txt = `pdftotext $file -`;
	  # $txt = file_get_contents($file);

		# split by PAGE_BREAK

	  $apps = array();
	  $a = array();
	  $pages = explode(chr(12),$txt); # split on CTRL-L, which PDF2TEXT puts between pages
	  foreach ($pages as $p) {
	    $p = preg_replace("/\r/","",$p);
	    $o = preg_replace("/\n/"," ",$p);
	    if (preg_match('/File No\.:/',$o) || preg_match('/File Nos\.:/',$o)) {
	      if (count($a) > 0) {
	        $apps[] = array('lines'=>$a);
	        $a = array();
	      }
	    }
	    $lines = explode("\n",$p);
	    foreach ($lines as $l) {
	      $a[] = $l;
	    }
	  }
		# last page
    $apps[] = array('lines'=>$a);

		# first one is always the agenda
		$a = array_shift($apps);
		$headtext = implode(" ",$a['lines']);
		self::scrapeCommitteeOfAdjustmentHeader($date,$panel,$headtext);

	  foreach ($apps as $a) {
	    $head = "";
	    foreach ($a['lines'] as $l) {
	      if (preg_match('/PURPOSE OF THE APPLICATION/',$l)) {
	        break;
	      }
	      $head .= $l;
	    }
	    $text =  implode(" ",$a['lines']);
	    $matches = array();
	    preg_match_all("/D\d\d-\d\d-\d\d\/.-\d\d\d\d\d/",$head,$matches);
	    foreach ($matches[0] as $d) {
				getDatabase()->execute(" update devapp set coadesc = '".preg_replace("/'/","''",implode("<br/>",$a['lines']))."' where devid = '$d' ");
	    }
	  }


	}

  static public function scrapeCommitteeOfAdjustmentHeader($date,$panel,$text) {
	  $text = preg_replace("/\t/"," ",$text);
	  $text = preg_replace("/\r/"," ",$text);
	  $text = preg_replace("/\n/"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/ (\d+)-(\d+) /"," $1 $2 ",$text);
	  $text = preg_replace("/\((\d+)\)/"," $1 ",$text);
	  $text = preg_replace("/\((\d+) /"," $1 ",$text);
	  $text = preg_replace("/ (\d+)\)/"," $1 ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);
	  $text = preg_replace("/  /"," ",$text);

	  $index = 0;

		$items = array();
	
	  $item = array();
    $item['app'] = array();
    $item['addr'] = array();
		$item['date'] = $date;
		$item['panel'] = $panel;

	  $words = explode(" ",$text);
	  for ($x = 0; $x < count($words); $x++) {
	
	    $word = $words[$x];;
	
	    $matches = array();
	
	    if (preg_match("/^(\d+)/",$word,$matches) && $matches[1] == ($index+1) ) {
	      $index = $matches[1];
	      if ($index > 1) {
	        self::dumpItem($item);
					$items[] = $item;
	      }
	      $item = array();
	      $item['app'] = array();
	      $item['addr'] = array();
				$item['date'] = $date;
				$item['panel'] = $panel;
	      # print "\n\n----- START OF APPLICATION ($index) -----\n\n";
				continue;
	    }
	
	    if (preg_match("/^(\d+)$/",$word,$matches)) {
        #print "----- $word -----\n";
	      $num = $matches[1];
	      $street = $words[$x+1];
	      array_push($item['addr'],array('num'=>$num,'street'=>$street));
	    }
	
	    if (preg_match("/^(D\d\d-\d\d-\d\d\/.-\d\d\d\d\d)/",$word,$matches)) {
	      $app = $matches[1];
	      # print "\n  application number: '$app'\n";
	      array_push($item['app'],$app);
	    }
	    # print " {$words[$x]} ";
	
	  }
	
	  # print "$text";
		# DevelopmentAppController::injestApplication($r['appid'],'notweets');

		# [{"lat":45.283326970038,"lon":-75.913048018637,"addr":"15 Huntmar Drive"}]

	  self::dumpItem($item);
		$items[] = $item;

		foreach ($items as $i) {
			#pr($i);
			foreach ($i['app'] as $devid) {
				print "devid: $devid\n";
		    getDatabase()->execute(" delete from devapp where devid = :devid ",array("devid"=>$devid));

				$addresses = array();
				foreach ($i['addr'] as $a) {
					if (preg_match('/[a-z]/',$a['street'])
						&& !in_array($a['street'],array('January','February','March','April','May','June','July','August','September','October','November','December'))
						) {
						$mm = address_latlon($a['num'],$a['street']);
						if ($mm['lat'] != '') {
							$ward = $mm['ward'];
							$addresses[] = array(
								'lat' => $mm['lat'],
								'lon' => $mm['lon'],
								'addr'=>"{$a['num']} {$a['street']}"
							);
						} else {
							$addresses[] = array(
								'addr'=>"{$a['num']} {$a['street']}"
							);
						}
					}
				}

				$ward = '';
		    $id = getDatabase()->execute(" 
		      insert into devapp 
		      (address,appid,devid,ward,apptype,receiveddate,created,updated,description)
		      values
		      (:address,:appid,:devid,:ward,:apptype,:receiveddate,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,:description)",array(
		        'devid'=> $devid,
		        'address'=> json_encode($addresses),
		        'appid'=> 'n/a',
		        'ward' => $ward,
		        'apptype' => 'coa', #$labels['Application'],
		        'receiveddate' => $i['date'],
		        'description' => "CoA {$i['date']} panel {$i['panel']}"
		    ));
	      getDatabase()->execute(" 
						insert into devappstatus (devappid,status,statusdate) values (:devappid,:status,:statusdate) 
					",array(
	        'devappid' => $id,
	        'status' => 'Appearance on CoA agenda, panel ' . $panel,
	        'statusdate' => $i['date']
	      ));
			}
		}
  }

  static public function dumpItem($item) {
		return;
    print "\n\n";
    pr($item);
    print "\n\n";
  }

  static public function viewDevApp($devid) {
		
    $a = getDatabase()->one(" select * from devapp where id = :devid or devid = :devid ",array("devid"=>$devid));
    if (!$a['id']) {
      top3();
      print "$devid not found in the database.\n";
      bottom3();
      return;
    }

    top3($a['devid'] . " - ". $a['apptype'] . " - " . $a['appid']);
    $a['address'] = json_decode($a['address']);

    ?>
    <h1><?php print $a['devid']; ?></h1>

    <div class="row">
    <div class="col-sm-6">

		<?php
		if ($a['apptype'] == 'coa') {
			# all good now!
			?>
	    <b>Committee of Adjustment application</b>
			<?php
		} else {
			?>
			<p>
	    <b><?php print $a['apptype']; ?></b>: <?php print $a['description']; ?>
	    <a target="_new" href="<?php print self::getLinkToApp($a['appid']); ?>"><i class="fa fa-external-link"></i> View application on ottawa.ca</a>
	    </p>
			<!--
			Damn REST sadface. This link doesnt work when you deep link a visitor right ot the comment page if they havent loaded some cookie crap
			from the 'main' devapp page on the city's website. GRRRRRR.
			<p>
			<a class="btn btn-primary" target="_blank" href="https://app01.ottawa.ca/postingplans/commentForm.jsf?lang=en&appId=<?php print $a['appid']; ?>&newReq=true">Send an official comment to the city regarding <?php print $a['devid']; ?></a>.
			</p>
			-->
			<?php
		}

		$streetviewImgUrl = '';
		?>

    <table class="table table-bordered table-condensed" style="width: 100%;">
    <tr><td>Ward</td><td><?php print $a['ward']; ?></td></tr>
    <tr><td>Received</td><td><?php print substr($a['receiveddate'],0,10); ?></td></tr>
    <tr><td>Updated</td><td><?php print substr($a['updated'],0,10); ?></td></tr>
    <tr><td>Address (Zoning)</td><td>
    <?php 
    foreach ($a['address'] as $addr) {
			#$sv_url = "https://maps.googleapis.com/maps/api/streetview?size=600x300&heading=1&pitch=-0.76&key=".OttWatchConfig::GOOGLE_STREETVIEW_API_KEY.".&location=".urlencode($addr->addr);
			if ($streetviewImgUrl == '') {
				$streetviewImgUrl = "https://maps.googleapis.com/maps/api/streetview?size=600x300&pitch=-0.76&key=".OttWatchConfig::GOOGLE_STREETVIEW_API_KEY."&location=".urlencode($addr->addr);
			}
      print $addr->addr;

			if (isset($addr->lat) && $addr->lat != '') {
	      $zoning = getApi()->invoke("/api/zoning/{$addr->lat}/{$addr->lon}");

	      print " (";
	      if ($zoning['ZONE_CODE'] != '') {
	        print "<a href=\"{$zoning['URL']}\">{$zoning['ZONE_CODE']}</a>";
	      } else {
	        print "zoning unknown";
	      }
	      print ")\n";
			} else {
				print " (zoning unknown)";
			}
			print "<br/>\n";
    }
    ?>
    </td></tr>
    <?php 
    $sql = " select * from devapp where id != {$a['id']} and ( ";
    $first = 1;
    foreach ($a['address'] as $addr) {
      if (!$first) {
        $sql .= " or ";
      }
      $sql .= " address like '%".mysql_escape_string($addr->addr)."%' ";
      $first = 0;
    }
    $sql .= " ) ";
    $related = array();
    if (count($a['address']) > 0) {
      $related = getDatabase()->all($sql);
    }
    if (count($related) > 0) {
      ?>
      <tr><td>Possibly related devapp(s)</td><td>
      <?php
      foreach ($related as $dd) {
        print "<a href=\"/devapps/{$dd['devid']}\">{$dd['devid']} - {$dd['apptype']}</a><br/>";
      }
      ?>
      </td></tr>
      <?php
    }
    ?>

			<?php
    $sql = " select * from bylaw where ";
    $first = 1;
    foreach ($a['address'] as $addr) {
      if (!$first) {
        $sql .= " or ";
      }
      $sql .= " summary like '%".mysql_escape_string($addr->addr)."%' ";
      $first = 0;
    }
    $related = array();
    if (count($a['address']) > 0) {
      $related = getDatabase()->all($sql);
    }
    if (count($related) > 0) {
			?>
			<tr>
				<td>Possibly related by-laws</td>
				<td>
					<?php
					foreach ($related as $r) {
						print "<a href=\"/bylaws/{$r['bylawnum']}\" target=\"_blank\">{$r['bylawnum']}</a>: {$r['summary']}<br/>";
					}
					?>
				</td>
			</tr>
			<?php
		}
			?>




		<?php if ($a['apptype'] != 'coa') { ?>
    <tr><td>Documents</td><td>
    <?php
    $docs = getDatabase()->all(" select * from devappfile where devappid = :id order by updated desc,title ",array('id'=>$a['id']));
    ?>
    <table>
      <tr>
      <th>Title</th>
      <th>Modified</th>
      </tr>
    <?php
    foreach ($docs as $d) {
      $doctitle = $d['title'];
      $doctitle = preg_replace("/{$a['devid']}/","",$doctitle);
      $doctitle = preg_replace("/^ *- */","",$doctitle);
      $doctitle = preg_replace("/  /"," ",$doctitle);
      $doctitle = preg_replace("/  /"," ",$doctitle);
      $doctitle = preg_replace("/  /"," ",$doctitle);
      ?>
      <tr>
      <td><a target="_blank" href="<?php print $d['href']; ?>"><?php print $doctitle; ?></a></td>
      <td><nobr><?php print substr($d['updated'],0,10); ?></nobr></td>
      </tr>
      <?php
    }

    ?>
    </table>
    </td></tr>
		<?php } else { ?>
		<tr>
			<td colspan="2">
			<center><b>Agenda Excerpt</b></center>
			<p><?php print $a['coadesc']; ?></p>
			</td>
		</tr>
		<?php } ?>
    <tr>
    <th style="text-align: center;">Date</th>
    <th style="text-align: center;">Status</th>
    </tr>
    <?php
    $dates = getDatabase()->all(" select date(statusdate) statusdate,status from devappstatus where devappid = :id order by statusdate desc ",array('id'=>$a['id']));
    foreach ($dates as $d) {
      ?>
      <tr>
      <td style="white-space: nowrap;"><?php print $d['statusdate']; ?></td>
      <td><?php print $d['status']; ?></td>
      </tr>
      <?php
    }
    ?>
    </table>

    </div>

    <div class="col-sm-6">
    <?php 

		print "<p><img src=\"$streetviewImgUrl\" class=\"img-responsive responsive\"/></p>";

    $a = $a['address'];
    if ($a && count($a>0)) {
    ?>
    <div id="map_canvas" style="width:100%; height:600px;"></div>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
    <script>
      $(document).ready(function() {
        var mapOptions = { 
          center: new google.maps.LatLng(45.420833,-75.69), 
          zoom: 16, 
          mapTypeId: google.maps.MapTypeId.ROADMAP 
        };
        map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
        <?php
        foreach ($a as $addr) {
					if ($addr->lat == '') { continue; }
	        ?>
	        var myLatlng = new google.maps.LatLng(<?php print $addr->lat; ?>,<?php print $addr->lon; ?>);
	        var marker = new google.maps.Marker({ position: myLatlng, map: map, title: '<?php print preg_replace("/'/","",($addr->addr)); ?>' }); 
	        map.panTo(myLatlng);
	        <?php 
        }
        ?>
      });
    </script>
    <?php 
    } else {
      print "<center><i>No map because no addresses were detected in the development application.</i></center>";
    }
    ?>
    </div>

    </div><!-- row -->

		<div id="comments">
    <?php disqus(); ?>
		</div>
    <?php

    bottom();
  }

  static public function listAll() {
    top();

    $match = $_GET['match'];
    $since = $_GET['since'];
    if ($since == '') {
      $since = 7;
    }

    $matchWhere = '';
    if ($match != '') {
      $safe = mysql_escape_string($match);
      $matchWhere = " ( ";
      $matchWhere .= " devid like '%$safe%' ";
      $matchWhere .= " or ward like '%$safe%' ";
#      $matchWhere .= " or status like '%$safe%' ";
      $matchWhere .= " or address like '%$safe%' ";
      $matchWhere .= " or description like '%$safe%' ";
      $matchWhere .= " or coadesc like '%$safe%' ";
      $matchWhere .= " ) and ";
    }

    if (preg_match("/^\d\d\d\d-\d\d-\d\d$/",$since)) {
      $apps = getDatabase()->all(" select * from devapp where $matchWhere updated >= '$since' order by updated desc ");
      $sinceDisplay = $since;
    } else if ($since == '' || preg_match("/^\d+$/",$since)) {
      $apps = getDatabase()->all(" select * from devapp where $matchWhere updated >= DATE_SUB(NOW(), INTERVAL $since day) order by updated desc ");
      $sinceDisplay = "$since days ago";
    } else {
      # malformed since
      print "The 'since' value is malformed; query aborted\n";
      bottom();
      return;
    }

    ?>

    <h1>Development Applications</h1>

    <div class="row-fluid">

    <div class="span5">

    Displaying <b><?php print count($apps); ?></b> applications updated since <?php print $sinceDisplay; ?>
    <p/>
    <script>

    function doFilter(reset) {
      since = $('#filterSinceValue').val();
      match = $('#filterMatchValue').val();
      if (reset) {
        since = '';
        match = '';
      }
      document.location.href = '?since='+since+'&match='+match;
    }
    </script>

    <form class="form-inline">
    Search for
    <input id="filterMatchValue" type="text" placeholder="streetname..." value="<?php print $match; ?>" style="width: 100px;">
    since
    <input id="filterSinceValue" type="text" placeholder="7" value="<?php print $since; ?>" style="width: 50px;"> days ago
    <button class="btn btn-primary" type="button" onclick="doFilter()"><i class="icon-search"></i> Go</button>
    <button class="btn" type="button" onclick="doFilter(1)"> Reset</button>
    </form>

    <!--
    <div class="input-prepend">
    <span class="add-on">Contains:</span>
    <span class="add-on">Updated Since:</span>
    </div>

    <div class="input-prepend">
    <input id="filterSinceValue" type="text" name="" placeholder="yyyy-mm-dd or 'X' for 'days-ago'" value="<?php print $since; ?>">
    </div>
    -->

    <div style="overflow:scroll; height: 500px;">
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <tr>
    <th>Application #<br/>Updated</th>
    <th>Application Type and Status</th>
    <th>Address(es)</th>
    </tr>
    <?php
    foreach ($apps as $a) {
      # $url = self::getLinkToApp($a['appid']);
      $url = OttWatchConfig::WWW . "/devapps/" . urlencode($a['devid']); # self::getLinkToApp($a['appid']);
      # double load for the status and date
      $status = getDatabase()->one(" select max(id) id from devappstatus where devappid = :id ",array('id'=>$a['id']));
      $status = getDatabase()->one(" select * from devappstatus where id = :id ",array('id'=>$status['id']));

      # $url = OttWatchConfig::WWW."/devapps/{$a['devid']}";
      ?>
      <tr>
      <td><b><nobr><a href="<?php print $url; ?>"><?php print $a['devid']; ?></a></nobr></b><br/>
      <nobr><?php print strftime("%Y-%m-%d",strtotime($status['statusdate'])); ?> updated</nobr><br/>
      <nobr><?php print strftime("%Y-%m-%d",strtotime($a['receiveddate'])); ?> started</nobr></td>
      <td>
      <b><?php print $a['apptype']; ?></b><br/>
      <?php 
      print "<i>".$status['status']."</i><br/>";
      print $a['description'];
      ?></td>
      <td>
      <?php
      $addr = json_decode($a['address']);
      foreach ($addr as $t) {
        if ($t->lat != '') {
          print "<nobr><a href=\"javascript:panMapTo({$t->lat},{$t->lon});\">{$t->addr}</a></nobr><br/>\n";
        } else {
          print "<nobr><a target=\"_blank\" href=\"http://maps.google.com/?q={$t->lat},{$t->lon}\">{$t->addr}</a></nobr><br/>\n";
        }
      }
      ?>
      </td>
      </tr>
      <?php
    }
    ?>
    </table>
    </div><!-- overflow -->
    </div><!-- span -->

    <div class="span7">
    <div id="map_canvas" style="width:100%; height:600px;"></div>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
    <script>

      $(document).ready(function() {
        var mapOptions = { 
          center: new google.maps.LatLng(45.420833,-75.69), 
          zoom: 12, 
          mapTypeId: google.maps.MapTypeId.ROADMAP 
        };
        map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);

        <?php
        foreach ($apps as $a) {

		      # double load for the status and date
		      $status = getDatabase()->one(" select max(id) id from devappstatus where devappid = :id ",array('id'=>$a['id']));
		      $status = getDatabase()->one(" select * from devappstatus where id = :id ",array('id'=>$status['id']));

          # $url = self::getLinkToApp($a['appid']);
          $url = OttWatchConfig::WWW . "/devapps/{$a['devid']}"; # self::getLinkToApp($a['appid']);

					# pick the address that has lat/lon data too
          $addr_arr = json_decode($a['address']);
					$addr = array();
					foreach ($addr_arr as $tmp) {
						if ($tmp->lat != '') {
							$addr = $tmp;
						}
					}
          if (count($addr) == 0) {
            continue;
          }
          ?>

	        var contentString<?php print $a['id']; ?> = 
            '<div>' + 
            '<b><a target="_blank" href="<?php print $url; ?>"><?php print $a['devid']; ?></a>: ' +
            '<?php print $a['apptype']; ?></b><br/>' +
            '<?php print $status['status']; ?><br/>' +
            'Updated: <?php print strftime("%Y-%m-%d",strtotime($status['statusdate'])); ?><br/>' +
            '<br/>' +
            '<i>' +
            '<?php print preg_replace("/'/",'"',$a['description']); ?>' +
            '</i>' +
            '</div>';
	        var infowindow<?php print $a['id']; ?> = new google.maps.InfoWindow({ content: contentString<?php print $a['id']; ?> });

          <?php
          # not all addresses have lat/lon
          $lat = $addr->lat;
          $lon = $addr->lon;
          $address = "{$addr->addr}, Ottawa, Ontario";
          if ($lat != '') {
            ?>
  	        var myLatlng<?php print $a['id']; ?> = new google.maps.LatLng(<?php print $lat; ?>,<?php print $lon; ?>);
		        var marker<?php print $a['id']; ?> = new google.maps.Marker({ position: myLatlng<?php print $a['id']; ?>, map: map, title: '<?php print $a['devid']; ?>' }); 
		        google.maps.event.addListener(marker<?php print $a['id']; ?>, 'click', function() {
		          infowindow<?php print $a['id']; ?>.open(map,marker<?php print $a['id']; ?>);
		        });
            <?php
          } else {
            ?>
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode( { 'address': '<?php print $address; ?>'}, function(results, status) {
              if (status == google.maps.GeocoderStatus.OK) {
		  	        var myLatlng<?php print $a['id']; ?> = new google.maps.LatLng(results[0].geometry.location.mb,results[0].geometry.location.nb);
				        var marker<?php print $a['id']; ?> = new google.maps.Marker({ position: myLatlng<?php print $a['id']; ?>, map: map, title: '<?php print $a['devid']; ?>' }); 
				        google.maps.event.addListener(marker<?php print $a['id']; ?>, 'click', function() {
				          infowindow<?php print $a['id']; ?>.open(map,marker<?php print $a['id']; ?>);
                });
              }
            });
            <?php
          }
        }
        ?>

        // map is in place and ready, panTo if user allows GEO location permission
		    if (navigator.geolocation) {
		      navigator.geolocation.getCurrentPosition(function(position) {
		        var newLatlng = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
		        map.panTo(newLatlng);
		      });
		    }

      });

      function panMapTo(lat,lon) {
        map.panTo(new google.maps.LatLng(lat,lon));
      }

    </script>
    </div>

    </div><!-- row -->
    <?php
    bottom();
  }

  static public function getLinkToApp($appid) {
    return "http://app01.ottawa.ca/postingplans/appDetails.jsf?lang=en&appId=$appid";
  }

  static public function scanDevApps() {

    # get dev-apps sorted by status update.
    # results are sorted with oldtest date first, so then jump to last page, and start scanning backwards
    # until no dates on page are "new"
    $html = @file_get_contents('http://app01.ottawa.ca/postingplans/searchResults.jsf?lang=en&newReq=true&action=sort&sortField=objectCurrentStatusDate&keyword=.');
		if (strlen($html) == 0) {
			return;
		}
    #file_put_contents("t.html",$html);
    #$html = file_get_contents("t.html");

    # parse out all of the pages of results
    $lines = explode("\n",$html);
    $add = 0;
    $span = "";
    foreach ($lines as $l) {
      if (preg_match("/span/",$l)) {
        if ($add) {
          $span = $l;
          break;
        }
      }
      if (preg_match("/searchpaging/",$l)) {
        $add = 1;
      }
    }

    $data = explode("<a",$span);
    $pages = array();
    foreach ($data as $d) {
      if (preg_match('/page=(\d+)"/',$d,$match)) {
        $pages[$match[1]] = 1;
      }
    }
    $pages = array_keys($pages);
    $pages = array_reverse($pages);

    # obtain all search results until a page has no relatively new DevApps
    foreach ($pages as $p) {
      $changed = 0;
      $url="http://app01.ottawa.ca/postingplans/searchResults.jsf?lang=en&action=sort&sortField=objectCurrentStatusDate&keyword=.&page=$p";
      $html = @file_get_contents($url);
      if (strlen($html) == 0) {
        # http failure; no worries; will be back soon.
        continue;
      }
      #file_put_contents("p.html",$html);
      #$html = file_get_contents("p.html");
      $lines = explode("\n",$html); 
      foreach ($lines as $l) {
        # <a href="appDetails.jsf;jsessionid=D49D6B525184BD8711CED3AFDE61A2D2?lang=en&appId=__866MYU" class="app_applicationlink">D01-01-12-0006           </a>
        $matches = array();
        if (preg_match('/appDetails.jsf.*appId=([^"]+)".*>(D[^ <]+)/',$l,$matches)) {
          $appid = $matches[1];
          $devid = $matches[2];
        }
        if (preg_match('/<td class="subRowGray15">(.*)</',$l,$matches)) {
          $statusdate = $matches[1];
          $statusdate = strftime("%Y-%m-%d",strtotime($statusdate));
          $app = getDatabase()->one(" 
            select devappid id,date(max(statusdate)) statusdate from devappstatus where devappid = (select id from devapp where appid = :appid)
            ",array("appid"=>$appid));
          $action = '';
          if ($app['id']) {
            if ($app['statusdate'] != $statusdate) {
              $changed = 1;
              self::injestApplication($appid,'update');
            }
          } else {
            $changed = 1;
            self::injestApplication($appid,'insert');
          }
        }
      }
      if (! $changed) {
        # nothing changed on this search results page;
        # no need to keep going on other serach pages
        break;
      }
    }

  }

  static function injestApplication ($appid,$action) {

		# get GIS data for it
    $addresses = array();

		$url = 'http://maps.ottawa.ca/arcgis/rest/services/Development_Applications/MapServer/0/query';
		$url .= '?where=DT_APP_ID+%3D+%27'.urlencode($appid).'%27';
		$url .= '&outFields=OBJECTID%2C+LATITUDE%2CLONGITUDE%2CADDRESS_NUMBER_ROAD_NAME';
		$url .= '&f=pjson';
		$gis_json = file_get_contents($url);
		$gis = json_decode($gis_json);
		$addrSeen = array();
		foreach ($gis->features as $f) {
			$lat = $f->attributes->LATITUDE;
			$lon = $f->attributes->LONGITUDE;
			$roadname = $f->attributes->ADDRESS_NUMBER_ROAD_NAME;
			if (!isset($addrSeen[$roadname])) {
			$addr = array();
			$addr['lat'] = $lat;
			$addr['lon'] = $lon;
			$addr['addr'] = $roadname;
			$addresses[] = $addr;
			$addrSeen[$roadname] = 1;
			}
		}

    $url = "http://app01.ottawa.ca/postingplans/appDetails.jsf?lang=en&appId=$appid";
    $html = file_get_contents($url);
    #file_put_contents("a.html",$html);
    #$html = file_get_contents("a.html");
    $html = preg_replace("/&nbsp;/"," ",$html);
    $html = preg_replace("/\r/"," ",$html);
    $lines = explode("\n",$html);

		$labels = array();
		$labels['Application #'] = '';
		$labels['Date Received'] = '';
		#$labels['Address'] = '';
		$labels['Ward'] = '';
		$labels['Application'] = '';
		$labels['Review Status'] = '';
		$labels['Status Date'] = '';
		$labels['Description'] = '';

    $files = array();

		$doAddr = 1;
		if (count($addresses) > 0) {
			# only try to get addresses out of the description if the GIS lookup failed.
			# GIS data, when present, is much better.
			$doAddr = 0;
		}

    $label = '';
    $value = '';
    for ($x = 0; $x < count($lines); $x++) {
      $matches = array();
      if (preg_match('/maps.ottawa.*center=([^&]+).*;(-*\d+\.\d+)&scale.*geoottawa">([^<]+)</',$lines[$x],$matches)) {
				# <li><a href="http://maps.ottawa.ca/geoOttawa/?center=-8452764.23&#44;5669659.81&scale=2000&featname=370%20Huntmar%20Drive&amp;lang=en" target="_geoottawa">370 Huntmar Drive</a></li> 
        # get lat/lon from mercator
        $addr = mercatorToLatLon($matches[1],$matches[2]);
        $addr['addr'] = $matches[3];
				if ($doAddr) { $addresses[] = $addr; }
			} else if (preg_match('/apps104.*LAT=([-\d\.]+).*LON=([-\d\.]+).*>([^<]+)</',$lines[$x],$matches)) {
        # <li><a href="http://apps104.ottawa.ca/emap?emapver=lite&LAT=45.278462&LON=-75.570191&featname=5640+Bank+Street&amp;lang=en" target="_emap">5640 Bank Street</a></li>
        $addr = array();
        $addr['lat'] = $matches[1];
        $addr['lon'] = $matches[2];
        $addr['addr'] = $matches[3];
        if ($doAddr) { $addresses[] = $addr; }
        #$addresses[0] = $matches[1];
      } else if (preg_match('/<a.*target="_emap">([^<]+)</',$lines[$x],$matches)) {
        # <li><a href="http://apps104.ottawa.ca/emap?emapver=lite&amp;lang=en" target="_emap">114 Richmond Road</a></li>
        $addr = array();
        $addr['addr'] = $matches[1];
        if ($doAddr) { $addresses[] = $addr; }
      }
      if (preg_match('/div.*class="label"/',$lines[$x])) {
        $x++;
        $label = self::suckToNextDiv($lines,$x);
        $label = trim($label);
      }
      if (preg_match('/div.*class="appDetailValue"/',$lines[$x])) {
        $x++;
        $value = self::suckToNextDiv($lines,$x);
        $value = preg_replace('/\s+/',' ',$value);
        if (array_key_exists($label,$labels)) {
          $labels[$label] = $value;
        }
      }
      if (preg_match('/main:content:supportingDocLink.*href="([^"]+)".*title="([^"]+)"/',$lines[$x],$matches)) {
        $file = array();
        $file['href'] = $matches[1];
        $file['title'] = $matches[2];
        $files[] = $file;
      }

    }

    $labels['status_date'] = strftime('%Y-%m-%d',strtotime($labels['Status Date']));
    unset($labels['Status Date']);
    $labels['date_received'] = strftime('%Y-%m-%d',strtotime($labels['Date Received']));
    unset($labels['Date Received']);

    if (strlen($labels['Description']) > 2040) {
      $labels['Description'] = substr($labels['Description'],2040);
    }

    $row = getDatabase()->one(" select * from devapp where appid = :appid ",array("appid"=>$appid));
    if ($row['id']) {
      $id = $row['id'];
      try {
		    getDatabase()->execute(" 
		      update devapp set 
	          address = :address,
	          devid = :devid,
	          ward = :ward,
	          apptype = :apptype,
	          receiveddate = :receiveddate,
	          updated = CURRENT_TIMESTAMP,
	          description = :description
	        where appid = :appid
	        ",array(
		        'address'=> json_encode($addresses),
		        'description' =>$labels['Description'],
	          'devid' => $labels['Application #'],
	          'ward' => $labels['Ward'],
	          'receiveddate' => $labels['date_received'],
	          'apptype' => $labels['Application'],
		        'appid'=> $appid,
		    ));
      } catch (Exception $e) {
				print $e;
				pr(array(
		        'address'=> json_encode($addresses),
		        'description' =>$labels['Description'],
	          'devid' => $labels['Application #'],
	          'ward' => $labels['Ward'],
	          'receiveddate' => $labels['date_received'],
	          'apptype' => $labels['Application'],
		        'appid'=> $appid,
		    ));
			}
      try {
	      getDatabase()->execute(" insert into devappstatus (devappid,status,statusdate) values (:devappid,:status,:statusdate) ",array(
	        'devappid' => $id,
	        'status' => $labels['Review Status'],
	        'statusdate' => $labels['status_date'],
	      ));
      } catch (Exception $e) {
        # duplicate key is expected
				$action = 'notweet';
        if (!preg_match('/devappstatus_in1/',$e)) {
          throw($e);
        }
      }
    } else {
	    $id = getDatabase()->execute(" 
	      insert into devapp 
	      (address,appid,devid,ward,apptype,receiveddate,created,updated,description)
	      values
	      (:address,:appid,:devid,:ward,:apptype,:receiveddate,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,:description)",array(
	        'devid'=> $labels['Application #'],
	        'address'=> json_encode($addresses),
	        'appid'=> $appid,
	        'ward' => $labels['Ward'],
	        'apptype' => $labels['Application'],
	        'receiveddate' =>$labels['date_received'],
	        'description' =>$labels['Description'],
	    ));
      getDatabase()->execute(" insert into devappstatus (devappid,status,statusdate) values (:devappid,:status,:statusdate) ",array(
        'devappid' => $id,
        'status' => $labels['Review Status'],
        'statusdate' => $labels['status_date'],
      ));
    }


    getDatabase()->execute(" update devappfile set deleted = CURRENT_TIMESTAMP where deleted is null and devappid = :devappid ",array( 'devappid' => $id,));
    #getDatabase()->execute(" delete from devappfile where devappid = :devappid ",array( 'devappid' => $id,));

		$touched = array();
    foreach ($files as $f) {
			if (isset($touched[$f['href']])) {
				continue;
			}
			$touched[$f['href']] = 1;

			$exists = getDatabase()->one(" select count(1) c from devappfile where devappid = :devappid and href = :href ",array(
        'devappid' => $id,
        'href' => $f['href']
			));

			if ($exists['c'] == 0) {
				# insert
      	getDatabase()->execute(" insert into devappfile (devappid,href,title,created,updated) values (:devappid,:href,:title,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP) ",array(
	        'devappid' => $id,
	        'href' => $f['href'],
	        'title' => $f['title'],
	      ));
			} else {
				# update
	      getDatabase()->execute(" update devappfile set deleted = null where href = :href  ",array( 'href' => $f['href']));
			}

			$meta = `HEAD {$f['href']}`;
			$meta = explode("\n",$meta);
			$lastmodified = preg_grep('/^Last-Modified: /',$meta);
			if (count($lastmodified) > 0) {
				$lastmodified = array_shift($lastmodified);
				$lastmodified = preg_replace('/Last-Modified: /','',$lastmodified);
				$lastmodified = strtotime($lastmodified);
				$lastmodified = date("Y-m-d",$lastmodified);
				if (preg_match('/^2\d\d\d-\d\d-\d\d$/',$lastmodified)) {
					# update 'UPDATED' to reflect file time.
		      getDatabase()->execute(" update devappfile set updated = :updated where href = :href  ",array(
		        'href' => $f['href'],
						'updated' => $lastmodified
		      ));
				}
			}

    }

    $ward = $labels['Ward'];
    $ward = explode(" - ",$ward);
    $ward = "{$ward[0]}, {$ward[1]}";

    $url = "http://app01.ottawa.ca/postingplans/appDetails.jsf?lang=en&appId=$appid";
    $url = OttWatchConfig::WWW . "/devapps/{$labels['Application #']}"; # self::getLinkToApp($a['appid']);

		$addr = '';
		if (count($addresses) > 0) {
			$addr = $addresses[0];
			$addr = $addr['addr'];
		}

    if ($action == 'insert') {
      $tweet = "NEW {$labels['Application']}: ".$addr." {$labels['Application #']} in $ward";
    } else if ($action == 'update') {
      $tweet = "Changed {$labels['Application']}: ".$addr." {$labels['Application #']} in $ward";
    } else {
			# no tweeting!
			return;
		}

    # new style
    $message = $tweet;
    $path = "/devapps/{$labels['Application #']}";
    syndicate($message,$path);
    # old style
    # $newtweet = tweet_txt_and_url($tweet,$url);
		# tweet($newtweet);
  }

  static function suckToNextDiv ($lines,$x) {
        $snippet = '';
        while (!preg_match('/div>/',$lines[$x])) {
          $snippet .= $lines[$x];
          $x++;
        }
        $snippet = preg_replace("/:/","",$snippet);
        $snippet = preg_replace("/ +/"," ",$snippet);
        $snippet = preg_replace("/^\s+/","",$snippet);
        $snippet = preg_replace("/\s$/","",$snippet);
        return $snippet;
  }


  
}
