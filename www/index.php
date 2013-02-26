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
      alert('Cannot perform an empty search');
      return;
    }
    document.location.href = 'lobbyist/search/'+v;
  }
  </script>

  <p/>

  <div class="row-fluid">
  <span class="span9">
  <h3>Search</h3>
  <input type="text" id="lobbyist_search_value" placeholder="Lobbyist name..."><br/>
  <button clas="btn" onclick="lobbyist_search_form_submit()"><i class="icon-search"></i> Search</button>
  </span>
  <span class="span3">
  <!--
  <a class="twitter-timeline" data-dnt="true" href="https://twitter.com/ottwatch" data-widget-id="306310112971210752">Tweets by @ottwatch</a>
  <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
  </span>
  -->
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
    ?>
    <div class="row-fluid">
    <span class="span12">
    <h3>No Matches</h3>
    </span>
    </div>
    <?php
    bottom();
    return;
  }
  ?>
  <div class="row-fluid">
  <h3>Found <?php print count($matches); ?> matches.</h3>
  <table class="table table-hover table-condensed">
  <tr>
  <th>Name</th>
  <th>Link</th>
  </tr>
  </tr>
  <?php
  foreach ($matches as $name => $ctl) {
    ?>
    <tr>
    <td>
    <a href="../<?php print $name; ?>"><?php print $name; ?></a>
    </td>
    <td>
    <a target="_blank" href="../<?php print $name; ?>/link"><i class="icon-edit"></i></a>
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
  <p><a target="_blank" href="<?php print $name; ?>/link"><i class="icon-edit"></i> direct link to ottawa.ca</a></p>
  </div>
  </div>
  <div class="row-fluid">
  <div class="span12">
  <iframe style="border: 0px; width: 100%; height: 1200px;" src="<?php print $name; ?>/link"></iframe>
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
    if (preg_match("/gvSearchResults.*LnkLobbyistName/",$line)) {
      $zname = $line;
      $zname = preg_replace("/.*<u>/","",$zname);
      $zname = preg_replace("/<.*/","",$zname);
      $ctl = $line;
      $ctl = preg_replace("/.*;ctl/","ctl",$ctl);
      $ctl = preg_replace("/&.*/","",$ctl);
      if ($zname == $name) {
        # exact match for the one we are looking for.
  			$fields = array(
  			  '__VIEWSTATE' => $vs,
  			  '__EVENTVALIDATION' => $ev,
  		    $ctl => ''
  			);
        autoSubmitForm($OTT_LOBBY_SEARCH_URL,$fields,"Forwarding to $name lobbyist page");
      }
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

  print "<h3>Exact match not found.</h3>\n";
  print "<ul>\n";
  foreach ($matches as $zname => $ctl) {
    print "<li><a href=\"../$zname/link\">$zname</a></li>\n";
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
  padding: 20px;
}
</style>
</head>
<body>
<div style="background: #fcfcfc; padding: 10px; border: #c0c0c0 solid 1px;">

<div class="row-fluid">
<div class="lead">
<a href="<?php print $OTT_WWW; ?>"><img style="width: 50px; height: 50px;" src="<?php print $OTT_WWW; ?>/img/ottwatch.png"/></a>
<?php print $title; ?>
</div>
</div>

</div>
<?php
}

function bottom() {
  ?>
<div style="margin-top: 10px; background: #fcfcfc; padding: 10px; border: #c0c0c0 solid 1px;">
<i>
Follow <b><a href="http://twitter.com/OttWatch">@OttWatch</a></b> on Twitter too.
Created by <a href="http://kevino.ca"><b>Kevin O'Donnell</b></a> to make it easier to be part of the political conversation in Ottawa.</i>
</div>
  <?php
  googleAnalytics();
  ?>

  <script src="http://code.jquery.com/jquery.js" type="text/javascript"></script>
  </body>
  </html>
  <?php
}

?>

