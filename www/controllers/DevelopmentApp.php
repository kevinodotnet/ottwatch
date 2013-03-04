<?php

include("../../lib/include.php");

DevelopmentAppController::scanDevApps();

class DevelopmentAppController {

  static public function scanDevApps() {


    # get dev-apps sorted by status update.
    # results are sorted with oldtest date first, so then jump to last page, and start scanning backwards
    # until no dates on page are "new"
    #$html = file_get_contents('http://app01.ottawa.ca/postingplans/searchResults.jsf?lang=en&newReq=true&action=sort&sortField=objectCurrentStatusDate&keyword=.');
    #file_put_contents("t.html",$html);
    $html = file_get_contents("t.html");

    # parse out all of the pages of results
    $lines = explode("\n",$html);
    $add = 0;
    $span = "";
    foreach ($lines as $l) {
      if (preg_match("/span/",$l)) {
        if ($add) {
          $span = $l;
          break;
        }
      }
      if (preg_match("/searchpaging/",$l)) {
        $add = 1;
      }
    }

    $data = explode("<a",$span);
    $pages = array();
    foreach ($data as $d) {
      if (preg_match('/page=(\d+)"/',$d,$match)) {
        $pages[$match[1]] = 1;
      }
    }
    $pages = array_keys($pages);
    $pages = array_reverse($pages);

    # obtain all search results until a page has no relatively new DevApps
    foreach ($pages as $p) {
      $url="http://app01.ottawa.ca/postingplans/searchResults.jsf?lang=en&action=sort&sortField=objectCurrentStatusDate&keyword=.&page=$p";
      $html = file_get_contents("t.html");
      exit;
    }

  }
  
}

?>
