<?php

#
# Just a command line tester, so I can test from time to time.
#

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');
require_once('twitteroauth.php');

  $consumerKey = OttWatchConfig::TWITTER_POST_CONSUMER_KEY;
  $consumerSecret = OttWatchConfig::TWITTER_POST_CONSUMER_SECRET;
  $accessToken = OttWatchConfig::TWITTER_POST_ACCESS_TOKEN;
  $accessTokenSecret = OttWatchConfig::TWITTER_POST_TOKEN_SECRET;

  if ($consumerKey == '') {
    # we are in debug mode, so silently discard
		print "NOPE!\n";
  }

  $twitter = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
  #$twitter->post('statuses/update', array('status' => $tweet));

	$results = $twitter->get('search/tweets', array('q'=>'ottbike'));
	pr($results->search_metadata);
	foreach ($results->statuses as $s) {
		print "{$s->user->followers_count}\t{$s->user->screen_name}\t{$s->user->name}\t{$s->text}\n";
		#pr($s);
	}

?>
