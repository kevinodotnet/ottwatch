<?php

include_once '../lib/include.php';
include_once 'epiphany/src/Epi.php';
include_once 'controllers/ApiController.php';
include_once 'controllers/MeetingController.php';
include_once 'controllers/DevelopmentApp.php';
include_once 'controllers/LobbyistController.php';
include_once 'controllers/LoginController.php';
include_once 'controllers/UserController.php';

Epi::setPath('base', 'epiphany/src');
Epi::init('route');
Epi::init('api');
Epi::init('route','session-php');

getApi()->get('/api/about', array('ApiController', 'about'), EpiApi::external);
getApi()->get('/api/point', array('ApiController', 'point'), EpiApi::external);
getApi()->get('/api/roads/(\d+)/([^/]+)', array('ApiController', 'road'), EpiApi::external);
getApi()->get('/api/roads/(\d+)/([^/]+)/(.*)', array('ApiController', 'road'), EpiApi::external);
getApi()->get('/api/wards/(\d+)', array('ApiController', 'ward'), EpiApi::external);
getApi()->get('/api/committees', array('ApiController', 'committees'), EpiApi::external);
getApi()->get('/api/councillors/(\d+)', array('ApiController', 'councillorById'), EpiApi::external);
getApi()->get('/api/councillors/([^/]+)/(.*)', array('ApiController', 'councillorByName'), EpiApi::external);

getRoute()->get('/', 'dashboard');
getRoute()->get('/about', 'about');
getRoute()->get('/ideas', 'ideas');
#getRoute()->get('/dashboard', 'dashboard');

getRoute()->get('/user/home', array('UserController','home'));
getRoute()->post('/user/add/place', array('UserController','addPlace'));

getRoute()->get('/user/register', array('LoginController','displayRegister'));
getRoute()->post('/user/register', array('LoginController','doRegister'));
getRoute()->get('/user/login', array('LoginController','display'));
getRoute()->post('/user/login', array('LoginController','doLogin'));
getRoute()->get('/user/logout', array('LoginController','logout'));

getRoute()->get('/lobbying/search/(.*)', array('LobbyistController','search'));
getRoute()->get('/lobbying/lobbyists/(.*)', array('LobbyistController','showLobbyist'));
getRoute()->get('/lobbying/clients/(.*)', array('LobbyistController','showClient'));
getRoute()->get('/lobbying/thelobbied/(.*)', array('LobbyistController','showLobbied'));
getRoute()->get('/lobbying/files/(.*)', array('LobbyistController','showFile'));
getRoute()->get('/lobbyist/([^\/]*)', 'lobbyist'); # legacy REST location

#getRoute()->get('/lobbyist/(.*)/details', 'lobbyistDetails');
#getRoute()->get('/lobbyist/(.*)/link', 'lobbyistLink');

getRoute()->get('/devapps', array('DevelopmentAppController','listAll'));
getRoute()->get('/devapps/([^\/]+)', array('DevelopmentAppController','viewDevApp'));

getRoute()->get('/meetings/file/(\d+)', array('MeetingController','getFileCacheUrl'));
getRoute()->get('/meetings', array('MeetingController','dolist')); // meetings
getRoute()->get('/meetings/([^\/]*)', array('MeetingController','dolist')); // meetings/CATEGORY
getRoute()->get('/meetings/meetid/(\d+)', array('MeetingController','meetidForward')); // meetings/CATEGORY/ID
getRoute()->get('/meetings/([^\/]*)/(\d+)', array('MeetingController','meetingDetails')); // meetings/CATEGORY/ID
getRoute()->get('/meetings/([^\/]*)/(\d+)/item/(\d+)', array('MeetingController','meetingDetails')); // meetings/CATEGORY/ID
getRoute()->get('/meetings/([^\/]*)/(\d+)/item/(\d+)/(files|files.json)', array('MeetingController','itemFiles')); // meetings/CATEGORY/ID

getRoute()->get('.*', 'error404');
getRoute()->run();

function ottawaMediaRSS() {
  $url = "http://ottawa.ca/rss/news_en.xml";
  $rss = file_get_contents($url);
  $xml = simplexml_load_string($rss);
  if (!is_object($xml)) {
    # could not load RSS; just fail silently
    print "<h4>Media Releases</h4>\n";
    print "<i>Could not load media releases. Probably a temporary error.</i>";
    return;
  }
  $items = $xml->xpath("//item");
  print "<h4>Media Releases</h4>\n";
  $max = 4;
  foreach ($items as $item) {
    if ($x++ < $max) {
    $title = $item->xpath("title"); $title = $title[0].'';
    $link = $item->xpath("link"); $link = $link[0].'';
    print "<small><a href=\"$link\" target=\"_blank\">$title</a></small><br/>\n";
    }
  }
}

