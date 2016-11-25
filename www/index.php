<?php

error_reporting(E_ERROR | E_PARSE);

session_start();
date_default_timezone_set("Canada/Eastern");

require '../vendor/autoload.php';

include_once '../lib/include.php';
include_once 'epiphany/src/Epi.php';
include_once 'controllers/ApiController.php';
include_once 'controllers/ScrapeApiController.php';
include_once 'controllers/MeetingController.php';
include_once 'controllers/BylawController.php';
include_once 'controllers/GisController.php';
include_once 'controllers/GraphController.php';
include_once 'controllers/DevelopmentApp.php';
include_once 'controllers/LobbyistController.php';
include_once 'controllers/LoginController.php';
include_once 'controllers/UserController.php';
include_once 'controllers/ChartController.php';
include_once 'controllers/ElectionController.php';
include_once 'controllers/OpenDataController.php';
include_once 'controllers/StoryController.php';
include_once 'controllers/MfippaController.php';
include_once 'controllers/BudgetController.php';

Epi::setPath('base', 'epiphany/src');
Epi::init('route');
Epi::init('api');
Epi::init('route','session-php');

getRoute()->get('/story/(\d+)/(.*)', array('StoryController', 'show'));
getRoute()->get('/story/(\d+)$', array('StoryController', 'show'));
getRoute()->get('/story/list', array('StoryController', 'doList'));
getRoute()->get('/story/add', array('StoryController', 'add'));
getRoute()->get('/story/edit/(\d+)', array('StoryController', 'edit'));
getRoute()->post('/story/save', array('StoryController', 'save'));

getApi()->get('/api/search', array('ApiController', 'search'), EpiApi::external);

getApi()->get('/api/about', array('ApiController', 'about'), EpiApi::external);
getApi()->get('/api/point', array('ApiController', 'point'), EpiApi::external);
getApi()->get('/api/roads/search/(.*)', array('ApiController', 'roadSearch'), EpiApi::external);
getApi()->get('/api/roads/(\d+)/([^/]+)', array('ApiController', 'road'), EpiApi::external);
getApi()->get('/api/roads/(\d+)/([^/]+)/(.*)', array('ApiController', 'road'), EpiApi::external);
getApi()->get('/api/wards/(\d+)', array('ApiController', 'ward'), EpiApi::external);
getApi()->get('/api/wards/(\d+)/polls', array('ApiController', 'wardPolls'), EpiApi::external);
getApi()->get('/api/wards/(\d+)/polls/(\d+)/([\d-\.]+)', array('ApiController', 'wardPoll'), EpiApi::external);
getApi()->get('/api/wards/(\d+)/polls/(\d+)/([\d-\.]+)/map/live', array('ApiController', 'wardPollMapLive'), EpiApi::external);
getApi()->get('/api/wards/(\d+)/polls/(\d+)/([\d-\.]+)/map/static', array('ApiController', 'wardPollMapStatic'), EpiApi::external);
getRoute()->get('/api/wards/(\d+)/polls/(\d+)/([\d-\.]+)/map/img', array('ApiController', 'wardPollMapStatic302'), EpiApi::external);
getRoute()->get('/api/ward/full', array('ApiController', 'wardFullDump'));
getApi()->get('/api/wards', array('ApiController', 'listWards'), EpiApi::external);
getApi()->get('/api/committees', array('ApiController', 'committees'), EpiApi::external);
getApi()->get('/api/candidates', array('ApiController', 'candidates'), EpiApi::external);
getApi()->get('/api/councillors/(\d+)', array('ApiController', 'councillorById'), EpiApi::external);
getApi()->get('/api/councillors/([^/]+)/(.*)', array('ApiController', 'councillorByName'), EpiApi::external);
getApi()->get('/api/meetings/json', array('ApiController', 'meetingsJson'), EpiApi::external);
getApi()->get('/api/feed/', array('ApiController', 'feed'), EpiApi::external);
getApi()->get('/api/feed/(\d+)', array('ApiController', 'feed'), EpiApi::external);
getApi()->get('/api/feed/(\d+)/(\d+)', array('ApiController', 'feed'), EpiApi::external);
getApi()->get('/api/zoning/(-{0,1}[\d\.]+)/(-{0,1}[\d\.]+)', array('ApiController', 'zoning'), EpiApi::external);
getApi()->post('/api/inbound/traffic-incident', 'traffic_incident', EpiApi::external);
getRoute()->get('/api/widget/findward', array('ApiController', 'widgetFindWard'));
getRoute()->get('/api/widget/findriding', array('ApiController', 'widgetFindRiding'));
getApi()->get('/api/election/results', array('ApiController','electionResults'), EpiApi::external);

# Typeahead
getApi()->get('/api/typeahead/address', array('ApiController','typeaheadAddress'), EpiApi::external);
getApi()->get('/api/typeahead/postal', array('ApiController','typeaheadPostal'), EpiApi::external);

