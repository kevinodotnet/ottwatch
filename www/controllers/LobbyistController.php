<?php

class LobbyistController {

	# base URL of the lobbyist registry
	const URL = "https://apps107.ottawa.ca/LobbyistRegistry/search/searchlobbyist.aspx?lang=en";

  #################################################################################################
  # GUI
  #################################################################################################

  public static function latereport() {
    top("Late Lobbying Report");

		?>
		<div class="row-fluid">
			<div class="span6 offset3">

	    <h1>Late Lobbying Report</h1>

			<h5>This report is no longer available.</h5>

			<p>Ottawa's Integrity Commissioner has confirmed for Ottwatch that at least two "reported on"
			dates for lobbying activities were wrong in the Ottwatch data. This puts the entire "late lobbying
			report" on thin ice since I can't know what other records could be wrong.</p>

			<p>I would like to believe the overwhelming majority of the activities were correct (<a href="/story/7/inside-the-lobbyist-registry">my own was</a>).</p>
			
			<p>But since I can't double-check the data, and now I've been told at least some of it
			was wrong, it all has to go.</p>

			<p>Hopefully in future the official Lobbyist Registry will expose the "reported on" date 
			to the public. I think it's valuable information to know which lobbyists fail to be compliant
			from day one - or which veteran lobbyists skip a prompt filing.</p>
			
			<p>I've previously suggested making the "reported on" date available to the Integry Comissioner.</p>

			<p>Kevin<br/>January, 2014</p>

			</div>
		</div>
		<?php

		bottom();
		return;

		/**
		 * Seems OttWatch date detection isn't working right. Information commissioner confirms this
		 * in an email. So, this feature is dead. Would be nice if the city registry would simply
		 * publish the reported-on-date, no?
		 *
		 * Baby steps...
		 */

    $rows = getDatabase()->all("
      select 
        ll.diff,
        left(l.lobbydate,10) lobbydate,
        left(l.created,10) created,
        l.lobbied,
        l.activity,
        f.lobbyist,
        f.client,
        f.issue,
        f.id as lobbyfileid,
        l.id as lobbyid
      from latelobbying ll 
        join lobbying l on l.id = ll.id
        join lobbyfile f on f.id = l.lobbyfileid
      order by
        l.created desc,
        l.id desc
    ");

    ?>
    <div class="row-fluid">
    <div class="span6">
    <h1>Late Lobbying Report</h1>
    </div>
    <div class="span6">
    <p>
    * Lobbyists are required to report their activities within <b>15 business days</b>.
    This report shows all lobbying that was reported on or after <b>24 calendar days</b> 
    had elapsed. In most cases this indicates a late report - unless the lobbyist was 
    afforded additional time due to statuatory holidays. 
    In other words, before getting excited that somebody failed to report a lobbying,
    check your favourite holiday calendar.
    </p>
    </div>
    </div>

    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <tr>
    <th>Calendar Days*</th>
    <th>Lobbying On</th>
    <th>Lobbyist</th>
    <th>Client</th>
    <th>Activity</th>
    <th>Lobbied</th>
    <th>Issue</th>
    </tr>
    <?php

    foreach ($rows as $r) { 
      ?>
      <tr>
      <td><?php print $r['diff']; ?></td>
      <td><nobr><?php print $r['lobbydate']; ?></nobr></td>
      <td><a href="<?php print OttWatchConfig::WWW."/lobbying/lobbyists/{$r['lobbyist']}"; ?>"><nobr><?php print $r['lobbyist']; ?></nobr></a></td>
      <td><a href="<?php print OttWatchConfig::WWW."/lobbying/clients/{$r['client']}"; ?>"><?php print $r['client']; ?></a></td>
      <td><?php print $r['activity']; ?></td>
      <td><?php print $r['lobbied']; ?></td>
      <td><a href="<?php print OttWatchConfig::WWW."/lobbying/files/{$r['lobbyfileid']}"; ?>"><?php print $r['issue']; ?></td>
      </tr>
      <?php
    }

    ?>
    </table>
    <?php

    bottom();
  }

  public static function getUrlForIssue ($id,$issue) {
    return "<a href=\"".OttWatchConfig::WWW."/lobbying/files/{$id}\">{$issue}</a>";
  }

  public static function getLongestRunningLobbyingFile() {
		$sql = "
			select 
        f.id,
			  datediff(max(i.lobbydate),min(i.lobbydate)) days
			from lobbyfile f 
			  join lobbying i on i.lobbyfileid = f.id
			group by f.id
			order by datediff(max(i.lobbydate),min(i.lobbydate)) desc
			limit 1
		";
    $row = getDatabase()->one($sql);
    return $row;
  }

  public static function showFile ($id) {
    $file = getDatabase()->one(" select * from lobbyfile where id = :id",array('id'=>$id));
    $issue = substr($file['issue'],0,30);
    $issue .= '...';
    top($issue);

    $longest = self::getLongestRunningLobbyingFile();
    $isLongest = 0;
    if ($id == $longest['id']) {
      $isLongest = 1;
    }
    ?>

    <div class="row-fluid">
    <div class="span4">
    <h4>Issue</h4>
    <?php 
      print $file['issue']; 
      if ($id == $longest['id']) {
        print "<h4>Fun Fact</h4>";
        print "At <b>{$longest['days']}</b> days this is the longest running lobbying file on record";
      }
    ?>
    </div>
    <div class="span4">
    <h4>Lobbyist</h4>
    <a href="<?php print "../lobbyists/".$file['lobbyist']; ?>"><?php print $file['lobbyist']; ?></a>
    </div>
    <div class="span3">
    <h4>Client</h4>
    <a href="<?php print "../clients/".$file['client']; ?>"><?php print $file['client']; ?></a>
    </div>
    <div class="span1">
    <?php renderShareLinks("Lobbying about ".$file['issue'],"/lobbying/files/".$file['id']); ?>
    </div>
    </div>

    <p/>

    <?php
    $rows = getDatabase()->all(" select * from lobbying where lobbyfileid = :id order by date(created) desc, date(lobbydate) desc ",array('id'=>$id));
    #pr($rows);
    ?>
    <div class="row-fluid">
    <div class="span6">
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
      <tr>
      <th>Date</th>
      <th>Activity</th>
      <th>Lobbied</th>
      </tr>
    <?php
    $lastdate= '';
    $lastactivity = '';
    foreach ($rows as $r) {
      ?>
      <tr>
      <?php
      if ($lastdate == $r['lobbydate'] && $lastactivity == $r['activity']) {
        ?>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <?php
      } else {
        ?>
        <td><nobr><?php print substr($r['lobbydate'],0,10); ?></nobr></td>
        <td><nobr><?php print $r['activity']; ?></nobr></td>
        <?php
      }
      ?>
      <td><nobr><?php print $r['lobbied']; ?></nobr></td>
      <td><nobr><?php print substr($r['created'],0,10); ?></nobr></td>
      </tr>
      <?php
      $lastdate = $r['lobbydate'];
      $lastactivity = $r['activity'];
    }
    ?>
    </table>
    </div><!-- /span -->


    <div class="span6">
    <h1>Discuss this lobbying!</h1>
    <?php disqus(); ?>
    </div>

    </div><!-- /row -->

    <?php
    bottom();
  }

  public static function showLobbied ($lobbied) {
    top("Lobbied: $lobbied");
    $rows = getDatabase()->all("
      select *, l.created ccreated
      from lobbyfile f
        join lobbying l on l.lobbyfileid = f.id
      where 
				l.lobbied like '%".mysql_escape_string($lobbied)."%'
				or l.lobbiednorm = '".mysql_escape_string($lobbied)."'
      order by
        l.created desc
      ");
    ?>
    <div class="row-fluid">
    <div class="span11">
    <h1><?php print $lobbied; ?></h1>
    </div>
    <div class="span1">
    <?php renderShareLinks("The Lobbied: $lobbied".$file['issue'],"/lobbying/thelobbied/".$lobbied); ?>
    </div>
    </div>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
      <tr>
      <th>Lobbied</th>
      <th>Lobbyist</th>
      <th>Client</th>
      <th>Issue</th>
      <th>Date</th>
      <th>Activity</th>
      </tr>
    <?php
    $lastclient = '';
    $lastissue = '';
    foreach ($rows as $r) {
      ?>
      <tr>
      <?php
      if ($r['client'] == $lastclient && $r['issue'] == $lastissue) {
        ?>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <?php
      } else {
        ?>
        <td><nobr><a href="<?php print OttWatchConfig::WWW."/lobbying/thelobbied/{$r['lobbied']}"; ?>"><?php print $r['lobbied']; ?></a></nobr></td>
        <td><nobr><a href="<?php print OttWatchConfig::WWW."/lobbying/lobbyists/{$r['lobbyist']}"; ?>"><?php print $r['lobbyist']; ?></a></nobr></td>
        <td><nobr><a href="<?php print OttWatchConfig::WWW."/lobbying/clients/{$r['client']}"; ?>"><?php print $r['client']; ?></a></nobr></td>
        <td><?php print self::getUrlForIssue($r['lobbyfileid'],$r['issue']); ?></td>
        <?php
      }
      $lastclient = $r['client'];
      $lastissue = $r['issue'];
      ?>
      <td><nobr><?php print substr($r['lobbydate'],0,10); ?></nobr></td>
      <td><nobr><?php print $r['activity']; ?></nobr></td>
      </tr>
      <?php
    }
    ?>
    </table>
    <?php
    bottom();
  }

  public static function showLobbyist ($lobbyist) {
    top("Lobbyist: $lobbyist");
    ?>

    <?php
    $rows = getDatabase()->all(" select client,issue from lobbyfile f where lobbyist = :lobbyist order by client ",array( 'lobbyist' => $lobbyist));
    $files = count($rows);
    ?>
    <div class="row-fluid">
    <div class="span4">
    <h1><?php print $lobbyist; ?></h1>
    </div>
    <div class="span7">
    <h4>Works on <?php print count($rows); ?> lobbying files for these clients:</h4>
    <?php
    $skip = array();
    foreach ($rows as $r) {
      if ($skip[$r['client']]) { continue; }
      print "<nobr><a href=\"".OttWatchConfig::WWW."/lobbying/clients/{$r['client']}\">{$r['client']}</a></nobr>";
      print "&nbsp;";
      print "&nbsp;";
      print "&nbsp; ";
      $skip[$r['client']] = 1;
    }
    ?>
    <br/><br/>
    </div>
    <div class="span1">
    <?php renderShareLinks("Lobbyist: $lobbyist","/lobbying/lobbyists/".$lobbyist); ?>
    </div>
    </div>

    <?php
    $rows = getDatabase()->all("
      select 
        * 
      from lobbyfile f
        join lobbying l on l.lobbyfileid = f.id
      where 
        lobbyist = :lobbyist
      order by
        l.lobbydate desc
      ",array(
        'lobbyist' => $lobbyist
      ));
    ?>
    <div class="row-fluid">
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
      <tr>
      <th>Client</th>
      <th>Issue</th>
      <th>Date</th>
      <th>Activity</th>
      <th>Lobbied</th>
      </tr>
    <?php
    $lastclient = '';
    $lastissue = '';
    foreach ($rows as $r) {
      ?>
      <tr>
      <?php
      if ($r['client'] == $lastclient && $r['issue'] == $lastissue) {
        ?>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <?php
      } else {
        ?>
        <td><nobr><a href="<?php print OttWatchConfig::WWW."/lobbying/clients/{$r['client']}"; ?>"><?php print $r['client']; ?></a></nobr></td>
        <td><?php print self::getUrlForIssue($r['lobbyfileid'],$r['issue']); ?></td>
        <?php
      }
      $lastclient = $r['client'];
      $lastissue = $r['issue'];
      ?>
      <td><nobr><?php print substr($r['lobbydate'],0,10); ?></nobr></td>
      <td><nobr><?php print $r['activity']; ?></nobr></td>
      <td><nobr><a href="<?php print OttWatchConfig::WWW."/lobbying/thelobbied/".urlencode($r['lobbied']); ?>"><?php print $r['lobbied']; ?></a></nobr></td>
      </tr>
      <?php
    }
    ?>
    </table>
    </div>
    <?php
    bottom();
  }

  public static function showClient ($client) {
    top("Lobbying Client: $client");

    $rows = getDatabase()->all("
      select *
      from lobbyfile f
        join lobbying l on l.lobbyfileid = f.id
      where client = :client
      order by
        l.created desc
      ",array(
      'client' => $client
      ));

    ?>
    <div class="row-fluid">
    <div class="span4">
    <h1><?php print $client; ?></h1>
    <?php
    # count lobbyists
    $lobbyists = array();
    $issues = array();
    foreach ($rows as $r) {
      $lobbyists[$r['lobbyist']] = 1;
      $issues[$r['issue']] = 1;
    }
    print "Total actitives: ".count($rows)."<br/>";
    print "Number of lobbyists: ".count($lobbyists)."<br/>";
    print "Number of issues: ".count($issues)."<br/>";
    ?>
    <br/>
    <b>Full size charts:</b>
    <ul>
    <li><a href="<?php print OttWatchConfig::WWW."/chart/lobbying/monthly?client={$client}"; ?>">Monthly</a></li>
    <li><a href="<?php print OttWatchConfig::WWW."/chart/lobbying/daily?client={$client}"; ?>">Daily</a></li>
    </ul>
    </div>
    <div class="span1">
    <?php renderShareLinks("Lobbying Clients: $client","/lobbying/clinets/".$client); ?>
    </div>
    <div class="span7">
    <?php ChartController::lobbyingDailyInner('daily',265/2,$client); ?>
    </div>
    </div>

    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
      <tr>
      <th>Lobbyist</th>
      <th>Issue</th>
      <th>Date</th>
      <th>Activity</th>
      <th>Lobbied</th>
      </tr>
    <?php
    $lastlobbyist = '';
    $lastissue = '';
    foreach ($rows as $r) {
      ?>
      <tr>
      <?php
      if ($r['lobbyist'] == $lastlobbyist && $r['issue'] == $lastissue) {
        ?>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <?php
      } else {
        ?>
        <td><nobr><a href="<?php print OttWatchConfig::WWW."/lobbying/lobbyists/{$r['lobbyist']}"; ?>"><?php print $r['lobbyist']; ?></a></nobr></td>
        <td><?php print self::getUrlForIssue($r['lobbyfileid'],$r['issue']); ?></td>
        <?php
      }
      $lastlobbyist = $r['lobbyist'];
      $lastissue = $r['issue'];
      ?>
      <td><nobr><?php print substr($r['lobbydate'],0,10); ?></nobr></td>
      <td><nobr><?php print $r['activity']; ?></nobr></td>
      <td><nobr><a href="<?php print OttWatchConfig::WWW."/lobbying/thelobbied/".urlencode($r['lobbied']); ?>"><?php print $r['lobbied']; ?></a></nobr></td>
      </tr>
      <?php
    }
    ?>
    </table>


    <?php
    bottom();
  }

