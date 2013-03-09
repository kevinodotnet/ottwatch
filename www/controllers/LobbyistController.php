<?php

include '../../lib/include.php';

LobbyistController::scrapeForNewLobbyActivities(60);

class LobbyistController {

	# base URL of the lobbyist registry
	const URL = "https://apps107.ottawa.ca/LobbyistRegistry/search/searchlobbyist.aspx?lang=en";

  public static function scrapeForNewLobbyActivities($daterange = 30) {

		$now = time();
		$then = $now-(60*60*24*$daterange);
		$from = strftime("%d-%b-%Y",$then);
		$to = strftime("%d-%b-%Y",$now);

    # Use the first lobbyist search results page to find all lobbyists
    # how have activities in the given range.

    print "Loading results for $from to $to ($daterange days)\n";

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
		
# 		foreach ($events as $event) {
# 			$who = $event['who'];
# 			$what = $event['what'];
# 			$job = $event['job'];
# 			$from = $event['from'];
# 			$to = $event['to'];
# 		
# 			$hash = md5("$from :: $to :: $who :: $job :: $what");
# 		  $hashfile = "$OTTVAR/lobby/$hash";
# 			if (file_exists($hashfile)) {
# 				continue;
# 			}
# 		
# 		  $link = "$OTT_WWW/lobbyist/".urlencode($who);
# 			$bitly = bitly_v3_shorten($link);
# 			$bitly = $bitly['url'];
# 			$tweet = tweet_txt_and_url("Lobbying: $who, $what","$bitly");
# 		
# 			file_put_contents($hashfile,"$from :: $to :: $who :: $job :: $what :: $bitly\n\n$tweet\n");
# 			# tweet($tweet);
# 		}


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

    print "Scraping $lobbyist, client: $client, issue: $issue\n";

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
        print "  $date activity: $activity lobbied: $lobbied\n";
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
        print "$from to $to :: $lobbyist :: $issue\n";

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

}
