<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
#ob_start();

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

if ($argv[1] == 'WAYBACK') {
	print "archive 2014\n";
	LobbyistController::scrapeForNewLobbyActivitiesFromTo('01-Jan-2014','01-Jan-2015');
	print "archive 2013\n";
	LobbyistController::scrapeForNewLobbyActivitiesFromTo('01-Jan-2013','01-Jan-2014');
	print "archive 2012\n";
	LobbyistController::scrapeForNewLobbyActivitiesFromTo('01-Jan-2012','01-Jan-2013');
} else {
	LobbyistController::scrapeForNewLobbyActivities($argv[1]);
}

LobbyistController::fixLobbyingNames();
LobbyistController::tweetNewActivities();

