<?php

include_once 'epiphany/src/Epi.php';
include_once '../lib/include.php';

Epi::setPath('base', 'epiphany/src');
Epi::init('route');

getRoute()->get('/', 'home');
getRoute()->get('/lobbyist/search/(.*)', 'lobbyist_search');
getRoute()->get('/lobbyist/search', 'lobbyist_search_form');
getRoute()->get('/lobbyist/([^\/]*)', 'lobbyist');
getRoute()->get('/lobbyist/(.*)/link', 'lobbyistLink');
getRoute()->get('.*', 'error404');
getRoute()->run();

function home() {
  echo "Welcome to OttWatch<hr/>";
  ?>
  <script>
  function lobbyistRedirect() {
    name = document.getElementById('lobbyistName').value;
    url = 'lobbyist/'+name+'/link';
    document.location.href = url;
  }
  </script>
  <input type="text" id="lobbyistName"/>
  <input type="button" onclick="lobbyistRedirect();" value="Load"/>
  <?php
}

function lobbyist_search_form() {
  ?>
  <script>
  function lobbyist_search_form_submit() {
    v = document.getElementById('lobbyist_search_value').value;
    if (v == '') {
      return;
    }
    document.location.href = 'search/'+v;
  }
  </script>
  <input type="text" id="lobbyist_search_value"/>
  <input type="button" onclick="lobbyist_search_form_submit()" value="Search"/>
  <?php
}

function lobbyist_search($name) {
  print "Searching for $name<br/>\n";
  $matches = lobbyistSearch($name);
  $vs = $matches["__vs"]; unset($matches["__vs"]);
  $ev = $matches["__ev"]; unset($matches["__ev"]);
#  if (count($matches) == 1) {
#    reset($matches);
#    $name = key($matches);
#    header("Location: ../$name");
#    return;
#  }
  if (count($matches) == 0) {
    print "No matches\n";
    return;
  }
  print "Found ".count($matches)." matches.<br/>\n";
  print "<ul>\n";
  foreach ($matches as $name => $ctl) {
    print "<li><a href=\"../$name\">$name</a>\n";
    print "<a target=\"blank\" href=\"../$name/link\">link</a></li>\n";
  }
  print "</ul>\n";
}

function lobbyist($name) {
  ?>
  <h1><?php print $name; ?></h1>
  <a target="_blank" href="<?php print $name; ?>/link">ottawa.ca profile for <?php print $name; ?></a>
  <?php
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
  $ev = getEventValidation($html);
  $vs = getViewState($html);
  $matches = array();
  foreach ($lines as $line) {
    // print ">>> $name >>> $line <<<\n";
    if (preg_match("/gvSearchResults.*LnkLobbyistName.*>$name</",$line)) {
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

  if (count($matches) == 1) {
    # not exact match, but only one, so forward anyway
    reset($matches);
    $zname = key($matches);
    $ctl = $matches[$zname];
		$fields = array(
		  '__VIEWSTATE' => $vs,
		  '__EVENTVALIDATION' => $ev,
	    $ctl => ''
		);
    autoSubmitForm($OTT_LOBBY_SEARCH_URL,$fields,"Forwarding to $zname lobbyist page");
    return;
  }

  print "Exact name match not found.<hr/>\n";
  print "<ul>\n";
  foreach ($matches as $name => $ctl) {
    print "<li><a href=\"../$name/link\">$name</a></li>\n";
  }
  print "</ul>\n";

  #print "Lobbyist search results for $name<hr/>";
  #print "<hr/>";
  #print $html;

  #header("Location: http://google.ca");
  #print "Will now 302 redirect to Ottawa.ca for lobbyist <b>$name</b> (".time().")\n";
}

function error404() {
  print "Not Found\n";
}

?>

