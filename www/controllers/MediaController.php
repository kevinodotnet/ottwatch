<?php

class MediaController {

  /*
  Get the press release RSS and convert it to array of objects.
  */
  public static function getMediaReleases() {
		$url = "http://ottawa.ca/rss/news_en.xml";
		$rss = file_get_contents($url);
		$xml = simplexml_load_string($rss);
		if (!is_object($xml)) {
		  # could not load RSS; just fail silently
		  return;
		}
		$items = $xml->xpath("//item");

    $releases = array();
		foreach ($items as $item) {
			# [title] => NR: Ottawa Public Health applauds ban on tanning beds for youth
			# [link] => http://ottawa.ca/cgi-bin/pressco.pl?Elist=18774&lang=en
			# [guid] => http://ottawa.ca/cgi-bin/pressco.pl?Elist=18774&lang=en
			# [pubDate] => Wed, 09 Oct 2013 15:22:00 EST
		  $title = $item->xpath("title"); $title = $title[0].'';
		  $link = $item->xpath("link"); $link = $link[0].'';
		  $guid = $item->xpath("guid"); $guid = $guid[0].'';
		  $pubDate = $item->xpath("pubDate"); $pubDate = $pubDate[0].'';
		  $guid = md5($guid); # i prefer guids to be opaque

      $release = new stdClass();
      $release->title = $title;
      $release->link = $link;
      $release->guid = $guid;
      $release->pubDate = $pubDate;
      $releases[] = $release;
		}
    return $releases;
  }

  /*
  Look for new media releases in the RSS
  */
  public static function insertNewReleases() {
    $releases = MediaController::getMediaReleases();
		foreach ($releases as $r) {
      $row = getDatabase()->all(" select * from rssitem where guid = :guid ",array('guid'=>$r->guid));
      if (count($row) == 0) {
        # does not exist, so insert
        print "Inserting...\n";
        db_insert('rssitem',array(
          'title'=>$r->title,
          'link'=>$r->link,
          'guid'=>$r->guid));
      } else {
        print "Exists...\n";
      }
		}
  }

}

?>
