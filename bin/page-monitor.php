<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/..");
require_once('include.php');
require_once('vendor/autoload.php');

/* Monitory an ottawa.ca drupal page for md5 hash changes. */

$url = $argv[1];
$var = "page.monitor.".md5($url);

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');
require_once('twitteroauth.php');

$html = @file_get_contents($url);
if (strlen($html) == 0) {
	# skip; no data
	return;
}
$html = ConsultationController::getCityContent($html,'');
$md5 = md5($html);

$prevMD5 = getvar($var);

if ($md5 == $prevMD5) {
  # no change, no do anything
  return;
}

setvar($var,$md5);

sendEmail('kevino@kevino.net',"$url is updated","$url\n\nis updated");

?>