# Budget
getRoute()->get('/budget/search', array('BudgetController', 'searchAll'));
getRoute()->get('/budget/(\d+)/(capital)/(draft)/(\d+)', array('BudgetController', 'showEntry'));
getRoute()->get('/budget/(\d+)/(capital)/(draft)/search', array('BudgetController', 'search'));
getRoute()->get('/budget/(\d+)/(capital)/(adopted)/(\d+)', array('BudgetController', 'showEntry'));
getRoute()->get('/budget/(\d+)/(capital)/(adopted)/search', array('BudgetController', 'search'));

# Scraper API
getApi()->get('/api/scrape/item/(\d+)/votes', array('ScrapeApiController','itemVote'), EpiApi::external);
getApi()->get('/api/scrape/coa/item/all', array('DevelopmentAppController','apiScrapeCoaSireForItemIds'), EpiApi::external);
getApi()->get('/api/scrape/item/(\d+)', array('MeetingController','apiScrapeItem'), EpiApi::external);

getRoute()->get('/feed/', 'feed');

getRoute()->get('/mfippa/', array('MfippaController','doList'));
getRoute()->get('/mfippa/random', array('MfippaController','showRandom'));
getRoute()->get('/mfippa/(A-.*)', array('MfippaController','show'));
getRoute()->get('/mfippa/(\d+)', array('MfippaController','show'));
getRoute()->get('/mfippa/(\d+)/img', array('MfippaController','showImg'));
getRoute()->get('/mfippa/process/(A-\d+-\d+)', array('MfippaController','process'));

getRoute()->get('/opendata/*', array('OpenDataController','doList'));

getApi()->get('/api/lobbying/all/csv', array('ApiController', 'lobbyingAllCsv'), EpiApi::external);

getApi()->get('/api/devapps/all', array('ApiController', 'devAppAll'), EpiApi::external);
getApi()->get('/api/devapps/([D_].*)', array('ApiController', 'devApp'), EpiApi::external);

getRoute()->get('/twilio/feed/latest/voice', 'twilioFeedLatestVoice');
getRoute()->get('/twilio/feed/latest/sms', 'twilioFeedLatestSMS');

getRoute()->get('/', 'dashboard');
getRoute()->get('/about', 'about');
#getRoute()->get('/ideas', 'ideas');

getRoute()->get('/user/home', array('UserController','home'));
getRoute()->post('/user/update', array('UserController','update'));
getRoute()->get('/user/email/sendVerify', array('UserController','emailSendVerify'));
getRoute()->get('/user/email/verify/(.*)', array('UserController','emailVerify'));
getRoute()->post('/user/add/place', array('UserController','addPlace'));

getRoute()->get('/user/register', array('LoginController','displayRegister'));
getRoute()->post('/user/register', array('LoginController','doRegister'));
getRoute()->get('/user/login', array('LoginController','display'));
getRoute()->post('/user/login', array('LoginController','doLogin'));
getRoute()->get('/user/logout', array('LoginController','logout'));

# OAUTH
getRoute()->get('/user/login/twitter', array('LoginController','twitter'));
getRoute()->get('/user/login/facebook', array('LoginController','facebook'));
getRoute()->get('/user/login/facebook/managepages', array('LoginController','facebookManagePages'));

getRoute()->get('/lobbying/latereport', array('LobbyistController','latereport'));
getRoute()->get('/lobbying/search/(.*)', array('LobbyistController','search'));
getRoute()->get('/lobbying/lobbyists/(.*)', array('LobbyistController','showLobbyist'));
getRoute()->get('/lobbying/clients/(.*)', array('LobbyistController','showClient'));
getRoute()->get('/lobbying/thelobbied/(.*)', array('LobbyistController','showLobbied'));
getRoute()->get('/lobbying/files/(.*)', array('LobbyistController','showFile'));
getRoute()->get('/lobbyist/([^\/]*)', 'lobbyist'); # legacy REST location

#getRoute()->get('/lobbyist/(.*)/details', 'lobbyistDetails');
#getRoute()->get('/lobbyist/(.*)/link', 'lobbyistLink');

getRoute()->get('/devapps', array('DevelopmentAppController','listAll'));
getRoute()->get('/devapps/(D.*)', array('DevelopmentAppController','viewDevApp'));

getRoute()->get('/bylaws/(\d\d\d\d-\d+)', array('BylawController','show'));
getRoute()->get('/bylaws', array('BylawController','listAll'));
getRoute()->get('/bylaws/', array('BylawController','listAll'));

getRoute()->get('/meetings/votes', array('MeetingController','votesIndex'));
getRoute()->get('/meetings/votes/member/([^\/]*)', array('MeetingController','votesMember'));
getRoute()->get('/meetings/votes/(\d+)', array('MeetingController','voteDisplay'));
getRoute()->get('/meetings/votes/report/closeVotes', array('MeetingController','reportCloseVotes'));
getRoute()->get('/meetings/votes/report/likeness', array('MeetingController','reportLikeness'));