function dashboard() {
  global $OTT_WWW;
  top();
  ?>
  <div class="row-fluid">
  <div class="span4">
  <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
  <tr>
  <td colspan="3">
  <h4>Today's Meetings</h4>
  </td>
  </tr>
  <?php
  $meetings = getDatabase()->all(" select id,meetid,category,date(starttime) starttime from meeting where date(starttime) = date(CURRENT_TIMESTAMP) order by starttime ");
  foreach ($meetings as $m) {
    $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
    ?>
    <tr>
      <td><?php print meeting_category_to_title($m['category']); ?></td>
      <td style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
      <td style="text-align: center;"><a class="btn btn-mini" href="<?php print "$OTT_WWW/meetings/{$m['category']}/{$m['meetid']}"; ?>">Agenda</a></td>
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
  $meetings = getDatabase()->all(" select id,category,date(starttime) starttime,meetid from meeting where date(starttime) > date(CURRENT_TIMESTAMP) order by starttime ");
  foreach ($meetings as $m) {
    $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
    ?>
    <tr>
      <td><?php print meeting_category_to_title($m['category']); ?></td>
      <td style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
      <td style="text-align: center;"><a class="btn btn-mini" href="<?php print "$OTT_WWW/meetings/{$m['category']}/{$m['meetid']}"; ?>">Agenda</a></td>
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
  $meetings = getDatabase()->all(" select id,meetid,category,date(starttime) starttime from meeting where date(starttime) < date(CURRENT_TIMESTAMP) order by starttime desc limit 5 ");
  foreach ($meetings as $m) {
    $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
    ?>
    <tr>
      <td><?php print meeting_category_to_title($m['category']); ?></td>
      <td style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
      <td style="text-align: center;"><a class="btn btn-mini" href="<?php print "$OTT_WWW/meetings/{$m['category']}/{$m['meetid']}"; ?>">Agenda</a></td>
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

  <?php
  ottawaMediaRSS();
  ?>

  </div>

  <div class="span4">
  <script>
  function lobbyist_search_form_submit() {
    v = document.getElementById('lobbyist_search_value').value;
    if (v == '') {
      alert('Cannot perform an empty search');
      return;
    }
    document.location.href = 'lobbying/search/'+v;
  }
  </script>
  <h4>Recent Lobbying</h4>
  <?php
  $rows = getDatabase()->all(" 
    select
      f.id,f.lobbyist,f.issue
    from lobbyfile f
      join lobbying l on l.lobbyfileid = f.id
    group by f.id,f.lobbyist,f.issue
    order by
      max(l.created) desc
    limit 5
  ");
  ?>
  <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
  <?php 
  foreach ($rows as $r) {
    ?>
    <tr>
    <td><nobr><?php print $r['lobbyist']; ?></nobr></td>
    <td><a href="<?php print "lobbying/files/".$r['id']; ?>"><?php print $r['issue']; ?></a></td>
    </tr>
    <?php
  }
  ?>
  <tr>
  <td colspan="2"><center>
  <button class="btn" onclick="document.location.href = 'lobbying/search/'">Show All Lobbying</button>
  </center>
  </td>
  </tr>
  </table>
  <h4>Search Lobbyist Registry</h4>
  <div class="input-prepend input-append">
  <input type="text" id="lobbyist_search_value" placeholder="Search...">
  <button class="btn" onclick="lobbyist_search_form_submit()"><i class="icon-search"></i> Search</button>
  </div>

  <h4>Development Applications</h4>
  <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
  <?php
  $count = getDatabase()->one(" select count(1) c from devapp ");
  $count = $count['c'];
  $apps = getDatabase()->all(" select appid,devid,date(statusdate),apptype statusdate from devapp order by statusdate desc limit 1 ");
  $apps = getDatabase()->all(" select * from devapp order by updated desc limit 5 ");
  foreach ($apps as $a) {
    $url = DevelopmentAppController::getLinkToApp($a['appid']);
    $addr = json_decode($a['address']);
    $addrcount = count($addr);
    $addr = $addr[0];
    $addr = $addr->addr;
    ?>
    <tr>
    <td><small><a href="<?php print $url; ?>"><?php print $a['devid']; ?></a></small></td>
    <td><small><?php print $a['apptype']; ?></small></td>
    <td><small><?php print $addr; ?></small></td>
    </tr>
    <?php
    #print "<a href=\"$url\">{$a['devid']}</a> {$a['apptype']}: {$addr}<br/>";
    #pr($a);
  }
  ?>
  <tr>
  <td colspan="3">
  <center>
  <a href="devapps">View all <?php print $count; ?>  development applications</a>
  </center>
  </td>
  </tr>
  </table>


  </div>

  <div class="span4">
  <a class="twitter-timeline" data-dnt="true" href="https://twitter.com/ottwatch" data-widget-id="306310112971210752">Tweets by @ottwatch</a>
  <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
  </div>

  </div>
  <?php
  bottom();
}

function ideas() {
  top();
  ?>
  <h1>Got an idea for OttWatch?</h1>
  <h4>Let me know by leaving a (public) comment below.</h4>
  <p>
  OttWatch is focused on the political/governance area so I don't plan to 
  to transportation or recreation related things. You might also be interested in the
  ongoing <a href="http://http://www.apps4ottawa.ca/">Apps4Ottawa</a> contest.
  </p>
  <?php
  disqus();
  bottom();
}

function about() {
  top();
  include("about_content.html");
  bottom();
}

function home() {
}


function lobbyist($name) {
  # move to new REST location
  header("Location: ".OttWatchConfig::WWW."/lobbying/lobbyists/$name");
}

function error404() {
  top();
  ?>
  <div class="row-fluid">

  <div class="span4">&nbsp;</div>
  <div class="span4">
  <h1>Error!</h1>
  <h4>Somehow, you've found a page that does not work.</h4>
  <h5>I should put a fail-whale here or something.</h5>
  </div>
  <div class="span4">&nbsp;</div>

  </div>
  <?php
  bottom();
}

function top($title = '') {
  global $OTT_WWW;
?>
<!DOCTYPE html>
<html>
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
<script src="http://code.jquery.com/jquery.js" type="text/javascript"></script>
<script src="<?php print $OTT_WWW; ?>/bootstrap/js/bootstrap.min.js"></script>
<script>
function copyToClipboard (text) {
  window.prompt ("Copy to clipboard: Ctrl+C, Enter", text);
}
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
</head>
<body>

<div class="row-fluid">
<div class="span12">
<div class="navbar"><div class="navbar-inner">
<ul class="nav">
<li><a href="<?php print $OTT_WWW; ?>">Home</a></li>
<!--<li><a href="<?php print $OTT_WWW; ?>/dashboard">Dashboard</a></li>-->
<li><a href="<?php print $OTT_WWW; ?>/about">About</a></li>
<li><a href="<?php print $OTT_WWW; ?>/ideas">Ideas</a></li>
<li><a href="<?php print $OTT_WWW; ?>/api/about">API</a></li>
<?php
if (!LoginController::isLoggedIn()) {
  ?>
  <li><a href="<?php print $OTT_WWW; ?>/user/login">Login</a></li>
  <?php
} else {
  ?>
  <li><a href="<?php print $OTT_WWW; ?>/user/home"><?php print getSession()->get('user_email'); ?></a></li>
  <li><a href="<?php print $OTT_WWW; ?>/user/logout">Logout</a></li>
  <?php
}
?>
</ul>
</div></div>
</div>
</div>

<?php
	if ($title != '') {
    if (0) {
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
}

function bottom() {
  global $OTT_WWW;
  ?>
<div class="well">
<a href="<?php print $OTT_WWW; ?>"><img alt="Ottawa Watch Logo" style="float: right; padding-left: 5px; width: 50px; height: 50px;" src="<?php print $OTT_WWW; ?>/img/ottwatch.png"/></a>
<i>
Follow <b><a href="http://twitter.com/OttWatch">@OttWatch</a></b> on Twitter too.
Created by <a href="http://kevino.ca"><b>Kevin O'Donnell</b></a> to make it easier to be part of the political conversation in Ottawa.</i>
<div class="clearfix"></div>
</div>
  <?php
  googleAnalytics();
  ?>

    <script type="text/javascript">
    /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
    var disqus_shortname = 'ottwatch'; // required: replace example with your forum shortname

    /* * * DON'T EDIT BELOW THIS LINE * * */
    (function () {
        var s = document.createElement('script'); s.async = true;
        s.type = 'text/javascript';
        s.src = 'http://' + disqus_shortname + '.disqus.com/count.js';
        (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
    }());
    </script>

  </body>
  </html>
  <?php
}

?>



