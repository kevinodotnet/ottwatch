<?php

$OTTVAR="/mnt/shared/ottwatch/var";

$OTT_LOBBY_SEARCH_URL="https://apps107.ottawa.ca/LobbyistRegistry/search/searchlobbyist.aspx";
$OTT_WWW="http://localhost/ottwatch";

function tweet($tweet,$allowDup=0) {

	global $OTTVAR;

	# send no tweet twice
  $hash = md5($tweet);
  $hashfile = "$OTTVAR/tweets/$hash";
  if (file_exists($hashfile)) {
		if (!$allowDup) {
			return false;
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
  if(strlen($tweet) <= 140) {
    $twitter->post('statuses/update', array('status' => $tweet));
		return true;
  } else {
		print "WARNING: tweet too long; not sent; '$tweet'";
		return false;
  }

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

?>
