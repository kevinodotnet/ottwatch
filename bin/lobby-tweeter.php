<?php

require_once('twitteroauth.php');

$VAR="/mnt/shared/ottwatch/var";
$VAR="/tmp/ottwatch";

#######################################################################################

$url="https://apps107.ottawa.ca/LobbyistRegistry/search/searchlobbyist.aspx?lang=en";

# extract ASP.NET control variables
$html = file_get_contents($url);
$lines = explode("\n",$html);
foreach ($lines as $line) {
  if (preg_match("/__VIEWSTATE/",$line)) {
    $viewstate = preg_replace('/.*value="/',"",$line);
    $viewstate = preg_replace('/".*/',"",$viewstate);
  }
  if (preg_match("/__EVENTVALIDATION/",$line)) {
    $eventvalidation = preg_replace('/.*value="/',"",$line);
    $eventvalidation = preg_replace('/".*/',"",$eventvalidation);
  }
}

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

