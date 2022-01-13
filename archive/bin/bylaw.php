<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

if (count($argv) > 1) {

  if ($argv[1] == 'missing') {
		$rows = getDatabase()->all(" select concat(b1.bn_year,'-',b1.bn_num-1) missing from bylaw b1 left join bylaw b2 on b2.bn_year = b1.bn_year and b2.bn_num = (b1.bn_num-1) where b2.id is null order by b1.bn_year desc, b1.bn_num desc ");
		foreach ($rows as $r) {
			print "{$r['missing']}\n";
		}
	}
  if ($argv[1] == 'injestBylaw') {
		$index = 2;
  	$pdf = $argv[$index++];
  	$pdf = "/home/ubuntu/bylaw/$pdf";
  	$summary = $argv[$index++];
  	$enacted = $argv[$index++];

		injestBylaw($pdf,$summary,$enacted);
	}

	return "Bad argv\n";
} 

print "missing arguments\n";
return;

function injestBylaw($pdf,$summary,$enacted) {

	$num = $pdf;
	$num = preg_replace('/\.pdf$/','',$num);
	$num = preg_replace('/.*\//','',$num);

	getDatabase()->execute(" delete from bylaw where bylawnum = :bylawnum ",array(
		'bylawnum' => $num
	));
	$id = getDatabase()->execute(" insert into bylaw (bylawnum,summary,enacted) values (:bylawnum,:summary,:enacted) ",array(
		'bylawnum' => $num,
		'summary' => $summary,
		'enacted' => $enacted
	));
	print "new row: $id\n";

	# extract year/num from the by law number itself
	getDatabase()->execute(" update bylaw set bn_year = left(bylawnum,4), bn_num = 0+substr(bylawnum,6,length(bylawnum)) ");

	$bylawOttWatchLink = "http://ottwatch.ca/bylaws/$num";

	# generate an HTML title page
	ob_start();
	?>
	<html>
	<body>

	<div>
	<h1>By-Law No. <?php print $num; ?></h1>
	<p><i><?php print $summary; ?></i></p>
	</div>
	
	<img style="float: right; padding: 0px 0px 10px 10px;" src="http://ottwatch.ca/img/ottwatch.png"/>
	<h1>OttWatch.ca By-law Archival Project</h1>

	<p>
	OttWatch.ca has begun archiving copies of all by-laws passed by the City of Ottawa. After each City Council meeting
	we ask for copies of by-laws enacted at the meeting, add this title page, and upload them to ottwatch.ca.
	</p>

	<p>
	Be aware though that you may not be looking at the most recent version of this by-law. It is very possible that it
	has been amended by Council by another by-law, or even by the Ontario Municipal Board. So, um, just know that before assuming anything.
	</p>

	<p>
	This PDF contains a copy of the by-law as passed by Council on a certain date. It is a snapshot in time. It could
	still be in force. It might have been amended. It may have been repealed.
	</p>

	<p>
	When in doubt, visit
	https://ottawa.ca/en/residents/laws-licenses-and-permits/laws
	</p>

	<p>
	If you end up asking for a copy of a by-law OttWatch doesn't have yet, please ask the Clerk's office to cc:
	kevino@kevino.net when they email it to you. You'd be doing the entire community a favour!
	</p>

	<p>
	<b>Enacted On:</b> <?php print $enacted; ?><br/>
	<b>OttWatch Bylaw Reference:</b> http://ottwatch.ca/bylaws/<?php print $num; ?><br/>
	</p>

	<p>
	This title page generated on <i><?php print date('Y-m-d'); ?></i>
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