getRoute()->get('/graph/force', array('GraphController','graphForce'));

getApi()->get('/meetings/votes/report/likeness/graph/data.json', array('MeetingController', 'graphLikenessJSON'), EpiApi::external);

getRoute()->get('/meetings/dump/all', array('MeetingController','dump'));
getRoute()->get('/meetings/calendar', array('MeetingController','calendarView'));
getRoute()->get('/meetings/calendar.ics', array('MeetingController','calendar'));
getRoute()->get('/meetings/file/(\d+)/(.*)', array('MeetingController','getFileCacheUrl'));
getRoute()->get('/meetings/file/(\d+)', array('MeetingController','getFileCacheUrl'));
getRoute()->get('/meetings', array('MeetingController','dolist')); // meetings
getRoute()->get('/meetings/([^\/]*)', array('MeetingController','dolist')); // meetings/CATEGORY
getRoute()->get('/meetings/meetid/(\d+)', array('MeetingController','meetidForward')); // meetings/CATEGORY/ID
getRoute()->get('/meetings/([^\/]*)/(\d+)', array('MeetingController','meetingDetails')); // meetings/CATEGORY/ID
getRoute()->get('/meetings/([^\/]*)/(\d+)/item/(\d+)', array('MeetingController','meetingDetails')); // meetings/CATEGORY/ID
getRoute()->get('/meetings/([^\/]*)/(\d+)/item/(\d+)/(files|files.json)', array('MeetingController','itemFiles')); // meetings/CATEGORY/ID

getRoute()->get('/chart/test', array('ChartController','test'));
getRoute()->get('/chart/lobbying/weighted/(\d+)', array('ChartController','lobbyingWeightedActivity'));
getRoute()->get('/chart/lobbying/(daily)', array('ChartController','lobbyingDaily'));
getRoute()->get('/chart/lobbying/(daily)/(\d+)', array('ChartController','lobbyingDaily'));
getRoute()->get('/chart/lobbying/(monthly)', array('ChartController','lobbyingDaily'));
getRoute()->get('/chart/lobbying/(monthly)/(\d+)', array('ChartController','lobbyingDaily'));

getRoute()->get('/consultations', array('ConsultationController','showMain'));
getRoute()->get('/consultations/', array('ConsultationController','showMain'));
getRoute()->get('/consultations/(\d+)', array('ConsultationController','showConsultation'));
getRoute()->get('/consultations/(\d+)/content', array('ConsultationController','showConsultationContent'));

getRoute()->get('/md5hist/([a-z0-9]*)', 'md5hist');

getRoute()->get('/election/*', array('ElectionController','showMain'));
getRoute()->get('/election/candidates.csv', array('ElectionController','candidatesCSV'));
getRoute()->get('/election/tools', array('ElectionController','showTools'));
getRoute()->get('/election/(mayor)/', array('ElectionController','showRace'));
getRoute()->get('/election/ward/(\d+)', array('ElectionController','showRace'));
getRoute()->get('/election/ward/(\d+)/map', array('ElectionController','showWardMap'));
getRoute()->get('/election/processReturn/*', array('ElectionController','processReturn'));
getRoute()->get('/election/processReturn/(\d+)', array('ElectionController','processReturn'));
getRoute()->get('/election/processDonation/scoreboard', array('ElectionController','processDonationScoreboard'));
getRoute()->get('/election/processDonation/*', array('ElectionController','processDonation'));
getRoute()->post('/election/processDonation/*', array('ElectionController','processDonationSave'));
getRoute()->get('/election/listDonations', array('ElectionController','listDonations'));
getRoute()->get('/election/donor/(\d+)', array('ElectionController','showDonor'));
getRoute()->get('/election/donation/(\d+)', array('ElectionController','showDonation'));
getRoute()->get('/election/tmp', array('ElectionController','tmp'));

getRoute()->get('/election/candidate/(\d+)', array('ElectionController','showCandidate'));

getRoute()->get('/election/question/(\d+)/(.*)', array('ElectionController','showQuestion'));
getRoute()->get('/election/question/add', array('ElectionController','questionAdd'));
getRoute()->get('/election/question/list', array('ElectionController','questionList'));
getRoute()->post('/election/question/add', array('ElectionController','questionAddPost'));
getRoute()->post('/election/question/vote', array('ElectionController','questionVote'));
getRoute()->post('/election/question/answer', array('ElectionController','saveAnswer'));

getRoute()->get('/election/(\d+)/race/(\d+)/results/', array('ElectionController','raceResults'));

getRoute()->get('/gis/viewLayer', array('GisController','viewLayer'));

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

function traffic_incident() {
	$dump = print_r($_POST,TRUE);
	error_log("traffic POST data: $dump ");
	$dump = print_r($_GET,TRUE);
	error_log("traffic GET data: $dump ");
	$postdata = file_get_contents("php://input");
	error_log("traffic raw: $postdata ");
}

