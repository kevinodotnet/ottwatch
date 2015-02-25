<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

$url = "http://ottawa.ca/en/city-hall/your-city-government/elections/2014-financial-statements";

$html = file_get_contents($url);
$html = preg_replace("/\n/"," ",$html);
$html = preg_replace("/\"/"," ",$html);

# trim HTML to only include council races
$html = preg_replace("/Ottawa Catholic School Board.*Mayor/"," ",$html);
$html = preg_replace("/Ottawa Catholic School Board.*/"," ",$html);

$urls = preg_grep("/documents.*ottawa.*\.pdf/i",explode(" ",$html));

$year = 2014;

foreach ($urls as $u) {
	$filename = preg_replace("/.*\//","",$u);
	$file = OttWatchConfig::FILE_DIR."/election/$year/financial_returns/$filename";
	if (file_exists($file)) {
		$rows = getDatabase()->all("
			select
				c.id,
				c.first,
				c.last,
				c.ward,
				r.filename,
				case when r.filename = '$filename' then 1 else 0 end filematch
			from
				candidate c
				join candidate_return r on r.candidateid = c.id
			where
				c.year = 2014
				and instr('$filename',c.first) > 0
				and instr('$filename',c.last) > 0
				and instr('$filename','_Original') > 0
				and r.filename != '$filename'
		");
		if (count($rows) == 0) {
			# not "_Original.pdf", 
			# or is a Trustee PDF
			# of PDF was downloaded and has been matched to a return
			# print "$filename already matched to a return, or is a trustee\n";
			if (preg_match('/Original/',$filename)) {
#				print "$filename\n";
			}
			continue;
		}
		print "$filename downloaded; name match to ".count($rows)." candidates\n";
		pr($rows);
		continue;
	}
	print "need to download $u ($file)\n";
}
