<?php

require_once('twitteroauth.php');

$VAR="/mnt/shared/ottwatch/var";
$VAR="/tmp/ottwatch";

#######################################################################################

#$html = searchByDate();
#file_put_contents("test_result.html",$html);
$html = file_get_contents("test_result.html");
$viewstate = getViewState($html);
$eventvalidation = getEventValidation($html);

$lines = explode("\n",$html);
for ($x = 0; $x < count($lines); $x++) {
  if (preg_match("/MainContent_gvSearchResults_LnkLobbyistName/",$lines[$x])) {
    print "###################################################\n";
    # start of lobby result
    $xml = "<tr><td><b>";
    for ($y = 0; $y < 13; $y++) {
      $xml .= $lines[$x+$y]."\n";
    }
    $xml .= "</td></tr>";
    $xml = preg_replace("/&/",'&amp;',$xml);
    $xml = simplexml_load_string($xml);
    print print_r($xml);
    print "\n";
    exit(0);
  }
}

function getViewState($html) {
	$lines = explode("\n",$html);
	foreach ($lines as $line) {
	  if (preg_match("/__VIEWSTATE/",$line)) {
	    $viewstate = preg_replace('/.*value="/',"",$line);
	    $viewstate = preg_replace('/".*/',"",$viewstate);
	    return $viewstate;
	  }
	}
}

function getEventValidation($html) {
	$lines = explode("\n",$html);
	foreach ($lines as $line) {
	  if (preg_match("/__VIEWSTATE/",$line)) {
	    $viewstate = preg_replace('/.*value="/',"",$line);
	    $viewstate = preg_replace('/".*/',"",$viewstate);
	    return $viewstate;
	  }
	}
}

function searchByDate() {

	$url="https://apps107.ottawa.ca/LobbyistRegistry/search/searchlobbyist.aspx?lang=en";
	
	# extract ASP.NET control variables
	$html = file_get_contents($url);
  $viewstate = getViewState($html);
  $eventvalidation = getEventValidation($html);
#	$lines = explode("\n",$html);
#	foreach ($lines as $line) {
#	  if (preg_match("/__VIEWSTATE/",$line)) {
#	    $viewstate = preg_replace('/.*value="/',"",$line);
#	    $viewstate = preg_replace('/".*/',"",$viewstate);
#	  }
#	  if (preg_match("/__EVENTVALIDATION/",$line)) {
#	    $eventvalidation = preg_replace('/.*value="/',"",$line);
#	    $eventvalidation = preg_replace('/".*/',"",$eventvalidation);
#	  }
#	}
	
	# write a test HTML file that verifies the input parameters work.
	$html = "
	<form method='post' action='$url'>
	<input type='hidden' name='__VIEWSTATE' value='$viewstate'/>
	<input type='hidden' name='__EVENTVALIDATION' value='$eventvalidation'/>
	<input type='hidden' name='ctl00\$MainContent\$dpFromDate_txtbox' value='18-Feb-2013'/>
	<input type='hidden' name='ctl00\$MainContent\$dpToDate_txtbox' value='22-Feb-2013'/>
	<input type='hidden' name='ctl00\$MainContent\$btnSearch' value='Search'/>
	<input type='submit' value='doit2'/>
	</form>
	";
	file_put_contents("test.html",$html);
	
	# invoke CURLkk
	
	$fields = array(
	  '__VIEWSTATE' => $viewstate,
	  '__EVENTVALIDATION' => $eventvalidation,
	  'ctl00$MainContent$dpFromDate_txtbox' => '18-Feb-2013',
	  'ctl00$MainContent$dpToDate_txtbox' => '22-Feb-2013',
	  'ctl00$MainContent$btnSearch' => 'Search'
	);
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	$fields_string = http_build_query($fields);
	
	$ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	#curl_setopt($ch,CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17 OttWatch');
	$response = curl_exec($ch);
	curl_close($ch);

  return $response;
	
}

