<?php

date_default_timezone_set("Canada/Eastern");

if (!@include_once('config.php')) {
  print "FATAL ERROR: config.php not found. Did you forget to take config-sample.php and make your own config.php?\n";
  exit(1);
}

if (1) {
  # avoids global vars name collissions
  $base = dirname(__FILE__).'/..';
  set_include_path(get_include_path() . PATH_SEPARATOR . "$base/lib");
  set_include_path(get_include_path() . PATH_SEPARATOR . "$base/www");
  set_include_path(get_include_path() . PATH_SEPARATOR . "$base/www/epiphany/src");
  set_include_path(get_include_path() . PATH_SEPARATOR . "$base/lib/phpexcel");

	include_once 'bitly.php';
	include_once 'Epi.php';

	# Prepare the database classes
	Epi::setPath('base', "$base/www/epiphany/src");
	Epi::init('database');
	Epi::setSetting('exceptions', true);
	EpiDatabase::employ(OttWatchConfig::DB_TYPE, OttWatchConfig::DB_NAME, OttWatchConfig::DB_HOST, OttWatchConfig::DB_USER, OttWatchConfig::DB_PASS);

	include_once 'controllers/EventController.php';
	include_once 'controllers/MeetingController.php';
	include_once 'controllers/Ott311Controller.php';
	include_once 'controllers/DevelopmentApp.php';
	include_once 'controllers/LobbyistController.php';
	include_once 'controllers/ConsultationController.php';
	include_once 'controllers/SyndicationController.php';
	include_once 'controllers/OpenDataController.php';
	include_once 'controllers/MfippaController.php';

	#include_once('EpiDatabase.php');
	#require_once('EpiException.php');
	
}

# Location of state files
$OTTVAR="/mnt/shared/ottwatch/var";

# URL of Lobby Registry search service
$OTT_LOBBY_SEARCH_URL="https://apps107.ottawa.ca/LobbyistRegistry/search/searchlobbyist.aspx?lang=en";

# HTTP address of OttWatch itself.
$OTT_WWW = OttWatchConfig::WWW;

function rowsToTable($rows) {
	$cols = array();
	foreach ($rows as $r) {
		foreach ($r as $k => $v) {
			if (!in_array($k,$cols)) {
				$cols[] = $k;
			}
		}
	}
	?>
	<table class="table table-bordered table-hover table-condensed">
	<tr>
		<th>#</th>
		<?php
		foreach ($cols as $c) {
			print "<th>$c</th>\n";
		}
		?>
	</tr>
	<?php
	$x = 0;
	foreach ($rows as $r) {
		print "<tr><td>$x</td>\n";
		foreach ($cols as $c) {
			print "<td>{$r[$c]}</td>";
		}
		print "</tr>";
		$x++;
	}
	?>
	</table>
	<?php
}

function meeting_category_to_title($category) {
  $row = getDatabase()->one(" select * from category where category = :category ",array('category' => $category));
  if ($row['title']) {
    return $row['title'];
  }
  return $category;
}

function syndicate($message,$path,$url = null) {
  $values = array();
  $values['message'] = $message;
  $values['path'] = $path;
  $values['url'] = $url;
  if ($url == null) {
    $values['url'] = OttWatchConfig::WWW.$path;
  }
  db_insert('feed',$values);
}

function tweet_txt_and_url($txt,$url) {
  # fix HTML escapes
  $txt = html_entity_decode($txt);

	$parts = explode(" ",$txt);
	$t = $txt;
	while (strlen($t) >= 115) {
		array_pop($parts);
		$txt = implode(" ",$parts);
		$txt = preg_replace("/[\.,:]$/","",$txt);
		$t = "$txt...";
	}
	return "$t $url";
}
 
function tweet($tweet) {

	# screw you em-dash and en-dash
	$tweet = preg_replace('/–/','-',$tweet);

	global $OTTVAR;

  # fix HTML escapes
  $tweet = html_entity_decode($tweet);

  $consumerKey = OttWatchConfig::TWITTER_POST_CONSUMER_KEY;
  $consumerSecret = OttWatchConfig::TWITTER_POST_CONSUMER_SECRET;
  $accessToken = OttWatchConfig::TWITTER_POST_ACCESS_TOKEN;
  $accessTokenSecret = OttWatchConfig::TWITTER_POST_TOKEN_SECRET;

	# print "$tweet\n";

  if ($consumerKey == '') {
    # we are in debug mode, so silently discard
    return 1;
  }

  $twitter = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
  $twitter->post('statuses/update', array('status' => $tweet));
	$code = $twitter->http_code;
	if ($code == 200) {
		return 1;
	} 
	print "ERROR: twitter returned $code ($tweet)\n";
	return 0;
}

