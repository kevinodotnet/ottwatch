<?php

require_once('twitteroauth.php');

$VAR="/mnt/shared/ottwatch/var";

file_put_contents("$VAR/ranat_".`date +%Y%m%d_%H%M%S`,"hi");

$data = `wget -qO - http://app05.ottawa.ca/sirepub/rss/rss.aspx | head -1`;
$xml = simplexml_load_string($data);
$items = $xml->xpath("//item");

foreach ($items as $i) {

  $category = $i->xpath("category"); $category = $category[0];
  $title = $i->xpath("title"); $title = $title[0];
  $pubDate = $i->xpath("pubDate"); $pubDate = $pubDate[0];
  $guid = $i->xpath("guid"); $guid = $guid[0];
  $link = $i->xpath("link"); $link = $link[0];
  $guidmd5 = md5($guid);

  $title = preg_replace("/ AM$/"," am",$title);
  $title = preg_replace("/ PM$/"," pm",$title);
  $title = preg_replace("/ am$/","am",$title);
  $title = preg_replace("/ pm$/","pm",$title);
  $meetingDate = explode(" - ",$title);
  $meetingDate = $meetingDate[1];

  if (file_exists("$VAR/$guidmd5")) {
    # this meeting has been tweeted already
    continue;
  }

  $link = preg_replace("/.*sirepub/","http://app05.ottawa.ca/sirepub",$link);
  $tweet = "$category on $meetingDate is updated $link";

	file_put_contents("$VAR/$guidmd5",$tweet);
  send_tweet($tweet);

}

function send_tweet($tweet) {
 
  $consumerKey = 'aPqhRRoL1X4lDRGbRpdjA';
  $consumerSecret = '9Cz0ot2iUfzAaoRNesHmxKl4se7zYMDpka0x2F9imG0';
  $accessToken = '1206679020-ZDNk6AZT5cYhGWiyFXB4K5BsQK3ItQf5m4Cpt5t';
  $accessTokenSecret = 'EmT6yieQC9LxAwYIDHKFnUOqf1jX31jHHwxwspX5TnI';
  $twitter = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
  if(strlen($tweet) <= 140) {
    $twitter->post('statuses/update', array('status' => $tweet));
		print "$tweet\n";
  } else {
    print "ERROR: too long ($tweet)\n";
  }

}

#    [title] => TRC - 2012-Dec-05 12:00 pm
#    [link] => http://sire/sirepub/mtgviewer.aspx?meetid=2283&doctype=MINUTES
#    [description] => SimpleXMLElement Object
#        (
#        )
#
#    [category] => TRC
#    [pubDate] => Wed, 06 Feb 2013 19:22:17 GMT
#    [guid] => 2283 TRC 2013-Feb-06 2:22:17 PM

?>
