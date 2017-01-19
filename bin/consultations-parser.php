<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');
require_once('twitteroauth.php');

$action = $argv[1];
if ($action == 'crawlEngagements') {
	ConsultationController::crawlEngagements();
	return;
}
if ($action == 'crawlProjects') {
	ConsultationController::crawlProjects();
	return;
}

if ($action == 'crawlProject') {
	ConsultationController::crawlProject($argv[2]);
	return;
}

ConsultationController::crawlConsultations();
ConsultationController::tweetUpdatedConsultations();