function feed() {
  top('Activity Feed');
  $count = $_GET['count']; if ($count == '') { $count = 100; }
  $before = $_GET['before']; 
  if ($before == '') {
    $recent = getApi()->invoke("/api/feed/$count");
  } else {
    $recent = getApi()->invoke("/api/feed/$count/$before");
  }
  $before = $recent['next']['before'];
  $count = $recent['next']['count'];
  $next = OttWatchConfig::WWW."/feed/?count=$count&before=$before";
  ?>
  <h1>Activity Feed</h1>
  <p><a href="<?php print $next; ?>">Next <?php print $count; ?> entries</a></p>
  <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
  <?php
  foreach ($recent['items'] as $r) {
    $type = '';
    $matches = array();
    $type = explode('/',$r['path']);
    $type = $type[1];
    ?>
    <tr>
    <td><?php print $type; ?></td>
    <td><nobr><?php print $r['diff']; ?></nobr></td>
    <td><a href="<?php print $r['url']; ?>"><?php print $r['message']; ?></a></td>
    <td><nobr><?php print $r['created']; ?></nobr></td>
    </tr>
    <?php
  }
  ?>
  </table>
  <p><a href="<?php print $next; ?>">Next <?php print $count; ?> entries</a></p>
  <?php
  bottom();
}

function DEPRECATED_adsense_adaptive() {
?>

<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- ottwatch-homepage -->
<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-5904054241204672" data-ad-slot="8106870146" data-ad-format="auto"></ins>
<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>

<?php
}

