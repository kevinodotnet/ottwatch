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

  if ($argv[1] == 'getCBC') {
		getCBC();
		return;
	}

	

}

function getCBC() {
	$url = 'http://www.cbc.ca/cmlink/rss-canada-ottawa';
	$xml = simplexml_load_string(file_get_contents($url), 'SimpleXMLElement', LIBXML_NOCDATA);
	foreach ($xml->channel->item as $i) {
		$link = $i->{'link'};
		$linkHash = md5($link);
		$data = file_get_contents($link);
		$i->ottwatch_contents = $data;
		file_put_contents(OttWatchConfig::FILE_DIR.'/localnews/cbcottawa/'.$linkHash,$i->asXML());
	}
}

function getRss() {
	getRssOttawaCitizen();
	getCBC();
}

function getRssOttawaCitizen() {

	# OttawaCitizen
	$url = 'http://ottawacitizen.com/category/news/local-news/feed';
	$xml = simplexml_load_string(file_get_contents($url), 'SimpleXMLElement', LIBXML_NOCDATA);

	#pr($xml);

	foreach ($xml->channel->item as $i) {
		$link = $i->{'link'};
		$linkHash = md5($link);
		$data = file_get_contents($link);
		$i->ottwatch_contents = $data;
		file_put_contents(OttWatchConfig::FILE_DIR.'/localnews/ottawacitizen/'.$linkHash,$i->asXML());
	}

}

