<?php

$url="https://apps107.ottawa.ca/LobbyistRegistry/search/searchlobbyist.aspx?lang=en";
require_once('twitteroauth.php');
$VAR="/mnt/shared/ottwatch/var";

#######################################################################################

# how far back to search. Lobbyists have 15 days to do it, so that's how far back we
# need to search
$daterange = 22;

# step through all the days looking for back-filed new lobby entries
for ($x = $daterange; $x >= 0; $x--) {
  $now = time();
  $then = $now-(60*60*24*$x);
  $date = strftime("%d-%b-%Y",$then);

  print "#########################################################\n";
  print "Searching $date... (page 0)\n";
  $html = searchByDate($date);

	# process page 1
  parseSearchResults($html);

	$viewstate = getViewState($html);
	$eventvalidation = getEventValidation($html);

	# process any additional pages
	$lines = explode("\n",$html);
	for ($x = 0; $x < count($lines); $x++) {
	  if (preg_match("/MainContent_page/",$lines[$x])) {
	    $xml = $lines[$x-1].$lines[$x].$lines[$x+1];
	    $xml = preg_replace("/&#39;/","'",$xml);
	    $xml = simplexml_load_string($xml);
			$links = $xml->xpath("//a");
			# start at offset 1 because we've already processed page 1
			for ($page = 1; $page < count($links); $page++) {
			  print "Searching $date... (page $page)\n";
				$href = $links[$page]->xpath("@href"); $href = $href[0].'';
				$name = $href;
				$name = preg_replace("/.*__doPostBack\('/","",$name);
				$name = preg_replace("/'.*/","",$name);
				$fields = array(
				  '__VIEWSTATE' => $viewstate,
				  '__EVENTVALIDATION' => $eventvalidation,
				  '__EVENTTARGET' => $name,
				  '__EVENTARGUMENT' => ''
				);
			  $html = sendPost($url,$fields);
			  parseSearchResults($html);
			}
		}
	}
	exit; # just one day during testing
}

exit;

#######################################################################################
function parseSearchResults($html) {

	$viewstate = getViewState($html);
	$eventvalidation = getEventValidation($html);
	
	$lines = explode("\n",$html);
	for ($x = 0; $x < count($lines); $x++) {
	  if (preg_match("/MainContent_gvSearchResults_LnkLobbyistName/",$lines[$x])) {
	    # start of lobby result
	    $xml = "<tr><td><b>";
	    for ($y = 0; $y < 13; $y++) {
	      $xml .= $lines[$x+$y]."\n";
	    }
	    $xml .= "</td></tr>";
	    $xml = preg_replace("/&/",'&amp;',$xml);
	    $xml = simplexml_load_string($xml);
	    #print print_r($xml); print "\n";
	
	    $who = $xml->xpath("//u"); $who = $who[0].'';
	    $spans = $xml->xpath("//span");
      # for ($y = 0; $y < count($spans); $y++) { print "span[$y] ".$spans[$y]."\n"; }
	
	    $job = $spans[0].'/'.$spans[1];
	    $from = explode('-',$spans[4]);
	    $to = explode('-',$spans[5]);
      $when = "{$from[0]}-{$from[1]} to {$to[0]}-{$to[1]}";

	    $what = $spans[2];
	
	    $tweet = "Lobbyist $who: $what, $when";
	    $tweet = "($when) Lobbyist $who: $what, $when";
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $len1 = strlen($tweet);
	    $tweet = substr($tweet,0,130);
	    $len2 = strlen($tweet);
	    if ($len1 != $len2) {
	      $tweet = $tweet."...";
	    }
      print "$tweet\n";
	  }
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

function searchByDate($date) {
  global $url;

  # how many days to go back in order to find results.
  $daterange = 60; 

	$html = file_get_contents($url);
  $viewstate = getViewState($html);
  $eventvalidation = getEventValidation($html);
	
	$fields = array(
	  '__VIEWSTATE' => $viewstate,
	  '__EVENTVALIDATION' => $eventvalidation,
	  'ctl00$MainContent$dpFromDate_txtbox' => $date,
	  'ctl00$MainContent$dpToDate_txtbox' => $date,
	  'ctl00$MainContent$btnSearch' => 'Search'
	);

  $response = sendPost($url,$fields);
  return $response;
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