function getViewState($html) {
	$lines = explode("\n",$html);
	foreach ($lines as $line) {
	  if (preg_match("/__VIEWSTATE/",$line)) {
	    $viewstate = preg_replace('/.*value="/',"",$line);
	    $viewstate = preg_replace('/".*/',"",$viewstate);
	    return $viewstate;
	  }
	}
}

function getEventValidation($html) {
	$lines = explode("\n",$html);
	foreach ($lines as $line) {
	  if (preg_match("/__EVENTVALIDATION/",$line)) {
	    $viewstate = preg_replace('/.*value="/',"",$line);
	    $viewstate = preg_replace('/".*/',"",$viewstate);
	    return $viewstate;
	  }
	}
}

function sendPost($url,$fields) {

  $fields_string = "";
	foreach($fields as $key=>$value) { 
    $fields_string .= $key.'='.$value.'&'; 
  }
	$fields_string = http_build_query($fields);
	
	$ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	$response = curl_exec($ch);
	curl_close($ch);

  return $response;
}

function autoSubmitForm($url,$fields,$helptext) {
  $id = md5(''.time());
  ?>
  <div style="display: none;">
  <form id="form<?php print $id; ?>" method="post" action="<?php print $url; ?>">
  <?php
  foreach ($fields as $k => $v) {
    print "<input type=\"hidden\" name=\"$k\" value=\"$v\"/>\n";
  }
  ?>
  <input type="submit" name="zzzgo" value="foo"/>
  </form>
  </div>
  <script>
  // setTimeout(function(){ document.getElementById('form<?php print $id; ?>').submit(); }, 2000);
  document.getElementById('form<?php print $id; ?>').submit();
  </script>
  <?php
}

function lobbyistSearch($name) {
  global $OTT_LOBBY_SEARCH_URL;

  # get search page
  $html = file_get_contents($OTT_LOBBY_SEARCH_URL);
  $ev = getEventValidation($html);
  $vs = getViewState($html);
	$fields = array(
	  '__VIEWSTATE' => $vs,
	  '__EVENTVALIDATION' => $ev,
    'ctl00$MainContent$btnSearch' => 'Search',
	  'ctl00$MainContent$txtLobbyist' => $name
	);
  $html = sendPost($OTT_LOBBY_SEARCH_URL,$fields);
 
  # find name in search results and forward to first one that is found.
  $lines = explode("\n",$html);
  $ev = getEventValidation($html);
  $vs = getViewState($html);
  $matches = array();
  foreach ($lines as $line) {
    if (preg_match("/gvSearchResults.*LnkLobbyistName/",$line)) {
      $zname = $line;
      $zname = preg_replace("/.*<u>/","",$zname);
      $zname = preg_replace("/<.*/","",$zname);
      $ctl = $line;
      $ctl = preg_replace("/.*;ctl/","ctl",$ctl);
      $ctl = preg_replace("/&.*/","",$ctl);
      if (!$matches[$zname]) {
        $matches[$zname] = array();
      }
      array_push($matches[$zname],$ctl);
    }
  }

  $matches["__ev"] = $ev;
  $matches["__vs"] = $vs;
  ksort($matches);
  return $matches;
}

function googleAnalytics() {
  ?>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php print OttWatchConfig::GOOGLE_ANALYTICS; ?>']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
  <?php

}

function disqus() {
  ?>
  <div id="disqus_thread"></div>
  <script type="text/javascript">
    /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
    var disqus_shortname = 'ottwatch'; // required: replace example with your forum shortname
    
    /* * * DON'T EDIT BELOW THIS LINE * * */
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
  </script>
  <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
  <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
  <?php    
}

function pr($o) {
  print "<pre>";
  print print_r($o);
  print "</pre>\n";
}

function renderShareLinks($text,$url) {
  $url = OttWatchConfig::WWW.$url;

  $fbUrl = "https://www.facebook.com/sharer/sharer.php?u=".urlencode($url);
  $twUrl = "";

  # twitter doesn't like spaces in urls
  $url = preg_replace("/ /","+",$url);
  $twUrl = "https://twitter.com/share".
    "?url=".urlencode($url).
    "&text=".urlencode($text).
    "&via=OttWatch".
    "&related=odonnell_k".
    "&hashtags=ottpoli".
    "";

  ?>
  <a target="_blank" href="<?php print $twUrl; ?>"><img src="<?php print OttWatchConfig::WWW; ?>/img/twitter-share.png"/></a>
  <a target="_blank" href="<?php print $fbUrl; ?>"><img src="<?php print OttWatchConfig::WWW; ?>/img/facebook-share.png"/></a>
  <?php
}

