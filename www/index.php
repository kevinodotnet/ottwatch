<?php

include_once '../lib/include.php';
include_once 'epiphany/src/Epi.php';
include_once 'controllers/MeetingController.php';

Epi::setPath('base', 'epiphany/src');
Epi::init('route');

getRoute()->get('/', 'dashboard');
getRoute()->get('/about', 'about');
#getRoute()->get('/dashboard', 'dashboard');

getRoute()->get('/lobbyist/search/(.*)', 'lobbyist_search');
getRoute()->get('/lobbyist/([^\/]*)', 'lobbyist');
getRoute()->get('/lobbyist/(.*)/details', 'lobbyistDetails');
getRoute()->get('/lobbyist/(.*)/link', 'lobbyistLink');
getRoute()->get('/meetings/(.*)', array('MeetingController','dolist'));

getRoute()->get('.*', 'error404');
getRoute()->run();

function dashboard() {
  global $OTT_WWW;
  top();
  ?>
  <div class="row-fluid">
  <div class="span5">
  <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
  <tr>
  <td colspan="3">
  <h4>Today's Meetings</h4>
  </td>
  </tr>
  <?php
  $meetings = getDatabase()->all(" select meetid,category,date(starttime) starttime from meeting where date(starttime) = date(CURRENT_TIMESTAMP) order by starttime ");
  foreach ($meetings as $m) {
    $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
    ?>
    <tr>
      <td><?php print meeting_category_to_title($m['category']); ?></td>
      <td style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
      <td style="text-align: center;"><a class="btn btn-mini" target="_blank" href="<?php print $mtgurl; ?>=AGENDA">Agenda</a></td>
    </tr>
    <?php
  }
  ?>
  <tr>
  <td colspan="3">
  <h4>Upcoming Meetings</h4>
  </td>
  </tr>
  <?php
  $meetings = getDatabase()->all(" select category,date(starttime) starttime,meetid from meeting where date(starttime) > date(CURRENT_TIMESTAMP) order by starttime ");
  foreach ($meetings as $m) {
    $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
    ?>
    <tr>
      <td><?php print meeting_category_to_title($m['category']); ?></td>
      <td style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
      <td style="text-align: center;"><a class="btn btn-mini" target="_blank" href="<?php print $mtgurl; ?>=AGENDA">Agenda</a></td>
    </tr>
    <?php
  }
  ?>
  <tr>
  <td colspan="3">
  <h4>Previous Meetings</h4>
  </td>
  </tr>
  <?php
  $meetings = getDatabase()->all(" select meetid,category,date(starttime) starttime from meeting where date(starttime) < date(CURRENT_TIMESTAMP) order by starttime desc limit 10 ");
  foreach ($meetings as $m) {
    $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
    ?>
    <tr>
      <td><?php print meeting_category_to_title($m['category']); ?></td>
      <td style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
      <td style="text-align: center;"><a class="btn btn-mini" target="_blank" href="<?php print $mtgurl; ?>=AGENDA">Agenda</a></td>
    </tr>
    <?php
  }
  ?>
  <tr>
  <td colspan="3">
  <a href="<?php print $OTT_WWW; ?>/meetings/all">See all meetings</a>
  </td>
  </tr>
  </table>
  </div>
  <div class="span3">
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
  <h4>Lobbyist Registry</h4>
  <div class="input-append">
  <input type="text" id="lobbyist_search_value" placeholder="Search by name...">
  <button class="btn" onclick="lobbyist_search_form_submit()"><i class="icon-search"></i> Search</button>
  </div>
  </div>
  <div class="span4">
  <a class="twitter-timeline" data-dnt="true" href="https://twitter.com/ottwatch" data-widget-id="306310112971210752">Tweets by @ottwatch</a>
  <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
  </div>
  </div>
  <?php
  bottom();
}

function about() {
  top();
  include("about_content.html");
  bottom();
}

