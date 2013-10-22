<?php

#
# Post to the OttWatch facebook page
#


$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

$url = "http://graph.facebook.com/".OttWatchConfig::FACEBOOK_PAGE_ID."/links";

$data = sendPost($url,array());

?>
