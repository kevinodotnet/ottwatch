<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

$tables[] = "candidate";
$tables[] = "candidate_donation";
$tables[] = "candidate_return";
$tables[] = "category";
$tables[] = "consultation";
$tables[] = "consultationdoc";
$tables[] = "devapp";
$tables[] = "devappfile";
$tables[] = "devappstatus";
$tables[] = "electedofficials";
$tables[] = "ifile";
$tables[] = "item";
$tables[] = "itemvote";
$tables[] = "itemvotecast";
$tables[] = "itemvotetab";
$tables[] = "lobbyfile";
$tables[] = "lobbying";
$tables[] = "meeting";
$tables[] = "mfippa";
$tables[] = "opendata";
$tables[] = "opendatafile";
$tables[] = "places";
$tables[] = "polls_2010";

$file = OttWatchConfig::FILE_DIR."/ottwatch.sql";

$cmd = " mysqldump --complete-insert --extended-insert=false ";
$cmd .= " -u ".OttWatchConfig::DB_USER." --password=".OttWatchConfig::DB_PASS." ".OttWatchConfig::DB_NAME;
$cmd .= " ".implode(" ",$tables);
$cmd .= " > $file ";

`$cmd`;

#print "$cmd\n";