function getAddressLatLon($number,$name) {
  $arg = "$number $name, Ottawa, Ontario";
  $url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=true&address=".urlencode($arg);
  $result = json_decode(file_get_contents($url));
  return $result;
}

function getvar ($name) {
  $row = getDatabase()->one(" select value from variable where name = :name ",array('name'=>$name));
  if ($row['value']) {
    return unserialize($row['value']);
  }
  return '';
}

function setvar ($name,$value) {
  $serialized_value = serialize($value);
  getDatabase()->execute(" insert into variable (name, value) values (:name,:value) on duplicate key update value = :value ",array(
    'name' => $name,
    'value' => $serialized_value
  ));
}

function latLonToMercator($mercatorY_lat, $mercatorX_lon) {
  if ((abs($mercatorX_lon) > 180 || abs($mercatorY_lat) > 90)) {
    return;
  }

	$num = $mercatorX_lon * 0.017453292519943295;
	$x = 6378137.0 * $num;
	$a = $mercatorY_lat * 0.017453292519943295;
	
	$mercatorX_lon = $x;
	$mercatorY_lat = 3189068.5 * log((1.0 + sin($a)) / (1.0 - sin($a)));

  $ret = array();
  $ret['x'] = $mercatorX_lon;
  $ret['y'] = $mercatorY_lat;
  return $ret;
}

function mercatorToLatLon($mercatorX_lon,$mercatorY_lat) {

	#$mercatorX_lon = -8452764.23;
	#$mercatorY_lat = 5669659.81;
	
	if (abs($mercatorX_lon) < 180 && abs($mercatorY_lat) < 90) {
  	return array();
	}
	
	# 20037508.3427892 - is the full extent of web mercator
	if ((abs($mercatorX_lon) > 20037508.3427892) || (abs($mercatorY_lat) > 20037508.3427892)) {
  	return array();
	}
	
	$x = $mercatorX_lon;
	$y = $mercatorY_lat;
	$num3 = $x / 6378137.0;
	# 57.29 = 180/pi
	$num4 = $num3 * 57.295779513082323;
	$num5 = floor((($num4 + 180.0) / 360.0));
	$num6 = $num4 - ($num5 * 360.0);
	$num7 = 1.5707963267948966 - (2.0 * atan(exp((-1.0 * $y) / 6378137.0)));
	$mercatorX_lon = $num6;
	$mercatorY_lat = $num7 * 57.295779513082323;

  $latlon = array();
  $latlon['lat'] = $mercatorY_lat;
  $latlon['lon'] = $mercatorX_lon;
  return $latlon;

}

  function getLatLonFromPolygonAsText($text) {
    $points = array();
    $text = preg_replace('/^POLYGON\(\(/','',$text);
    $text = preg_replace('/\)\)$/','',$text);
    $pairs = explode(',',$text);
    foreach ($pairs as $p) {
      $pp = explode(' ',$p);
      $points[] = array('lat'=>$pp[1],'lon'=>$pp[0]);
    }
    return $points;
  }

  function getLatLonFromPoint($text) {
    # POINT(-75.74431034266786 45.38770326435866)
    $matches = array();
    $result = array();
    if (preg_match("/POINT\(([^ ]+) ([^\)]+)\)/",$text,$matches)) {
      $result['lat'] = $matches[2];
      $result['lon'] = $matches[1];
    }
    return $result;
  }

function db_save($table, $values, $key) {
	$count = getDatabase()->one(" select count(1) c from $table where $key = :key ",array('key'=>$values[$key]));
	if ($count['c'] == 0) {
		return db_insert($table,$values);
	}
	db_update($table,$values, $key);
}

function db_insert($table, $values) {
	$sql = db_generate_insert($table, $values);
	$new = array();
	foreach ($values as $k => $v) {
		$n = preg_replace('/\./','_',$k);
		$new[$n] = $values[$k];
	}
	return getDatabase()->execute($sql, $new);
}