  public static function search ($query) {
    top();

    $clause = mysql_escape_string($query);
    $since = $_GET['since'];
    if (preg_match('/^\d\d\d\d-\d\d-\d\d$/',$since)) {
      $since = " and l.created >= '$since' ";
    } else {
      $since = '';
    }

    $rows = getDatabase()->all("
      select 
        f.id,
        f.lobbyist,
        f.client,
        f.issue,
        date(min(l.lobbydate)) fdate,
        date(max(l.lobbydate)) tdate,
        date(max(l.created)) created,
        count(1) as count
      from lobbyfile f
        join lobbying l on l.lobbyfileid = f.id
      where
        (client like '%$clause%'
	        or lobbyist like '%$clause%'
	        or issue like '%$clause%'
	        or lobbied like '%$clause%') 
        $since
      group by
        f.id,
        f.lobbyist,
        f.client,
        f.issue
      order by
        date(max(l.created)) desc
      ");
    if (count($rows) == 0) {
      print "<h1>'$query': No matches</h1>\n";
      bottom();
      return;
    }
    ?>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
      <tr>
      <th>Lobbyist</th>
      <th>Issue</th>
      <th>Client</th>
      <th>Activities</th>
      <th>From</th>
      <th>To</th>
      </tr>
    <?php
    foreach ($rows as $r) {
      ?>
      <tr>
      <td><nobr><a href="<?php print OttWatchConfig::WWW."/lobbying/lobbyists/{$r['lobbyist']}"; ?>"><?php print $r['lobbyist']; ?></a></nobr></td>
      <td><?php print self::getUrlForIssue($r['id'],$r['issue']); ?></td>
      <td><nobr><a href="<?php print OttWatchConfig::WWW."/lobbying/clients/{$r['client']}"; ?>"><?php print $r['client']; ?></a></nobr></td>
      <td><nobr><a href="../files/<?php print $r['id']; ?>" class="btn"><?php print $r['count']; ?> activities</a></nobr></td>
      <td><nobr><?php print $r['fdate']; ?></nobr></td>
      <td><nobr><?php print $r['tdate']; ?></nobr></td>
      </tr>
      <?php
    }
    ?>
    </table>
    <?php
    bottom();
  }

  #################################################################################################
  # Scaping
  #################################################################################################

  public static function scrapeForNewLobbyActivities($daterange = 30) {

		$now = time();
		$then = $now-(60*60*24*$daterange);
		$from = strftime("%d-%b-%Y",$then);
		$to = strftime("%d-%b-%Y",$now);

    # Use the first lobbyist search results page to find all lobbyists
    # how have activities in the given range.

    # print "Loading results for $from to $to ($daterange days)\n";

		$html = LobbyistController::searchByDate($from,$to);
		#file_put_contents("lobbysearch.html",$html);
		#$html = file_get_contents("lobbysearch.html");

		# process page 1
    self::parseSearchResults($html);
		
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
				  $html = sendPost(self::URL,$fields);
					self::parseSearchResults($html);
				}
			}
		}
  }

  /*
  HTML is the output of a client/lobbyistfile. 
  Many rows of all lobbying activity over a wide date range.
  */
  public static function scrapeLobbyistClientFile($html) {

    $html = preg_replace('/\n/',' ',$html);
    $html = preg_replace('/\r/',' ',$html);
    $html = preg_replace('/  /',' ',$html);
    $html = preg_replace('/  /',' ',$html);
    $html = preg_replace('/  /',' ',$html);
    $html = preg_replace('/  /',' ',$html);
    $html = preg_replace('/  /',' ',$html);

    $matches = array();
    $issue = 'ERR: issue parsing failed';
    $client = 'ERR: client parsing failed';
    $lobbyist = 'ERR: name parsing failed';
    if (preg_match('/<span id="MainContent_lblIssue">([^<]+)</',$html,$matches)) {
      $issue = $matches[1];
      $issue = trim($issue);
      $issue = substr($issue,0,200);
    }
    if (preg_match('/<span id="MainContent_lblClientOrg">([^<]+)</',$html,$matches)) {
      $client = $matches[1];
      $client = trim($client);
    }
    if (preg_match('/<span id="MainContent_lblName"[^>]+>([^<]+)</',$html,$matches)) {
      $lobbyist = $matches[1];
      $lobbyist = trim($lobbyist);
    }

    #print "Scraping $lobbyist, client: $client, issue: $issue\n";

    if ($issue == 'ERR: issue parsing failed') {
      file_put_contents("err.html",$html);
      print $html;
      return;
    }

    # is this in the database already?
    $file = getDatabase()->one(" select * from lobbyfile where lobbyist = :lobbyist and client = :client and issue = :issue ",array(
        'lobbyist' => $lobbyist,
        'client' => $client,
        'issue' => $issue
    ));
    $fileid = $file['id'];
    if (!$file['id']) {
      $fileid = getDatabase()->execute(" 
        insert into lobbyfile (lobbyist,client,issue) values
        (:lobbyist,:client,:issue) ",array(
        'lobbyist' => $lobbyist,
        'client' => $client,
        'issue' => $issue
      ));
    }

    # scope HTML to just the data-table, then continue parsing as XML, to tease out
    # all date-based lobbying activities
    $tablehtml = preg_replace('/.*<table class="dataTable"/','<table ',$html);
    $tablehtml = preg_replace('/<\/table>.*/','</table>',$tablehtml);
    $tablehtml = preg_replace('/<br\/>/','|',$tablehtml); # lobbied names are br delimited, convert to PIPE 
    $xml = simplexml_load_string($tablehtml);
    if (!is_object($xml)) {
      print "broken xml\n$xml\n";
      return;
    }
    $rows = $xml->xpath("//tr");
    foreach ($rows as $r) {
      $tds = $r->xpath("td");
      if (count($tds) == 0) {
        continue;
      }
      $date = $tds[0];
      $date = strftime("%Y-%m-%d",strtotime($date));
      $activity = $tds[1];
      $lobbied = $tds[2];
      foreach (explode("|",$lobbied) as $who) {
        self::insertLobbying($fileid,$date,$activity,$who);
      }
    }

  }

  public static function insertLobbying($fileid,$date,$activity,$lobbied) {
      try {
	      $newid = getDatabase()->execute(" 
	        insert into lobbying (lobbyfileid,lobbydate,activity,lobbied,created) values 
	        (:lobbyfileid,:lobbydate,:activity,:lobbied,CURRENT_TIMESTAMP) ",array(
	        'lobbyfileid' => $fileid,
	        'lobbydate' => $date,
	        'activity' => $activity,
	        'lobbied' => $lobbied
	      ));
        # a unique constraint will cause exceptions on existing rows. If we got here
        # then the row is actually new, so perhaps it should be tweeted.
        # for now, do nothing. Other processes can pick up on CREATED value to see
        # if tweet action should happen.
        # print "  $date activity: $activity lobbied: $lobbied\n";
      } catch (Exception $e) {
        if (!preg_match('/Duplicate/',$e)) {
          # only duplicate key is expected since we are not selecting to detect if we
          # know about this one already
          print "$e\n";
        }
      }
  }
	
	public static function parseSearchResults($html) {

		$events = array();
		$viewstate = getViewState($html);
		$eventvalidation = getEventValidation($html);

		$lines = explode("\n",$html);
    $scanFrom = 0;
		for ($x = 0; $x < count($lines); $x++) {
      $l = $lines[$x];

      # look for all the "View" buttons to load entire record for the matced lobbyist.
      $matches = array();
      if (preg_match('/<a.*MainContent_gvSearchResults_LnkLobbyistName.*<u>(.*)<\/u>/',$l,$matches)) {
        # print ">>> $l\n";
        # <a id="MainContent_gvSearchResults_LnkLobbyistName_0" href="javascript:__doPostBack(&#39;ctl00$MainContent$gvSearchResults$ctl02$LnkLobbyistName&#39;,&#39;&#39;)"><u>Bryan Huehn</u></a>
        $lobbyist = $matches[1];
      }
      if (preg_match('/<span.*gvsrlblFromDate.*>(\d\d-\S\S\S-20\d\d)</',$l,$matches)) {
        # print ">>> $l\n";
        #<span id="MainContent_gvSearchResults_gvsrlblFromDate_0">04-Mar-2013</span><br />
        $from = $matches[1];
        $from = strftime("%Y-%m-%d",strtotime($from));
      }
      if (preg_match('/<span.*gvsrlblToDate.*>(\d\d-\S\S\S-20\d\d)</',$l,$matches)) {
        # print ">>> $l\n";
        # <span id="MainContent_gvSearchResults_gvsrlblToDate_0">04-Mar-2013</span>
        $to = $matches[1];
        $to = strftime("%Y-%m-%d",strtotime($to));
      }
			# sometimes 'issue' has ^M linefeeds
      if (preg_match('/<span.*gvsrlblIssue.*>/',$l)) {
				# great! but pull in mor elines if it doesnt have closing span
				$m = 1;
	      while (!preg_match('/<span.*gvsrlblIssue.*>([^<]+)</',$l,$matches)) {
					$l .= $lines[$x+$m];
					$l = preg_replace('/\r/',' ',$l);
					$l = preg_replace('/\n/',' ',$l);
					$l = preg_replace('/  /',' ',$l);
					$m++;
				}
				# now next IF will match because we have pulled in the close span tag
			}
      if (preg_match('/<span.*gvsrlblIssue.*>([^<]+)</',$l,$matches)) {
        # print ">>> $l\n";
        # <span id="MainContent_gvSearchResults_gvsrlblIssue_0">Presto pass mass distribution</span>
        $issue = $matches[1];
        # $issue = substr($issue,0,30);
      }
      if (preg_match('/<input.*name="(ctl00.*gvSearchResults.*btnView)"/',$lines[$x],$matches)) {
        $ctl = $matches[1];
        # print ">>> $l\n";
        # print "\n";
        # print "$from to $to :: $lobbyist :: $issue\n";

        # now check if we have a lobbyfile match; if so, assume no changes because the min/max date
        # ranges are the same, and don't bother scraping the detailed page.
        # TODO: possible to miss details if lobbyist registers "old" activity within existing min/max
        # activitydate. But worth the risk, as it speeds up scraping because no need to load detail
        # page for all files.
        $all = getDatabase()->all("
          select 
            f.lobbyist,
            f.issue,
            min(date(l.lobbydate)) mindate,
            max(date(l.lobbydate)) maxdate
          from lobbyfile f
            join lobbying l on l.lobbyfileid = f.id
          where 
            f.lobbyist = :lobbyist 
            and f.issue = :issue
          group by f.lobbyist, f.issue
          having
            min(date(l.lobbydate)) = :from
            and max(date(l.lobbydate)) = :to
          ",array(
          'lobbyist' => $lobbyist,
          'issue' => $issue,
          'from' => $from,
          'to' => $to,
        ));
        if (count($all) > 0) {
          continue;
        }

				$fields = array(
				  '__VIEWSTATE' => $viewstate,
				  '__EVENTVALIDATION' => $eventvalidation,
				  $ctl => '',
				);

        # Load the entire lobbyist/client file for this row, and hand-off parsing.
			  $lobbyistHTML = sendPost(self::URL,$fields);
				if (preg_match('/OracleConnection/',$lobbyistHTML)) {
					# errors happen, try again just for kicks
				  $lobbyistHTML = sendPost(self::URL,$fields);
					if (preg_match('/OracleConnection/',$lobbyistHTML)) {
						print "Two back to back OracleConnection errors; giving up\n";
						continue;
					}
				}
        #file_put_contents("lobbyistprofile.html",$lobbyistHTML);
        #$lobbyistHTML = file_get_contents("lobbyistprofile.html");
        self::scrapeLobbyistClientFile($lobbyistHTML);
      }
    }
  }

	public static function searchByDate($from,$to) {
	
		$html = file_get_contents(self::URL);
	  $viewstate = getViewState($html);
	  $eventvalidation = getEventValidation($html);
		
		$fields = array(
		  '__VIEWSTATE' => $viewstate,
		  '__EVENTVALIDATION' => $eventvalidation,
		  'ctl00$MainContent$dpFromDate_txtbox' => $from,
		  'ctl00$MainContent$dpToDate_txtbox' => $to,
		  'ctl00$MainContent$btnSearch' => 'Search'
		);
	
	  $response = sendPost(self::URL,$fields);
	  return $response;
	}

  public static function tweetLongestFileBadge() {

    # check if we have a flip in "longest lobbying file"
    $last = getvar('lobbytweet.longest');
    $longest = self::getLongestRunningLobbyingFile();
    setvar('lobbytweet.longest',$longest['id']);

    if ($last != $longest['id']) {
      # we have a flip; just PRINT for
      $oldfile = getDatabase()->one(" select * from lobbyfile where id = :id",array('id'=>$last));
      $file = getDatabase()->one(" select * from lobbyfile where id = :id",array('id'=>$longest['id']));

      $txt = "{$file['lobbyist']} has ousted {$oldfile['lobbyist']} for longest running lobby file: {$longest['days']} days";
      $url = OttWatchConfig::WWW."/lobbying/files/".$file['id'];
      print "\nBADGES!\n";
      print "TXT: $txt\n";
      print "URL: $url\n";
    }

  }

  public static function tweetNewActivities() {

    #setvar('lobbytweet.last','1362459600');

    $last = getvar('lobbytweet.last');
    if ($last == '') {
      # defaulting now NOW
      $last = time();
    }
    # update the touch time to NOW, even if we fail to tweet.
    setvar('lobbytweet.last',time());

    # find all the lobbying that has happened since LAST
    $rows = getDatabase()->all("
      select
        *
      from lobbying l
        join lobbyfile f on f.id = l.lobbyfileid
      where
        l.created >= from_unixtime(:last)
      order by 
        l.created desc, l.id
      ",array(
      'last'=>$last
    ));
    $tweets = array();
    $posts = array();
    foreach ($rows as $r) {
      $tweet = "Lobbying: {$r['lobbyist']} ({$r['client']}) {$r['issue']}";
      # new style
      $path = "/lobbying/files/".$r['lobbyfileid'];
      $posts[$path] = $tweet;
      # old style
      $url = OttWatchConfig::WWW."/lobbying/files/".$r['lobbyfileid'];
      $tweets[$url] = $tweet;
    }
    foreach ($posts as $path  => $text) {
      syndicate($text,$path);
    }
    # keying by URL guarantees we don't double-tweet because of multiple new activities 
    # on the same lobbyfile
    #foreach ($tweets as $url => $text) {
    #  $tweet = tweet_txt_and_url($text,$url);
    #  # allow duplicates because subsequent tweets about the same file
    #  # will be the same, but spaced in time according to the lobbyist
    #  # activity dates
    #  tweet($tweet);
    #  sleep(5);
    #}
    
  }

	public function fixLobbyingNames() {

#		$rows = getDatabase()->all(" select l.id,l.lobbied,l.lobbiednorm,e.first,e.last from lobbying l join electedofficials e on e.id = l.electedofficialid ");
#		foreach ($rows as $r) {
#      getDatabase()->execute(" update lobbying set lobbiednorm = :lobbiednorm where id = :id ",array('id'=>$r['id'],'lobbiednorm' => "{$r['last']}, {$r['first']}"));
#		}

		$rows = getDatabase()->all(" select id,lobbied from lobbying where electedofficialid is null ");
		$count = 0;
		foreach ($rows as $r) {
			$keep = 0;
			if (preg_match('/mayor/i',$r['lobbied'])) { $keep = 1; }
			if (preg_match('/councillor/i',$r['lobbied'])) { $keep = 1; }
			if (preg_match('/ward/i',$r['lobbied'])) { $keep = 1; }
			if (preg_match('/assistant/i',$r['lobbied'])) { $keep = 0; }
			if (preg_match('/ Planner,/',$r['lobbied'])) { $keep = 0; }
			if (preg_match('/ Mgr,/i',$r['lobbied'])) { $keep = 0; }
			if (!$keep) { continue; }

			$name = trim(array_shift(explode(":",$r['lobbied'])));

			# dirty
			if ($name == 'Hume, Peter E') { $name = 'Hume, Peter'; }
			if ($name == 'Clark, Peter D') { $name = 'Clark, Peter'; }
			if ($name == 'Tierney, Timothy') { $name = 'Tierney, Tim'; }

			# is this an elected official?
			$e = getDatabase()->one("
				select * 
				from electedofficials 
				where 
					concat(first,' ',last) = :name
					or concat(last,', ',first) = :name
				",array('name'=>$name));

			if (!$e['id']) {
        # not an elected official
        continue;
			}

      getDatabase()->execute(" 
				update lobbying set 
					electedofficialid = :eid, 
					lobbiednorm = :lobbiednorm 
				where id = :id ",array(
					'id'=>$r['id'],
					'eid'=>$e['id'],
					'lobbiednorm'=>"{$e['last']}, {$e['first']}"
			));

		}


	}

}

?>
