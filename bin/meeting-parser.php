<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
#ob_start();

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');
#require_once('twitteroauth.php');

# get RSS of all meetings
$data = `wget -qO - http://app05.ottawa.ca/sirepub/rss/rss.aspx | head -1`;
$xml = simplexml_load_string($data);
$items = $xml->xpath("//item");

# iterate through each meeting
foreach ($items as $i) {
  $link = $i->xpath("link"); $link = $link[0];
  $link = preg_replace("/.*sirepub/","http://app05.ottawa.ca/sirepub",$link);

  $meetid = $link;
  $meetid = preg_replace("/.*meetid=/","",$meetid);
  $meetid = preg_replace("/&.*/","",$meetid);

  # DEBUG
  $meetid = "2252";

  # extract all items from the agenda
  $items = array();
  $url = "http://app05.ottawa.ca/sirepub/agview.aspx?agviewmeetid=$meetid&agviewdoctype=AGENDA";
  $html = `wget -qO - '$url'`;
  $lines = explode("\n",$html);
  foreach ($lines as $line) {
    if (preg_match("/itemid=/",$line)) {
      $itemid = $line;
      $itemid = preg_replace("/.*itemid=/","",$itemid);
      $itemid = preg_replace('/".*/',"",$itemid);
      $items[] = $itemid;
    }
  }

  # extract all files from the agenda item
  foreach ($items as $itemid) {
    $url = "http://app05.ottawa.ca/sirepub/agdocs.aspx?doctype=agenda&itemid=$itemid";
    $html = `wget -qO - '$url'`;
	  $lines = explode("\n",$html);
    $files = array();
	  foreach ($lines as $line) {
	    if (preg_match("/fileid=/",$line)) {
	      $fileid = $line;
	      $fileid = preg_replace("/.*fileid=/","",$fileid);
	      $fileid = preg_replace('/".*/',"",$fileid);
	      $files[] = $fileid;
        getFile($meetid,$itemid,$fileid);
	    }
	  }
  }
}

#$output = ob_get_contents();
#ob_end_clean();
#$now = strftime("%Y%m%d_%H%M%S",time());
#file_put_contents("$OTTVAR/ranat_meetings_$now",$output);

function getFile ($meetid,$itemid,$fileid) {
  $url = "http://app05.ottawa.ca/sirepub/view.aspx?cabinet=published_meetings&fileid=$fileid";
  print "getting $url\n";
  $html = `wget -qO - '$url'`;
  # GET:  <script>document.location = 'cache/2/urdcrgg4d4kra23egdhjlg20/468760225201302453793.PDF';</script>
  # MAKE: http://app05.ottawa.ca/sirepub/cache/2/ekaza2ovgebjwdj3y2fegh0x/4688002252013024625547.PDF
  $cache = $html;
  $cache = preg_replace("/.*cache/","http://app05.ottawa.ca/sirepub/cache",$cache);
  $cache = preg_replace("/'.*/","",$cache);
  $pdf = `wget -qO - '$cache'`;
  `mkdir -p $OTTVAR/meetings/$meetid/$itemid`;
  file_put_contents("$OTTVAR/meetings/$meetid/$itemid/$fileid.pdf",$pdf);
  `pdftotext $OTTVAR/meetings/$meetid/$itemid/$fileid.pdf`;
  exit;
}

?>