function dashboard() {
  global $OTT_WWW;
  top("OttWatch - watching ottawa.ca to save you time",false,false)
  ?>

  <div class="row-fluid">
  <div class="span4">

  <p class="lead">
  <b>OttWatch</b> is dedicated to making it easier to be 
  part of the political conversation in Ottawa. 
  <a href="about">Read about all the features</a>.

  <div style="margin-top: 10px; background: #08c; color: #ffffff; padding: 20px; font-size: 125%; border-radius: 4px;">
  <center>
	<?php
  $row = getDatabase()->one(" select * from story where deleted = 0 and published = 1 order by updated desc limit 1 ");
	$row['body'] = '';
	print "<a style=\"color: #ffffff;\" href=\"/story/{$row['id']}\">";
	print "<b>Latest Story</b>: {$row['title']}";
	print "</a>";
	?>
  </center>
  </a>
  </div>

  <div style="margin-top: 10px; background: #08c; color: #ffffff; padding: 20px; font-size: 125%; border-radius: 4px;">
  <center>
	<?php
  $row = getDatabase()->one(" select * from story where deleted = 0 and published = 1 order by updated desc limit 1 ");
	$row['body'] = '';
	print "<a style=\"color: #ffffff;\" href=\"/election/listDonations\">";
	print "Campaign Donation Database";
	print "</a>";
	?>
  </center>
  </a>
  </div>

  <table class="table table-bordered table-hover table-condensed" style="width: 100%; margin-top: 20px;">
  <?php 
  $meetings = getDatabase()->all(" select id,meetid,category,date(starttime) starttime from meeting where date(starttime) = date(CURRENT_TIMESTAMP) order by starttime ");
  if (count($meetings) > 0) {
	  ?>
	  <tr>
	  <td colspan="3">
	  <h4>Today's Meetings</h4>
	  </td>
	  </tr>
	  <?php
	  foreach ($meetings as $m) {
	    $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
	    ?>
	    <tr>
	      <td><?php print meeting_category_to_title($m['category']); ?></td>
	      <td style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
	      <td style="text-align: center;"><a class="btn btn-mini" href="<?php print "$OTT_WWW/meetings/meeting/{$m['meetid']}"; ?>">Agenda</a></td>
	    </tr>
	    <?php
	  }
  }
  # sometimes ottawa.ca ppl create meetings *way* in advance for testing purposes.
  # only look 2 months in advance. Typically meetings aren't created until 2 wks in advance anyway
  $meetings = getDatabase()->all(" 
    select id,category,date(starttime) starttime,meetid 
    from meeting 
    where 
      date(starttime) > date(CURRENT_TIMESTAMP) 
      and datediff(starttime,current_timestamp()) < 60
    order by starttime ");
  if (count($meetings) > 0) {
	  ?>
	  <tr>
	  <td colspan="3">
	  <h4>Upcoming Meetings</h4>
	  </td>
	  </tr>
	  <?php
	  foreach ($meetings as $m) {
	    $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
	    ?>
	    <tr itemscope="" itemtype="http://data-vocabulary.org/Event">
	      <td itemprop="summary"><?php print meeting_category_to_title($m['category']); ?></td>
	      <td itemprop="startDate" datetime="<?php print $m['starttime']; ?>" style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
	      <td style="text-align: center;"><a itemprop="url" class="btn btn-mini" href="<?php print "$OTT_WWW/meetings/meeting/{$m['meetid']}"; ?>">Agenda</a></td>
	    </tr>
	    <?php
	  }
  }
  ?>
  <tr>
  <td colspan="3">
  <h4>Previous Meetings</h4>
  </td>
  </tr>
  <?php
  $meetings = getDatabase()->all(" select id,meetid,category,date(starttime) starttime from meeting where date(starttime) < date(CURRENT_TIMESTAMP) order by starttime desc limit 3 ");
  foreach ($meetings as $m) {
    $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
    ?>
    <tr>
      <td><?php print meeting_category_to_title($m['category']); ?></td>
      <td style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
      <td style="text-align: center;"><a class="btn btn-mini" href="<?php print "$OTT_WWW/meetings/meeting/{$m['meetid']}"; ?>">Agenda</a></td>
    </tr>
    <?php
  }
  ?>
  <tr>
  <td colspan="3">
  <a class="btn-mini btn" href="<?php print $OTT_WWW; ?>/meetings/all"><i class="icon-list"></i> All Meetings</a>
  <a class="btn-mini btn" href="<?php print $OTT_WWW; ?>/meetings/calendar"><i class="icon-calendar"></i> Calendar</a>
  </td>
  </tr>
  </table>

  <?php
  // ottawaMediaRSS();
  ?>

  </div>



  <div class="span4">

  <script>
  function devapp_search_form_submit() {
    v = document.getElementById('devapp_search_value').value;
    if (v == '') {
      alert('Cannot perform an empty search');
      return;
    }
    document.location.href = 'devapps?since=999&match=' + encodeURIComponent(v);
  }
  function lobbyist_search_form_submit() {
    v = document.getElementById('lobbyist_search_value').value;
    if (v == '') {
      alert('Cannot perform an empty search');
      return;
    }
    document.location.href = 'lobbying/search/'+encodeURIComponent(v);
  }
  </script>
  <h4>Lobbyist Registry <small>(<a onclick="javascript:document.location.href = 'lobbying/search/?recent=30'">recent</a>,<a onclick="javascript:document.location.href = 'lobbying/search/'">all</a>)</small></h4>
  <div class="input-prepend input-append">
  <input type="text" id="lobbyist_search_value" placeholder="Search Lobbyist Registry...">
  <button class="btn" onclick="lobbyist_search_form_submit()"><i class="icon-search"></i> Search</button>
  </div><!-- /search lobbying -->

  <h4>Development Applications <small>(<a href="devapps?since=7">recent</a>,<a href="devapps?since=999">all</a>)</small></h4>
  <div class="input-prepend input-append">
  <input type="text" id="devapp_search_value" placeholder="Search Dev Apps..."/>
  <button class="btn" onclick="devapp_search_form_submit()"><i class="icon-search"></i> Search</button>
  </div><!-- /search devapps -->
	<br/>


  <h4>More Reports and Data</h4>
	<ul>
	<li><a href="<?php print $OTT_WWW; ?>/consultations/">Consultations</a>: A complete list of public consultations from ottawa.ca</li>
	<li><a href="<?php print $OTT_WWW; ?>/election/">Election 2014</a>: Everything election related!</li>
	<li><a href="<?php print $OTT_WWW; ?>/lobbying/latereport">Late Lobbying Report</a>: Who's been naughty and failed to report lobbying activity within the required deadlines.</li>
	<li><a href="<?php print $OTT_WWW; ?>/chart/lobbying/weighted/30">Lobbying Intensity Report</a>: See what companies are most active pushing their agenda at City Hall.</li>
	<li><a href="<?php print $OTT_WWW; ?>/mfippa/">MFIPPA</a>: Freedom of Information requests processed by the City.</li>
	<li><a href="<?php print $OTT_WWW; ?>/bylaws/">By-Laws</a>: Archive of bylaws as they are enacted.</li>
	<li><a href="<?php print $OTT_WWW; ?>/opendata/">OpenData</a>: Most recently updated data from <i>data.ottawa.ca</i>.</li>
	<li><a href="<?php print $OTT_WWW; ?>/story/list">Stories</a>: Original articles by OttWatch</li>
	<li><a href="<?php print $OTT_WWW; ?>/meetings/votes">Voting History</a>: See all votes at committee and council.</li>
	<li><a href="<?php print $OTT_WWW; ?>/api/about">API</a>: Documentation on the application programming interface for OttWatch.</li>
	<li><a href="<?php print $OTT_WWW; ?>/about">About</a>: What's this all about?</li>
	</ul>

  <h4>User</h4>
	<ul>
	<?php
	if (!LoginController::isLoggedIn()) {
	  ?>
	  <li><a href="<?php print $OTT_WWW; ?>/user/login">Login</a>: Log in to OttWatch for user-specific features</li>
	  <?php
	} else {
	  ?>
	  <li><a href="<?php print $OTT_WWW; ?>/user/home">User Profile</a>: About you.</li>
	  <li><a href="<?php print $OTT_WWW; ?>/user/logout">Logout</a>: Get out of here.</li>
	  <?php
	}
	?>
	</ul>

  </div>
  <div class="span4">
  <h4>Recent Comments</h4>
  <?php disqusRecent(8); ?>
  </div>
  </div>


  <h4>Activity Feed</h4>

  <table class="table table-bordered table-hover table-condensed" style="width: 100%;">

  <?php
  $recent = getApi()->invoke("/api/feed/30");
  foreach ($recent['items'] as $r) {
    ?>
    <tr>
    <?php
    $url = OttWatchConfig::WWW.$r['path'];
    $message = $r['message'];
    ?>
    <td><nobr><?php print $r['diff']; ?></nobr></td>
    <td><a href="<?php print $url; ?>"><?php print $message; ?></a></td>
    </tr>
    <?php
  }
  ?>
  <tr>
  <td></td>
  <td><a href="feed/">See all</a></td>
  </tr>
  </table>

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

function top3($title = '',$quiet = false, $menu = true) {
	top_common(1,$title,$quiet,$menu);
	?>
	<?php
}

function top($title = '',$quiet = false, $menu = true) {
	top_common(0,$title,$quiet, $menu);
}

function top_common($v3, $title = '',$quiet = false, $menu = true) {
  global $OTT_WWW;
?>
<!DOCTYPE html>
<html>
<head>
<title><?php print $title; ?></title>
<meta property="twitter:account_id" content="1512911885" /><!-- ads.twitter -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php 
if ($v3) {
	?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap-theme.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
	<?php
} else {
	?>
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
	<link href="<?php print $OTT_WWW; ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen" type="text/css">
	<link href="<?php print $OTT_WWW; ?>/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
	<script src="<?php print $OTT_WWW; ?>/jquery.js" type="text/javascript"></script>
	<script src="<?php print $OTT_WWW; ?>/bootstrap/js/bootstrap.min.js"></script>
	<?php
}
?>

<link rel="stylesheet" type="text/css" href="/diff2html/diff2html.css">
<script type="text/javascript" src="/diff2html/diff2html.js"></script>
<script type="text/javascript" src="/diff2html/diff2html-ui.js"></script>

<script src="<?php print $OTT_WWW; ?>/bootstrap-ajax-typeahead/js/bootstrap-typeahead.js" type="text/javascript"></script>
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<style type="text/css">
	<?php 
	if ($v3) {
		?>
	html { 
		position: relative; 
		min-height: 100%; 
	}
  body {
		margin-bottom: 60px;
	}
		<?php
	} else {
		?>
  body {
		padding: 20px;
	}
		<?php
	}
	?>
	.footer {
	  position: absolute;
	  bottom: 0;
	  width: 100%;
	  /* Set the fixed height of the footer here */
	  height: 60px;
	  background-color: #f5f5f5;
	}
	.container {
	  width: auto;
	  padding: 0 15px;
	}
	.ottwatchstorybody {
		font-size: 110%;
		line-height: 140%;
		color: #565656;
	}
	.fb-like {
		vertical-align: top;
	}
	.top-action-alert {
		text-align: center;
		width: 100%;
		border: solid 1px #e12014;
		background: #e12014;
		color: #ffffff;
		border-radius: 3px;
		font-size: 125%;
		padding-top: 10px;
		padding-bottom: 10px;
		margin-bottom: 5px;
	}
	.top-action-alert a {
		color: #ffffff;
	}

	.rotate {
		-webkit-transform: rotate(-90deg);
		-ms-transform: rotate(-90deg);
		transform: rotate(-90deg);
		height: 200px;
	}

	.processDonation-help {
		padding-left: 40px;
		font-size: 90%;
	}

</style>
<script>
function copyToClipboard (text) {
  window.prompt ("Copy to clipboard: Ctrl+C, Enter", text);
}
function voteOnQuestion(p,i,v) {
	$.post( '/election/question/vote', 
		{ 
			ajax: 1, 
			id: i, 
			vote: v
		}, function( data ) {
			$('#'+p+'Tally').html(data.tally);
			$('#'+p+'Votes').html(data.votes);
			$('#'+p+'Result').html('<p>Vote recorded!</p>');
		},'json'
	);
}
</script>
</head>
<body>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=204783589569220";
          fjs.parentNode.insertBefore(js, fjs);
          }(document, 'script', 'facebook-jssdk'));</script>

<?php
if ($quiet) { return; }
$remaining = getDatabase()->one(" select count(1) c from candidate_donation where amount is null ");
$remaining = $remaining['c'];
#$remaining = 0;

if ($v3) {
	?>

	<nav class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/">OttWatch</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
				<?php
				if (!LoginController::isLoggedIn()) {
				  ?>
				  <li><a href="<?php print $OTT_WWW; ?>/user/login?next=<?php print urlencode($_SERVER['REQUEST_URI']); ?>">Login</a></li>
				  <?php
				} else {
				  ?>
				  <li><a href="<?php print $OTT_WWW; ?>/user/home">Profile</a></li>
				  <li><a href="<?php print $OTT_WWW; ?>/user/logout?next=<?php print $_SERVER['REQUEST_URI']; ?>">Logout</a></li>
				  <?php
				}
				?>
				<li><a href="<?php print $OTT_WWW; ?>/about">About</a></li>
				<li>
					<?php
					{
				  $row = getDatabase()->one(" select * from story where deleted = 0 and published = 1 order by updated desc limit 1 ");
					print "<a href=\"/story/{$row['id']}\">";
					print "<b>Latest Story: {$row['title']}</b>";
					print "</a>";
					}
					?>
				</li>
				<?php
				if (true) { // if (false && $remaining == 0) {
					?>
					<!--<li><a style="" href="<?php print $OTT_WWW; ?>/election/processDonation/">0 donations remaining</a></li>-->
					<?php
				} else {
					?>
					<li><a style="font-weight: bold; color: #f00;" href="<?php print $OTT_WWW; ?>/election/processDonation/">HELP: <?php print $remaining; ?> donations left to scan!</a></li>
					<?php
				}
				?>
			</ul>
		</div>
	</div>
	</nav>
	<div class="container">

	<?php
} elseif ($menu == true) {
	?>
	<div class="row-fluid">
	<div class="span12">
	<div class="navbar">
	<div class="navbar-inner">
	<ul class="nav">
	<li><a href="<?php print $OTT_WWW; ?>">Home</a></li>
	<?php
	if (!LoginController::isLoggedIn()) {
	  ?>
	  <li><a href="<?php print $OTT_WWW; ?>/user/login?next=<?php print urlencode($_SERVER['REQUEST_URI']); ?>">Login</a></li>
	  <?php
	} else {
	  ?>
	  <li><a href="<?php print $OTT_WWW; ?>/user/home">Profile</a></li>
	  <li><a href="<?php print $OTT_WWW; ?>/user/logout?next=<?php print $_SERVER['REQUEST_URI']; ?>">Logout</a></li>
	  <?php
	}
	?>
	<li><a href="<?php print $OTT_WWW; ?>/about">About</a></li>
	<li>
		<?php
		{
	  $row = getDatabase()->one(" select * from story where deleted = 0 and published = 1 order by updated desc limit 1 ");
		print "<a href=\"/story/{$row['id']}\">";
		print "<b>Latest Story: {$row['title']}</b>";
		print "</a>";
		}
		?>
	</li>
	<?php
	if (true) { // if (false && $remaining == 0) {
		?>
		<!--<li><a style="" href="<?php print $OTT_WWW; ?>/election/processDonation/">0 donations remaining</a></li>-->
		<?php
	} else {
		?>
		<li><a style="font-weight: bold; color: #f00;" href="<?php print $OTT_WWW; ?>/election/processDonation/">HELP: <?php print $remaining; ?> donations left to scan!</a></li>
		<?php
	}
	?>
	</ul>
	</div>
	</div>
	</div>
	</div>
	<?php
	}

}

