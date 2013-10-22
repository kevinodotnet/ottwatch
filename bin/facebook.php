<?php

#
# Usage:
#   php ./facebook.php post_text [link]
#
# 
#

$index = 1;
$type = @$argv[$index++];
$message = @$argv[$index++];
$link = @$argv[$index++];

if (!isset($type)) {
	print "ERROR: first arg, type, not provided. Must be 'link' or 'status'\n";
	return;
}

if ($type != 'link' && $type != 'status') {
	print "ERROR: bad type: '$type'\n";
	return;
}

if ($type == 'link' && !isset($link)) {
	
}

#
#
#

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

$row = getDatabase()->one(" select * from variable where name = 'fb_page_access_token' ");
if (! $row['value']) {
	print "ERROR: no fb_page_access_token\n";
	return;
}


if (isset($link)) {
} 

$data = array();
$data['message'] = $message;
if (isset($link)) {
	$data['link'] = $link;
}
$data['access_token'] = $row['value'];
$url = "https://graph.facebook.com/".OttWatchConfig::FACEBOOK_PAGE_ID."/links";

$json = sendPost($url,$data);
$result = json_decode($json);

print "JSON\n\n$json\n\n";

pr($result);

?>