function home() {
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
    <div class="span12">
    <h3>No Matches</h3>
    </div>
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
  <p>If you get a blank page below, reload, or go <a target="_blank" href="<?php print urlencode($name); ?>/link">direct to ottawa.ca <i class="icon-edit"></i></a>.
  (The lobbyist registry has intermittent flake-outs)
  </p>
  </div>
  </div>
  <div class="row-fluid">
  <div class="span12">
  <iframe style="border: 0px; width: 100%; height: 1200px;" src="<?php print urlencode($name); ?>/details"></iframe>
  </div>
  </div>
  <?php
  bottom();
}

function lobbyistDetails($name) {
  global $OTT_LOBBY_SEARCH_URL;
  $matches = lobbyistSearch($name);
  $vs = $matches["__vs"]; unset($matches["__vs"]);
  $ev = $matches["__ev"]; unset($matches["__ev"]);
  foreach ($matches as $zname => $ctl) {
    if ($zname == $name) {
    	$fields = array(
    	  '__VIEWSTATE' => $vs,
    	  '__EVENTVALIDATION' => $ev,
        $ctl => ''
    	);
      $html = sendPost($OTT_LOBBY_SEARCH_URL,$fields);
      $lines = explode("\n",$html);
      $html = array();
      $add = 1;
      foreach ($lines as $line) {
        if ($add) {
          array_push($html,$line);
        }
        if (preg_match("/<body/",$line)) {
          $add = 0;
        }
        if (preg_match("/Header End/",$line)) {
          $add = 1;
        }
        if (preg_match("/Search Lobbyist Design/",$line)) {
          $add = 0;
        }
        if (preg_match("/End Search Lobbyist Design/",$line)) {
          $add = 1;
        }
      }
      $base = "\n<base href=\"https://apps107.ottawa.ca/LobbyistRegistry/search/\" target=\"_blank\"/>\n";
      $html = implode("\n",$html);
      $html = preg_replace("/<head>/","<head>$base",$html);
      print $html;
      return;
    }
  } 
  print "$name Not found...\n";
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
<!DOCTYPE html>
<html>
<!-- <?php print $_SERVER['REQUEST_URI']; ?> -->
<head>
<title><?php print $title; ?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="<?php print $OTT_WWW; ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen" type="text/css">
<link href="<?php print $OTT_WWW; ?>/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
<style type="text/css">
  body {
  padding: 20px;
}
</style>
</head>
<body>

<div class="row-fluid">
<div class="span12">
<div class="navbar"><div class="navbar-inner">
<ul class="nav">
<li><a href="<?php print $OTT_WWW; ?>">Home</a></li>
<!--<li><a href="<?php print $OTT_WWW; ?>/dashboard">Dashboard</a></li>-->
<li><a href="<?php print $OTT_WWW; ?>/about">About</a></li>
</ul>
</div></div>
</div>
</div>

<?php
	if ($title != '') {
		?>
		
		<div style="background: #fcfcfc; padding: 10px; border: #c0c0c0 solid 1px;">
		<div class="row-fluid">
		<div class="lead span6">
		<?php print $title; ?>
		</div>
		</div>
		</div>
		<?php
	}
}

function bottom() {
  global $OTT_WWW;
  ?>
<div style="margin-top: 10px; background: #fcfcfc; padding: 10px; border: #c0c0c0 solid 1px;">
<a href="<?php print $OTT_WWW; ?>"><img alt="Ottawa Watch Logo" style="float: right; padding-left: 5px; width: 50px; height: 50px;" src="<?php print $OTT_WWW; ?>/img/ottwatch.png"/></a>
<i>
Follow <b><a href="http://twitter.com/OttWatch">@OttWatch</a></b> on Twitter too.
Created by <a href="http://kevino.ca"><b>Kevin O'Donnell</b></a> to make it easier to be part of the political conversation in Ottawa.</i>
<div style="clear: both;"></div>
</div>
  <?php
  googleAnalytics();
  ?>

  <script src="http://code.jquery.com/jquery.js" type="text/javascript"></script>
  <script src="<?php print $OTT_WWW; ?>/bootstrap/js/bootstrap.min.js"></script>
  </body>
  </html>
  <?php
}

?>

