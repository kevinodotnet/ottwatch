<?php

include_once 'epiphany/src/Epi.php';
include_once '../lib/include.php';

Epi::setPath('base', 'epiphany/src');
Epi::init('route');

getRoute()->get('/', 'home');
getRoute()->get('/lobbyist/([^\/]*)', 'lobbyist');
getRoute()->get('/lobbyist/(.*)/link', 'lobbyistLink');
getRoute()->get('.*', 'error404');
getRoute()->run();

function home() {
  echo "Welcome to OttWatch: ";
  echo time();
  echo "<hr/>";
}

function lobbyist($name) {
  print "You are looking for lobbyist <b>$name</b>\n";
}

function lobbyistLink($name) {
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
  # TODO: potential defect if two people are registered under same name?
  $lines = explode("\n",$html);
  foreach ($lines as $line) {
    if (preg_match("/gvSearchResults.*>$name</",$line)) {
      $ev = getEventValidation($html);
      $vs = getViewState($html);
      # href="javascript:__doPostBack(&#39;ctl00$MainContent$gvSearchResults$ctl02$LnkLobbyistName&#39;,&#39;&#39;)"><u>Patrick Dion</u></a>
      $ctl = $line;
      $ctl = preg_replace("/.*;ctl/","ctl",$ctl);
      $ctl = preg_replace("/&.*/","",$ctl);
			$fields = array(
			  '__VIEWSTATE' => $vs,
			  '__EVENTVALIDATION' => $ev,
		    $ctl => ''
			);
      autoSubmitForm($OTT_LOBBY_SEARCH_URL,$fields,"Forwarding to $name lobbyist page");
      #$html = sendPost($OTT_LOBBY_SEARCH_URL,$fields);
      #print "$html";
      return;
    }
  }

  print "Lobbyist search results for $name<hr/>";
  print "<hr/>";
  print $html;

  #header("Location: http://google.ca");
  #print "Will now 302 redirect to Ottawa.ca for lobbyist <b>$name</b> (".time().")\n";
}

function error404() {
  echo "Your file is not found: \n";
  echo time();
}

?>

