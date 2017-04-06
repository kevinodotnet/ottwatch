<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');
require_once('twitteroauth.php');

if (count($argv) > 1) {

  if ($argv[1] == 'getRss') {
		getRss();
		return;
	}

}

function getRss() {
	getRssOttawaCitizen();
}

function getRssOttawaCitizen() {

	# OttawaCitizen
	$url = 'http://ottawacitizen.com/category/news/local-news/feed';
	$xml = simplexml_load_string(c_file_get_contents($url), 'SimpleXMLElement', LIBXML_NOCDATA);

	#pr($xml);

	foreach ($xml->channel->item as $i) {
		$link = $i->{'link'};
		$linkHash = md5($link);
		file_put_contents(OttWatchConfig::FILE_DIR.'/localnews/ottawacitizen/'.$linkHash,$i->asXML());
	}

}

