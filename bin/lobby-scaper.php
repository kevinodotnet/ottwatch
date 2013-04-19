<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
#ob_start();

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

LobbyistController::scrapeForNewLobbyActivities($argv[1]);
LobbyistController::fixLobbyingNames();

?>
