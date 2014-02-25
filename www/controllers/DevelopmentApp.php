<?php

class DevelopmentAppController {

  static public function viewDevApp($devid) {
    $a = getDatabase()->one(" select * from devapp where devid = :devid ",array("devid"=>$devid));
    if (!$a['id']) {
      top();
      print "$devid not found in the database.\n";
      bottom();
      return;
    }
    top($a['devid'] . " - ". $a['apptype'] . " - " . $a['appid']);

    #$a['ward'] = preg_replace("/Ward /","",$a['ward']);
    #$a['ward'] = preg_replace("/ .*/","",$a['ward']);
    $a['address'] = json_decode($a['address']);

    ?>
    <h1><?php print $a['devid']; ?></h1>

    <div class="row-fluid">
    <div class="span6">

    <div class="pull-right"><?php renderShareLinks("{$a['devid']}","/devapps/{$a['devid']}"); ?></div>
    <p>
    <b><?php print $a['apptype']; ?></b>: <?php print $a['description']; ?>
    </p>
    <p>
    <a target="_new" href="<?php print self::getLinkToApp($a['appid']); ?>"><i class="icon-share-alt"></i> View application on ottawa.ca</a>
    </p>

    <table class="table table-bordered table-condensed" style="width: 100%;">
    <tr><td>Ward</td><td><?php print $a['ward']; ?></td></tr>
    <tr><td>Received</td><td><?php print substr($a['receiveddate'],0,10); ?></td></tr>
    <tr><td>Updated</td><td><?php print substr($a['updated'],0,10); ?></td></tr>
    <tr><td>Address (Zoning)</td><td>
    <?php 
    foreach ($a['address'] as $addr) {
      print $addr->addr;
			if (isset($addr->lat)) {
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
      $sql .= " address like '%{$addr->addr}%' ";
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
        print "<a href=\"{$dd['devid']}\">{$dd['devid']} - {$dd['apptype']}</a><br/>";
      }
      ?>
      </td></tr>
      <?php
    }
    ?>
    <tr><td>Documents</td><td>
    <?php
    $docs = getDatabase()->all(" select * from devappfile where devappid = :id order by updated desc,title ",array('id'=>$a['id']));
    ?>
    <table class="table table-condensed">
      <tr>
      <th>Title</th>
      <th>Modified</th>
      </tr>
    <?php
    foreach ($docs as $d) {
      $doctitle = $d['title'];
      $doctitle = preg_replace("/{$a['devid']}/","",$doctitle);
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
    <tr>
    <th style="text-align: center;">Date</th>
    <th style="text-align: center;">Status</th>
    </tr>
    <?php
    $dates = getDatabase()->all(" select date(statusdate) statusdate,status from devappstatus where devappid = :id order by statusdate desc ",array('id'=>$a['id']));
    foreach ($dates as $d) {
      ?>
      <tr>
      <td><?php print $d['statusdate']; ?></td>
      <td><?php print $d['status']; ?></td>
      </tr>
      <?php
    }
    ?>
    </table>

    <?php
    disqus();
    ?>
    </div>

    <div class="span6">
    <?php
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
	        ?>
	        var myLatlng = new google.maps.LatLng(<?php print $addr->lat; ?>,<?php print $addr->lon; ?>);
	        var marker = new google.maps.Marker({ position: myLatlng, map: map, title: '<?php print $addr->addr; ?>' }); 
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
      $url = OttWatchConfig::WWW . "/devapps/{$a['devid']}"; # self::getLinkToApp($a['appid']);

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
          $addr = json_decode($a['address']);
          $addr = $addr[0];
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

    $addresses = array();
    $files = array();

    $label = '';
    $value = '';
    for ($x = 0; $x < count($lines); $x++) {
      $matches = array();
      if (preg_match('/maps.ottawa.*center=([^&]+).*;(-*\d+\.\d+)&scale.*geoottawa">([^<]+)</',$lines[$x],$matches)) {
				# <li><a href="http://maps.ottawa.ca/geoOttawa/?center=-8452764.23&#44;5669659.81&scale=2000&featname=370%20Huntmar%20Drive&amp;lang=en" target="_geoottawa">370 Huntmar Drive</a></li> 
        # get lat/lon from mercator
        $addr = mercatorToLatLon($matches[1],$matches[2]);
        $addr['addr'] = $matches[3];
        $addresses[] = $addr;
			} else if (preg_match('/apps104.*LAT=([-\d\.]+).*LON=([-\d\.]+).*>([^<]+)</',$lines[$x],$matches)) {
        # <li><a href="http://apps104.ottawa.ca/emap?emapver=lite&LAT=45.278462&LON=-75.570191&featname=5640+Bank+Street&amp;lang=en" target="_emap">5640 Bank Street</a></li>
        $addr = array();
        $addr['lat'] = $matches[1];
        $addr['lon'] = $matches[2];
        $addr['addr'] = $matches[3];
        $addresses[] = $addr;
        #$addresses[0] = $matches[1];
      } else if (preg_match('/<a.*target="_emap">([^<]+)</',$lines[$x],$matches)) {
        # <li><a href="http://apps104.ottawa.ca/emap?emapver=lite&amp;lang=en" target="_emap">114 Richmond Road</a></li>
        $addr = array();
        $addr['addr'] = $matches[1];
        $addresses[] = $addr;
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

    getDatabase()->execute(" delete from devappfile where devappid = :devappid ",array( 'devappid' => $id,));
		$touched = array();
    foreach ($files as $f) {
			if (isset($touched[$f['href']])) {
				continue;
			}
			$touched[$f['href']] = 1;
      getDatabase()->execute(" insert into devappfile (devappid,href,title,created,updated) values (:devappid,:href,:title,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP) ",array(
        'devappid' => $id,
        'href' => $f['href'],
        'title' => $f['title'],
      ));
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
      $tweet = "Updated {$labels['Application']}: ".$addr." {$labels['Application #']} in $ward";
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

?>
