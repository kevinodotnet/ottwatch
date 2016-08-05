<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

if (count($argv) > 1) {

  if ($argv[1] == 'injestBylaw') {
		$index = 2;
  	$pdf = $argv[$index++];
  	$summary = $argv[$index++];
  	$meetingid = $argv[$index++];

		injestBylaw($pdf,$summary,$meetingid);
	}

	return "Bad argv\n";
} 

print "missing arguments\n";
return;

function injestBylaw($pdf,$summary,$meetingid) {
	$num = $pdf;
	$num = preg_replace('/\.pdf$/','',$num);
	$num = preg_replace('/.*\//','',$num);

	$m = getDatabase()->one(" select * from meeting where meetid = $meetingid ");
	if (!isset($m['id'])) {
		print "bad meeting id: $meetingid\n";
		return;
	}

	$enacted = substr($m['starttime'],0,10);

	getDatabase()->execute(" delete from bylaw where bylawnum = :bylawnum and meetingid = :meetingid ",array(
		'bylawnum' => $num,
		'meetingid' => $m['id']
	));
	$id = getDatabase()->execute(" insert into bylaw (bylawnum,summary,meetingid) values (:bylawnum,:summary,:meetingid) ",array(
		'bylawnum' => $num,
		'summary' => $summary,
		'meetingid' => $m['id']
	));

	print "new row: $id\n";

	$bylawOttWatchLink = "http://ottwatch.ca/bylaws/$num";

	# generate an HTML title page
	ob_start();
	?>
	<html>
	<head>
	<title>By-Law NO. <?php print $num; ?> as of <?php print $enacted; ?>. OttWatch By-Law Archive</title>
	</head>
	<body>
	
	<img style="float: right; padding: 0px 0px 10px 10px;" src="http://ottwatch.ca/img/ottwatch.png"/>
	<h1>OttWatch.ca By-law Archival Project</h1>

	<p>
	OttWatch.ca has begun archiving copies of all by-laws passed by the City of Ottawa. After each City Council meeting
	we ask for copies of by-laws enacted at the meeting, add this title page, and upload them to ottwatch.ca.
	</p>

	<p>
	Be aware though that you may not be looking at the most recent version of this by-law. It is very possible that it
	has been amended by Council by another by-law. So, um, just know that before assuming anything.
	</p>

	<p>
	This PDF contains a copy of the by-law as passed by Council on a certain date. It is a snapshot in time. It could
	still be in force. It might have been amended. It may have been repealed.
	</p>

	<p>
	When in doubt, visit
	http://ottawa.ca/en/residents/laws-licenses-and-permits/laws/laws-z
	</p>

	<p>
	If you end up asking for a copy of a by-law OttWatch doesn't have yet, please ask the Clerk's office to cc:
	kevino@kevino.net when they email it to you. You'd be doing the entire community a favour!
	</p>

	<p>
	This title page generated on <i><?php print date('Y-m-d'); ?></i>
	</p>

	<h1>By-Law No. <?php print $num; ?></h1>
	<p><i><?php print $summary; ?></i></p>
	<p>
	<b>Enacted On:</b> <?php print $enacted; ?><br/>
	<b>Council Meeting:</b> <?php print "http://ottwatch.ca/meetings/meeting/$meetingid"; ?><br/>
	<b>OttWatch Bylaw Reference:</b> http://ottwatch.ca/bylaws/<?php print $num; ?><br/>
	</p>

	</body>
	</html>
	<?php
	$prefixHtml = ob_get_contents();
	ob_end_clean();

	# HTML to PDF, then combine the two PDFs
	file_put_contents("/tmp/tmp.html",$prefixHtml);
	`wkhtmltopdf /tmp/tmp.html /tmp/tmp.pdf`;
	$unitedPdf = "/tmp/united.pdf";
	`pdfunite /tmp/tmp.pdf $pdf $unitedPdf`;

	$baseUrl = "bylaws/$enacted/$num.pdf";
	$htmlUrl = "http://s3.ottwatch.ca/$baseUrl";
	$s3Url = "s3://s3.ottwatch.ca/$baseUrl";
	$s3UrlOrig = "s3://s3.ottwatch.ca/bylaws/$enacted/{$num}_original.pdf";

	`aws s3 cp --acl public-read $unitedPdf $s3Url`;
	`aws s3 cp --acl public-read $pdf $s3UrlOrig`;

	getDatabase()->execute(" update bylaw set url = '$htmlUrl' where id = $id ");

}

