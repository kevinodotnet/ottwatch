<?php

include_once 'epiphany/src/Epi.php';
include_once '../lib/include.php';

Epi::setPath('base', 'epiphany/src');
Epi::init('route');

getRoute()->get('/', 'home');
getRoute()->get('/lobbyist/search/(.*)', 'lobbyist_search');
#getRoute()->get('/lobbyist/search', 'lobbyist_search_form');
getRoute()->get('/lobbyist/([^\/]*)', 'lobbyist');
getRoute()->get('/lobbyist/(.*)/link', 'lobbyistLink');
getRoute()->get('.*', 'error404');
getRoute()->run();

function home() {
  top("Ottawa Watch");
  ?>
  <script>
  function lobbyist_search_form_submit() {
    v = document.getElementById('lobbyist_search_value').value;
    if (v == '') {
      return;
    }
    document.location.href = 'lobbyist/search/'+v;
  }
  </script>

  <div class="row-fluid">
  <span class="span6">
  Lobbyist Search by name
  </span>
  </div>
  <div class="row-fluid">
  <span class="span6">
  <input type="text" id="lobbyist_search_value"/>
  <input type="button" onclick="lobbyist_search_form_submit()" value="Search"/>
  </span>
  </div>

  <?php
  bottom();
}

function lobbyist_search($name) {
  top("Lobbyist Search: $name");
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
    bottom();
    return;
  }
  ?>
  <p>Found <?php print count($matches); ?> matches.</p>
  <div class="span6">
  <table class="table table-hover table-condensed">
  <tr>
  <th>Ottawa.ca Link</th>
  <th>Name</th>
  </tr>
  </tr>
  <?php
  foreach ($matches as $name => $ctl) {
    ?>
    <tr>
    <td>
    <a target="_blank" href="../<?php print $name; ?>/link">link</a>
    </td>
    <td>
    <a href="../<?php print $name; ?>"><?php print $name; ?></a>
    </td>
    </tr>
    <?php
  }
  ?>
  </table>
  </div>
  <?php
  bottom();
}

function lobbyist($name) {
  top("Lobbyist: $name");
  ?>
  <div class="row-fluid">
  <div class="span12">
  <p><a target="_blank" href="<?php print $name; ?>/link">link</a></p>
  </div>
  </div>
  <div class="row-fluid">
  <div class="span12">
  <iframe style="width: 100%; height: 1200px;" src="<?php print $name; ?>/link"></iframe>
  </div>
  </div>
  <?php
  bottom();
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

function top($title) {
  global $OTT_WWW;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<!-- <?php print $_SERVER['REQUEST_URI']; ?> -->
<head>
<title><?php print $title; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="<?php print $OTT_WWW; ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen" type="text/css">
<style type="text/css">
  body {
  padding-left: 20px;
  padding-right: 20px;
}
</style>
</head>
<body>
<div style="float: right;">
<a href="<?php print $OTT_WWW; ?>">Home</a>
</div>
<div>
<div class="lead"><?php print $title; ?></div>
</div>
<?php
}

function bottom() {
  ?>
  <script src="http://code.jquery.com/jquery.js" type="text/javascript"></script>
  </body>
  </html>
  <?php
}

?>

