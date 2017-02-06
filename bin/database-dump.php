<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

if (0) {
	$rows = getDatabase()->all(" show tables ");
	foreach ($rows as $r) {
		$table = $r['Tables_in_ottwatch'];
		$tables[] = $table;
		print "\$tables[] = '$table';\n";
	}
	#pr($tables);
	exit;
}

$tables = getTables();

$file = OttWatchConfig::FILE_DIR."/ottwatch.sql.gz";
$cmd = " mysqldump --complete-insert --extended-insert=false ";
$cmd .= " -u ".OttWatchConfig::DB_USER." --password=".OttWatchConfig::DB_PASS." ".OttWatchConfig::DB_NAME;
$cmd .= " ".implode(" ",$tables);
$cmd .= " | gzip -9 > $file ";

print "exporting...\n";
`$cmd`;
print "uploading...\n";
`aws s3 cp --acl public-read $file s3://s3.ottwatch.ca/ottwatch.sql.gz`;
print "done...\n";

exit;

function getTables() {
$tables = array();
// $tables[] = 'answer';
// $tables[] = 'archive_candidate_donation';
// $tables[] = 'archive_candidate_donation2';
// $tables[] = 'budget_capital';
// $tables[] = 'budget_draft_2015';
// $tables[] = 'buildingPermits_2015';
$tables[] = 'bylaw';
$tables[] = 'candidate';
$tables[] = 'candidate_donation';
$tables[] = 'candidate_return';
$tables[] = 'category';
$tables[] = 'consultation';
$tables[] = 'consultationdoc';
// $tables[] = 'data311';
$tables[] = 'devapp';
$tables[] = 'devappfile';
$tables[] = 'devappstatus';
$tables[] = 'electedofficials';
$tables[] = 'election';
// $tables[] = 'election_donor';
// $tables[] = 'election_question';
$tables[] = 'election_vote';
$tables[] = 'election_vote_candidate';
$tables[] = 'election_vote_race';
$tables[] = 'feed';
/*
$tables[] = 'geo_cyclingnetwork_20151207';
$tables[] = 'geo_cyclingwinter1516_20151207';
$tables[] = 'geo_devapps_20160606';
$tables[] = 'geo_devapps_20160613';
$tables[] = 'geo_devapps_20160614';
$tables[] = 'geo_former_municipalities';
$tables[] = 'geo_old_property_zoning';
$tables[] = 'geo_ons_boundaries';
$tables[] = 'geo_pedestrianplan_existingsignals_20160201';
$tables[] = 'geo_policedistrict_20161024';
$tables[] = 'geo_policezone_20161024';
$tables[] = 'geo_property';
$tables[] = 'geo_property1';
$tables[] = 'geo_property_address';
$tables[] = 'geo_property_addresses_20160229';
$tables[] = 'geo_property_parcels_20150402';
$tables[] = 'geo_property_parcels_20160121';
$tables[] = 'geo_property_parcels_20160229';
$tables[] = 'geo_property_zoning';
$tables[] = 'geo_publicwashrooms_20160111';
$tables[] = 'geo_roadinformationlanes_20160706';
$tables[] = 'geo_schools';
$tables[] = 'geo_sidewalks_20150914';
$tables[] = 'geo_splashpads_20150817';
$tables[] = 'geo_street_names_20150402';
$tables[] = 'geo_street_names_20151006';
$tables[] = 'geo_street_names_20160217';
$tables[] = 'geo_street_names_20160902';
$tables[] = 'geo_wadingpools_20150817';
$tables[] = 'geo_zoning';
$tables[] = 'geo_zoning1';
$tables[] = 'geo_zoning_20150403';
$tables[] = 'geo_zoning_20150729';
$tables[] = 'geo_zoning_20160116';
$tables[] = 'geo_zoning_20160229';
$tables[] = 'geo_zoning_singles';
$tables[] = 'geometry_columns';
*/
$tables[] = 'ifile';
$tables[] = 'item';
$tables[] = 'itemvote';
// $tables[] = 'itemvote_20161013';
$tables[] = 'itemvotecast';
// $tables[] = 'itemvotecast_summary1';
// $tables[] = 'itemvotecast_summary2';
// $tables[] = 'itemvotetab';
// $tables[] = 'latelobbying';
$tables[] = 'lobbyfile';
$tables[] = 'lobbying';
// $tables[] = 'md5hist';
$tables[] = 'meeting';
$tables[] = 'mfippa';
// $tables[] = 'oc_stops';
// $tables[] = 'ocgps_20150210';
$tables[] = 'opendata';
$tables[] = 'opendatafile';
// $tables[] = 'people';
// $tables[] = 'permit';
// $tables[] = 'places';
// $tables[] = 'polls_2010';
// $tables[] = 'polls_2014';
$tables[] = 'publicevent';
// $tables[] = 'question';
// $tables[] = 'question_vote';
// $tables[] = 'ridings_2011';
// $tables[] = 'roadways';
// $tables[] = 'roadways_2015';
// $tables[] = 'spatial_ref_sys';
// $tables[] = 'story';
// $tables[] = 'story54';
// $tables[] = 't';
// $tables[] = 'variable';
// $tables[] = 'votinglocations_2010';
// $tables[] = 'wards_2010';
return $tables;
}
