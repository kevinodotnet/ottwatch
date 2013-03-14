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
    top();

    pr($a);

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
      $matchWhere .= " or status like '%$safe%' ";
      $matchWhere .= " or address like '%$safe%' ";
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
    function filterMatch() {
      match = $('#filterMatchValue').val();
      document.location.href = '?match='+match;
    }
    function filterSince() {
      since = $('#filterSinceValue').val();
      document.location.href = '?since='+since;
    }
    </script>

    <div class="input-prepend input-append">
    <span class="add-on">Contains:</span>
    <input id="filterMatchValue" class="span10" type="text" name="" placeholder="Streetname..." value="<?php print $match; ?>">
    <button class="btn" type="button" onclick="filterMatch()">Search</button>
    </div>

    <div class="input-prepend input-append">
    <span class="add-on">Updated Since:</span>
    <input id="filterSinceValue" class="span10" type="text" name="" placeholder="yyyy-mm-dd or 'X' for 'days-ago'" value="<?php print $since; ?>">
    <button class="btn" type="button" onclick="filterSince()">Limit</button>
    </div>

    <div style="overflow:scroll; height: 500px;">
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <tr>
    <th>Application #<br/>Updated</th>
    <th>Application Type and Status</th>
    <th>Address(es)</th>
    </tr>
    <?php
    foreach ($apps as $a) {
      $url = self::getLinkToApp($a['appid']);
      # $url = OttWatchConfig::WWW."/devapps/{$a['devid']}";
      ?>
      <tr>
      <td><b><nobr><a href="<?php print $url; ?>"><?php print $a['devid']; ?></a></nobr></b><br/>
      <nobr><?php print strftime("%Y-%m-%d",strtotime($a['statusdate'])); ?> updated</nobr><br/>
      <nobr><?php print strftime("%Y-%m-%d",strtotime($a['receiveddate'])); ?> started</nobr></td>
      <td><b><?php print $a['apptype']; ?></b><br/>
      <?php print $a['status']; ?></td>
      <td>
      <?php
      $addr = json_decode($a['address']);
      foreach ($addr as $t) {
        print "<nobr><a target=\"_blank\" href=\"http://maps.google.com/?q={$t->lat},{$t->lon}\">{$t->addr}</a></nobr><br/>\n";
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
    <script>
      $(document).ready(function() {
        var mapOptions = { center: new google.maps.LatLng(45.420833,-75.69), zoom: 8, mapTypeId: google.maps.MapTypeId.ROADMAP };
        var map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);

        <?php
        foreach ($apps as $a) {
          $url = self::getLinkToApp($a['appid']);
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
            '<?php print $a['status']; ?><br/>' +
            'Updated: <?php print strftime("%Y-%m-%d",strtotime($a['statusdate'])); ?>' +
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
      });
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
    $html = file_get_contents('http://app01.ottawa.ca/postingplans/searchResults.jsf?lang=en&newReq=true&action=sort&sortField=objectCurrentStatusDate&keyword=.');
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
      $html = file_get_contents($url);
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
          $app = getDatabase()->one(" select id,date(statusdate) statusdate from devapp where appid = :appid ",array("appid"=>$appid));
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
    print "injestApplication($appid,$action)\n";
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
      if (preg_match('/apps104.*LAT=([-\d\.]+).*LON=([-\d\.]+).*>([^<]+)</',$lines[$x],$matches)) {
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
      }
      if (preg_match('/div.*class="appDetailValue"/',$lines[$x])) {
        $x++;
        $value = self::suckToNextDiv($lines,$x);
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

    getDatabase()->execute(" delete from devapp where appid = :appid ",array("appid"=>$appid));
    $id = getDatabase()->execute(" 
      insert into devapp 
      (address,appid,devid,ward,apptype,status,statusdate,receiveddate,created,updated)
      values
      (:address,:appid,:devid,:ward,:apptype,:status,:statusdate,:receiveddate,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)",array(
        'devid'=> $labels['Application #'],
        'address'=> json_encode($addresses),
        'appid'=> $appid,
        'ward' => $labels['Ward'],
        'apptype' => $labels['Application'],
        'status' => $labels['Review Status'],
        'statusdate' => $labels['status_date'],
        'receiveddate' =>$labels['date_received'],
    ));

    foreach ($files as $f) {
      getDatabase()->execute(" insert into devappfile (devappid,href,title,created,updated) values (:devappid,:href,:title,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP) ",array(
        'devappid' => $id,
        'href' => $f['href'],
        'title' => $f['title'],
      ));
    }

    $ward = $labels['Ward'];
    $ward = explode(" - ",$ward);
    $ward = "{$ward[0]}, {$ward[1]}";

    $url = "http://app01.ottawa.ca/postingplans/appDetails.jsf?lang=en&appId=$appid";

		$addr = '';
		if (count($addresses) > 0) {
			$addr = $addresses[0];
			$addr = $addr['addr'];
		}

    if ($action == 'insert') {
      $tweet = "New {$labels['Application']}: ".$addr." {$labels['Application #']} in $ward";
    } else if ($action == 'update') {
      $tweet = "Updated {$labels['Application']}: ".$addr." {$labels['Application #']} in $ward";
    } else {
			# no tweeting!
			return;
		}

		# allow dups because a devapp will be updated multiple times
    $newtweet = tweet_txt_and_url($tweet,$url);
		print "$newtweet\n";
		tweet($newtweet,1);
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
