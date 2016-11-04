<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");

require_once('include.php');

if ($argv[1] == 'scrapeCommitteeOfAdjustment') {
  $file = $argv[2];

	$matches = array();
	if (!preg_match('/.*coa-(\d\d\d\d-\d\d-\d\d)-panel(\d).pdf$/',$file,$matches)) {
		print "Invalid COA filename; could not parse date/panel number\n";
		exit;
	}
	$date = $matches[1];
	$panel = $matches[2];

  DevelopmentAppController::scrapeCommitteeOfAdjustment($date,$panel,$file);
  return;
}

if ($argv[1] == 'coaAgendaToDevApp') {
  DevelopmentAppController::coaAgendaToDevApp();
	return;
}

if ($argv[1] == 'recent') {
	$match = $argv[2];
	$rows = getDatabase()->all(" select category,starttime,meetid from meeting where category like 'COA%' and meetid != '' order by starttime desc limit 9 ");
	foreach ($rows as $r) {
		$meetid = $r['meetid'];
		print "##############################################\n";
		pr($r);
		print "##############################################\n";
		coaNoticeToText($meetid,$match);
	}
	return;
}

if ($argv[1] == 'coaNoticeToText') {

	$meetid = $argv[2];
	$match = $argv[3];

	coaNoticeToText($meetid,$match);

	return;

}

function coaNoticeToText($meetid,$match) {
	$sql = "
		select m.meetid, i.id itemid, i.title itemtitle, f.id fileid, f.* 
		from meeting m 
			join item i on i.meetingid = m.id 
			join ifile f on f.itemid = i.id 
		where 
			meetid = $meetid
			and f.title like '%notice%'
	";
	$rows = getDatabase()->all($sql);
	foreach ($rows as $r) {
		$url = "http://ottwatch.ca/meetings/file/{$r['fileid']}";
		$pdf = c_file_get_contents($url);

		global $OTTVAR;
		file_put_contents("$OTTVAR/pdf/fileid_{$r['fileid']}.pdf",$pdf);
		`pdftotext $OTTVAR/pdf/fileid_{$r['fileid']}.pdf $OTTVAR/pdf/fileid_{$r['fileid']}.txt`;
		#system(" grep -C 2 -i '$match' $OTTVAR/pdf/fileid_{$r['fileid']}.txt");
		$lines = ` grep -C 2 -i '$match' $OTTVAR/pdf/fileid_{$r['fileid']}.txt`;
		if ($lines != '') {
			print "\n";
			print "-------------------------------------\n";
			print "http://ottwatch.ca/meetings/meeting/$meetid\n";
			print "-------------------------------------\n";
			print "{$r['itemtitle']}\n";
			print "\n$lines\n";
		}
	}
}


print "ERROR: bad ARGV\n";