function db_update($table,$values,$key) {
	if ($key == null) {
		$key = 'id';
	}
  $sql = " update $table set ";
  foreach ($values as $k => $v) {
    #if ($k == $key) { continue; }
    $sql .= " `$k` = :$k, ";
  }
  $sql = preg_replace('/, $/','',$sql);
  $sql .= " where $key = :$key ";
	getDatabase()->execute($sql,$values);
}

function db_generate_insert($table, $values) {
  $sql = "insert into $table (";
  foreach ( $values as $k => $v ) {
		$k = preg_replace('/\./','_',$k);
    $sql .= "`{$k}`,";
  }
  $sql = rtrim($sql, ',');
  $sql .= ") values (";
  foreach ( $values as $k => $v ) {
		$k = preg_replace('/\./','_',$k);
    $sql .= ":{$k},";
  }
  $sql = rtrim($sql, ',');
  $sql .= ")";
  return $sql;
}

function sendEmail($to,$subject,$body) {

  $mail = new PHPMailer;
  $mail->isSMTP();    
  $mail->Host = OttWatchConfig::SMTP_HOST;
  $mail->Port = OttWatchConfig::SMTP_PORT;
  $mail->From = OttWatchConfig::SMTP_FROM_EMAIL;
  $mail->FromName = OttWatchConfig::SMTP_FROM_NAME;
  $mail->addAddress($to);
  $mail->Subject = $subject;
  $mail->Body = $body;

  if(!$mail->send()) {
    return $mail->ErrorInfo;
  }

	return '';
}

function formatPercent($number, $by100=false) { 
	if ($by100) {
		$number = $number * 100;
	}
  return sprintf('%.2f', $number).'%';
} 

function formatMoney($number, $fractional=false) { 
		if ($number == '') { $number = 0; }
    if ($fractional) { 
        $number = sprintf('%.2f', $number); 
    } 
    while (true) { 
        $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number); 
        if ($replaced != $number) { 
            $number = $replaced; 
        } else { 
            break; 
        } 
    } 
    return $number; 
} 

function address_latlon ($num, $street) {

	$street = strtoupper($street);

	$where = urlencode("ADDRNUM = $num and FULLNAME like '$street %'");
	
	$url = "http://maps.ottawa.ca/arcgis/rest/services/Property_Parcels/MapServer/0/query";
	$url .= "?where=$where";
	$url .= "&outFields=objectid,ward";
	$url .= "&f=pjson";
	
	if (false) {
		# empty defaults, so skip
		$url .= "&text=";
		$url .= "&objectIds=";
		$url .= "&time=";
		$url .= "&geometry=";
		$url .= "&geometryType=esriGeometryEnvelope";
		$url .= "&inSR=";
		$url .= "&spatialRel=esriSpatialRelIntersects";
		$url .= "&relationParam=";
		$url .= "&returnGeometry=true";
		$url .= "&maxAllowableOffset=";
		$url .= "&geometryPrecision=";
		$url .= "&outSR=";
		$url .= "&returnIdsOnly=false";
		$url .= "&returnCountOnly=false";
		$url .= "&orderByFields=";
		$url .= "&groupByFieldsForStatistics=";
		$url .= "&outStatistics=";
		$url .= "&returnZ=false";
		$url .= "&returnM=false";
		$url .= "&gdbVersion=";
		$url .= "&returnDistinctValues=false";
	}

	$json = file_get_contents($url);

	$o = json_decode($json);

	if (count($o->features) > 0) {
		$x = $o->features[0]->geometry->x;
		$y = $o->features[0]->geometry->y;
		$m = mercatorToLatLon($x,$y);
		$m['ward'] = $o->features[0]->attributes->WARD;
		pr($m);
		return $m;
	}

	$m = array();
	$m['lat'] = '';
	$m['lon'] = '';
	$m['ward'] = '';
	return $m;

}

function c_file_get_contents($url) {
	global $OTTVAR;
	$m = md5($url);
	$f = "$OTTVAR/c_file_get_contents/$m";
	if (file_exists($f)) {
		return `gzip -cd $f`;
	}
	$d = file_get_contents($url);
	file_put_contents($f,gzencode($d));
	return $d;
}

function md5hist_insert ($values) {
	db_insert('md5hist',$values);
	md5hist_fix();
}

function md5hist_fix () {

	$sql = " select max(id) id from md5hist group by curmd5,prevmd5 having count(1) > 1; ";
	$rows = getDatabase()->all($sql);
	foreach ($rows as $r) {
		$ids = $r['id'];
		getDatabase()->execute(" delete from md5hist where id in ( $ids ); ");
	}

}
