<?php

#
# Just a command line tester, so I can test from time to time.
#

$tweet = $argv[1];

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');
require_once('twitteroauth.php');

tweet($tweet);

?>
