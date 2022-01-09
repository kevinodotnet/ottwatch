<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

apiEmployeeDirectory('clayton','foster','');

function apiEmployeeDirectory($first,$last,$detail) {
	$url = 'http://ottawa.ca/cgi-bin/apps/empdir/citytel.pl?lang=en&lname='.urlencode($last).'&fname='.urlencode($first).'&extf=&submit=Search';
	$html = c_file_get_contents($url);
	$html = preg_replace("/\n/"," ",$html);
	$html = preg_replace("/\r/"," ",$html);
	$html = preg_replace("/\t/"," ",$html);
	$html = preg_replace("/  */"," ",$html);
	$html = preg_replace("/<tr/","\n<tr",$html);
	$html = preg_replace("/<TR/","\n<TR",$html);
	foreach (explode("\n",$html) as $line) {
		if (!preg_match("/^send.pl/i",$line)) { continue; }

		# <TR bgcolor=ffffcc>
		#<TD valign=top><B>Foster , Clayton</B></TD>
		#<TD valign=top>Prj Mgr Security Systems & Coordination</TD>
		#<TD valign=top>Emergency & Protective Services Dept.</TD>
		#<TD valign=top>Security & Emergency Management Branch</TD>
		#<TD valign=top>613-580-2424 x24114</TD>
		#<TD valign=center><a href="/cgi-bin/mail/send.pl?to=CLEnor8YfT8Aw&lang=en"><center><img src=/images/mail.gif width=14 height=10 border=0></center></a></TD>
		#<TD valign=top></TD></TR> </td></tr></TABLE> <P>The Telephone Directory is updated daily. <HR size=1><h3></H3> <style type="text/css"> #contentWrapper table {margin:auto !important;} </style> <form METHOD="GET" > 
		# <input type="hidden" name="lang" value="en"> <table width="50%" border="0"> 

		print "$line\n";
	}
	
}