function bottom3($quiet = false) {
	bottom_common(1,$quiet);
}

function bottom($quiet = false) {
	bottom_common(0,$quiet);
}

function bottom_common($v3, $quiet) {
  global $OTT_WWW;

  if ($v3) {
		?>
		</div><!-- .container -->
		<?php
	}

  if (!$quiet && $v3) {
		?>
		<footer class="footer" style="padding: 10px;">
		<div class="row">
			<div class="col-sm-3 text-center">
				<b>Be part of the political conversation in Ottawa.</b>
			</div>
			<div class="col-sm-3 text-center">
				Created by <a href="http://kevino.ca"><b>Kevin O'Donnell</b></a>
			</div>
			<div class="col-sm-3 text-center">
				Follow <a href="http://twitter.com/OttWatch">@OttWatch</a></b> and <b><a href="http://twitter.com/ODonnell_K">@ODonnell_K</a>
			</div>
			<div class="col-sm-3 text-center">
				<a href="<?php print $OTT_WWW; ?>"><img style="width: 50px; height: 50px;" src="<?php print $OTT_WWW; ?>/img/ottwatch.png"/></a>
			</div>
		</div>
		</footer>
		<?php
	}
  if (!$quiet && !$v3) {
	  ?>
		<div class="well" style="margin-top: 10px;" >
		<a href="<?php print $OTT_WWW; ?>"><img style="float: right; padding-left: 5px; width: 50px; height: 50px;" src="<?php print $OTT_WWW; ?>/img/ottwatch.png"/></a>
		<i>Created by <a href="http://kevino.ca"><b>Kevin O'Donnell</b></a> to make it easier to be part of the political conversation in Ottawa.</i><br/>
		On Twitter? Follow <b><a href="http://twitter.com/OttWatch">@OttWatch</a></b> and <b><a href="http://twitter.com/ODonnell_K">@ODonnell_K</a></b>
		<div class="clearfix"></div>
		</div>

	  <?php
  }

  googleAnalytics();

  ?>

	<script type="text/javascript">
	var disqus_shortname = 'ottwatch'; // required: replace example with your forum shortname
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

/*
 * Show $count latest Disqus comments from the comment cache
 */

function disqusRecent ($count) {
  $json = file_get_contents(OttWatchConfig::FILE_DIR.'/disqus/posts.json');
  $data = json_decode($json);
  $x = 0;

  foreach ($data->response as $r) {

    if ($x++ == $count) {
      return;
    }
    if ($x >= 6) {
			return;
		}
    
    $name = $r->author->name;
    $message = substr($r->raw_message,0,100);
    $message .= "...";
    $message = preg_replace('/\+/','+ ',$message);
    $createdAt = date('M d',strtotime($r->createdAt));
    $thread = $r->thread;
    $thread = json_decode(file_get_contents(OttWatchConfig::FILE_DIR."/disqus/$thread.json"));
    $link = $thread->response->link;
    $title = $thread->response->title;

    ?>
    <p>
    <b><?php print $name; ?></b> 
    commented on 
    <i><a href="<?php print $link; ?>"><?php print $title; ?></a></i>
    (<?php print $createdAt; ?>):
    "<?php print $message; ?>"
    <a href="<?php print $link; ?>"><i class="icon-share"></i></a>
    </p>
    <?php

  }
}

function mapToTable($o) {
	?>
  <table class="table table-bordered table-hover table-condensed">
	<?php
	foreach ($o as $k=>$v) {
		?>
		<tr>
			<th><?php print $k; ?></th>
			<td><?php print $v; ?></td>
		</tr>
		<?php
	}
	?>
	</table>
	<?php
}

function md5hist ($md5) {
	top3();

	$rows = getDatabase()->all(" select * from md5hist where curmd5 = :md5 ",array('md5'=>$md5));
	if (count($rows) == 0) {
		print "End of the line; OttWatch doesn't have a cached copy of that version of the document.";
		bottom3();
		return;
	}
	if (count($rows) > 1) {
		print "Collision on MD5 hash happened, which shouldn't, so Kevin screwed up; maybe tweet this URL to @odonnell_k please?";
		bottom3();
		return;
	}
	$row = $rows[0];
	if ($row['prevmd5'] == '') {
		print "This is the first version; can't compare to before the beginning of time!";
		bottom3();
		return;
	}

	?>
	<h1>Document MD5: <?php print $row['curmd5']; ?></h1>
	<ul>
	<li>Created: <?php print $row['created']; ?></li>
	<li>Previous diff: <a href="/md5hist/<?php print $row['prevmd5']; ?>"><?php print $row['prevmd5']; ?></a></li>
	<li>Jump down to the <a href="#line-by-line">line-by-line</a> comparison.</li>
	</ul>
	<?php

	$file1 = OttWatchConfig::FILE_DIR."/consultationmd5/".$row['prevmd5'];
	$file2 = OttWatchConfig::FILE_DIR."/consultationmd5/".$row['curmd5'];
	$cmd = "diff -u $file1 $file2";
	#$cmd = "diff -u /mnt/shared/ottwatch/var/consultationmd5/b28bd0cf99eb78067347ad130f89e0a5 /mnt/shared/ottwatch/var/consultationmd5/f8e160e7a13d3cf2ddbb10c2051c5931";

	$diff = `$cmd`;
	#pr(htmlspecialchars($diff));

	?>
	<script>
	lineDiffExample
	var lineDiffExample = <?php echo json_encode($diff); ?>;

	$(document).ready(function() {
		var diff2htmlUi = new Diff2HtmlUI({diff: lineDiffExample});
		diff2htmlUi.draw('#side-by-side', { inputFormat: 'json', showFiles: false, matching: 'words', outputFormat: 'side-by-side', synchronisedScroll: true });
		diff2htmlUi.draw('#line-by-line', { inputFormat: 'json', showFiles: false, matching: 'words', outputFormat: 'line-by-line', synchronisedScroll: true });
	});
	</script>

	<h2>Side-by-Side</h2>
	<div id="side-by-side"></div>
	<h2>Line-by-Line</h2>
	<div id="line-by-line"></div>

	<?php
	bottom3();
}

function twilioFeedLatestVoice() {
	$msg = feedLatest();
	?>
	<Response><Say><?php print $msg; ?></Say></Response>
	<?php
}

function twilioFeedLatestSMS() {
	$msg = feedLatest();
	?>
	<Response><Message><?php print $msg; ?></Message></Response>
	<?php
}

function feedLatest() {
	$row = getDatabase()->one(" select * from feed order by id desc limit 1 ");
	return "{$row['message']} {$row['url']}";
}

?>




