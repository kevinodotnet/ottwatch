<?php

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

	require_once('bitly.php');
	require_once('Epi.php');
	require_once('controllers/MeetingController.php');
	#require_once('EpiDatabase.php');
	#require_once('EpiException.php');
	
	# Prepare the database classes
	Epi::setPath('base', "$base/www/epiphany/src");
	Epi::init('database');
	Epi::setSetting('exceptions', true);
	EpiDatabase::employ(OttWatchConfig::DB_TYPE, OttWatchConfig::DB_NAME, OttWatchConfig::DB_HOST, OttWatchConfig::DB_USER, OttWatchConfig::DB_PASS);
}

# Location of state files
$OTTVAR="/mnt/shared/ottwatch/var";

# URL of Lobby Registry search service
$OTT_LOBBY_SEARCH_URL="https://apps107.ottawa.ca/LobbyistRegistry/search/searchlobbyist.aspx?lang=en";

# HTTP address of OttWatch itself.
$OTT_WWW="http://ottwatch.kevino.ca";

function meeting_category_to_title($category) {
  $row = getDatabase()->one(" select * from category where category = :category ",array('category' => $category));
  if ($row['title']) {
    return $row['title'];
  }
  $row = getDatabase()->one(" select count(1) c from category where category = :category ",array('category' => $category));
  if ($row['c'] > 0) {
    # row exists, but title is null; broken; delete to below insert will fix
    getDatabase()->execute(" delete from category where category = :category ",array('category' => $category));
  }
  $row = getDatabase()->execute(" insert into category (category,title) values (:category,:title) ",array('title' => $category,'category' => $category));
  return $category;
}

function tweet_txt_and_url($txt,$url) {
	$parts = explode(" ",$txt);
	$t = "$txt $url";
	while (strlen($t) > 140) {
		array_pop($parts);
		$txt = implode(" ",$parts);
		$txt = preg_replace("/[\.,:]$/","",$txt);
		$t = "$txt... $url";
	}
	return $t;
}
 
function tweet($tweet,$allowDup=0) {

	global $OTTVAR;

	# send no tweet twice
  $hash = md5($tweet);
  $hashfile = "$OTTVAR/tweets/$hash";
  if (file_exists($hashfile)) {
		if (!$allowDup) {
			return -1;
		}
	}

	# check tweet cache and dont send if found
	file_put_contents($hashfile,$tweet);

	# todo: move to non-git configuration file
  $consumerKey = 'aPqhRRoL1X4lDRGbRpdjA';
  $consumerSecret = '9Cz0ot2iUfzAaoRNesHmxKl4se7zYMDpka0x2F9imG0';
  $accessToken = '1206679020-ZDNk6AZT5cYhGWiyFXB4K5BsQK3ItQf5m4Cpt5t';
  $accessTokenSecret = 'EmT6yieQC9LxAwYIDHKFnUOqf1jX31jHHwxwspX5TnI';

  $twitter = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	print "Sending... $tweet\n";
  $twitter->post('statuses/update', array('status' => $tweet));
	$code = $twitter->http_code;
	if ($code == 200) {
		return 1;
	} 
	print "ERROR: twitter returned $code\n";
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
    // print ">>> $name >>> $line <<<\n";
#    if (preg_match("/gvSearchResults.*LnkLobbyistName.*>$name</",$line)) {
#      # href="javascript:__doPostBack(&#39;ctl00$MainContent$gvSearchResults$ctl02$LnkLobbyistName&#39;,&#39;&#39;)"><u>Patrick Dion</u></a>
#      $ctl = $line;
#      $ctl = preg_replace("/.*;ctl/","ctl",$ctl);
#      $ctl = preg_replace("/&.*/","",$ctl);
#			$fields = array(
#			  '__VIEWSTATE' => $vs,
#			  '__EVENTVALIDATION' => $ev,
#		    $ctl => ''
#			);
#      autoSubmitForm($OTT_LOBBY_SEARCH_URL,$fields,"Forwarding to $name lobbyist page");
#      #$html = sendPost($OTT_LOBBY_SEARCH_URL,$fields);
#      #print "$html";
#      return;
#    }
    if (preg_match("/gvSearchResults.*LnkLobbyistName/",$line)) {
      $zname = $line;
      $zname = preg_replace("/.*<u>/","",$zname);
      $zname = preg_replace("/<.*/","",$zname);
      $ctl = $line;
      $ctl = preg_replace("/.*;ctl/","ctl",$ctl);
      $ctl = preg_replace("/&.*/","",$ctl);
      $matches[$zname] = $ctl;
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
  _gaq.push(['_setAccount', 'UA-6324294-24']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
  <?php

}

?>
