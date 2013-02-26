<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
ob_start();

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');
require_once('twitteroauth.php');

# base URL of the lobbyist registry
$url="https://apps107.ottawa.ca/LobbyistRegistry/search/searchlobbyist.aspx?lang=en";

# how far back to search. Lobbyists have 15 days
$daterange = 30;

# the set of all tweets generated based on the search window
$tweets = array();

# step through all the days looking for back-filed new lobby entries
$now = time();
$then = $now-(60*60*24*$daterange);
$from = strftime("%d-%b-%Y",$then);
$to = strftime("%d-%b-%Y",$now);
$html = searchByDate($from,$to);

# process page 1
$newtweets = parseSearchResults($html); foreach ($newtweets as $t) { $tweets[] = $t; }

# process any additional pages
$viewstate = getViewState($html);
$eventvalidation = getEventValidation($html);
$lines = explode("\n",$html);
for ($x = 0; $x < count($lines); $x++) {
  if (preg_match("/MainContent_page/",$lines[$x])) {
    $xml = $lines[$x-1].$lines[$x].$lines[$x+1];
    $xml = preg_replace("/&#39;/","'",$xml);
    $xml = simplexml_load_string($xml);
		$links = $xml->xpath("//a");
		# start at offset 1 because we've already processed page 1
		for ($page = 1; $page < count($links); $page++) {
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
			$newtweets = parseSearchResults($html); foreach ($newtweets as $t) { $tweets[] = $t; }
		}
	}
}

$sent = 0;
foreach ($tweets as $t) {
	if (tweet($t)) {
		$sent ++;
	}
}

print "Send $sent of ".count($tweets)." tweets\n";

$output = ob_get_contents();
ob_end_clean();
$now = strftime("%Y%m%d_%H%M%S",time());
file_put_contents("$OTTVAR/ranat_lobby_$now",$output);

exit;

#######################################################################################
function parseSearchResults($html) {

	$tweets = array();

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
      $from = "{$from[1]}{$from[0]}";
      $to = "{$to[1]}{$to[0]}";

      $when = "$from-$to";
			if ($from == $to) {
	      $when = $from;
			}

	    $what = $spans[2];
	
	    $tweet = "Lobbying: $who ($when) $what";
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $tweet = preg_replace("/  /"," ",$tweet);
	    $tweet = preg_replace("/\n/"," ",$tweet);
	    $tweet = preg_replace("/\n/"," ",$tweet);
	    $tweet = preg_replace("/\n/"," ",$tweet);
	    $tweet = preg_replace("/\n/"," ",$tweet);
	    $tweet = preg_replace("/\n/"," ",$tweet);
	    $tweet = preg_replace("/\n/"," ",$tweet);
	    $tweet = preg_replace("/\n/"," ",$tweet);
	    $tweet = preg_replace("/\n/"," ",$tweet);
	    $tweet = preg_replace("/\n/"," ",$tweet);
	    $tweet = preg_replace("/\n/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $tweet = preg_replace("/\r/"," ",$tweet);
	    $len1 = strlen($tweet);
	    $tweet = substr($tweet,0,130);
	    $len2 = strlen($tweet);
	    if ($len1 != $len2) {
	      $tweet = $tweet."...";
	    }
			array_push($tweets,$tweet);
	  }
	}

	return $tweets;
}

function searchByDate($from,$to) {
  global $url;

  # how many days to go back in order to find results.
  $daterange = 60; 

	$html = file_get_contents($url);
  $viewstate = getViewState($html);
  $eventvalidation = getEventValidation($html);
	
	$fields = array(
	  '__VIEWSTATE' => $viewstate,
	  '__EVENTVALIDATION' => $eventvalidation,
	  'ctl00$MainContent$dpFromDate_txtbox' => $from,
	  'ctl00$MainContent$dpToDate_txtbox' => $to,
	  'ctl00$MainContent$btnSearch' => 'Search'
	);

  $response = sendPost($url,$fields);
  return $response;
}

