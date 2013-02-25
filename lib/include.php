<?php

$OTTVAR="/mnt/shared/ottwatch/var";

function tweet($tweet,$allowDup=0) {

	global $OTTVAR;

	# send no tweet twice
  $hash = md5($tweet);
  $hashfile = "$OTTVAR/tweets/$hash";
  if (file_exists($hashfile)) {
		if (!$allowDup) {
			return false;
		}
	}

	# check tweet cache and dont send if found
	file_put_contents($hashfile,$tweet);

	# todo: move to non-git configuration file
  $consumerKey = 'aPqhRRoL1X4lDRGbRpdjA';
  $consumerSecret = '9Cz0ot2iUfzAaoRNesHmxKl4se7zYMDpka0x2F9imG0';
  $accessToken = '1206679020-ZDNk6AZT5cYhGWiyFXB4K5BsQK3ItQf5m4Cpt5t';
  $accessTokenSecret = 'EmT6yieQC9LxAwYIDHKFnUOqf1jX31jHHwxwspX5TnI';

  $twitter = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
  if(strlen($tweet) <= 140) {
    $twitter->post('statuses/update', array('status' => $tweet));
		return true;
  } else {
		print "WARNING: tweet too long; not sent; '$tweet'";
		return false;
  }

}

?>
