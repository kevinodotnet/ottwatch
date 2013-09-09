<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

$rows = getDatabase()->all("
	select 
		d.devappid,
		d.statusdate,
		d.status,
		coalesce(p.status,'START') prevstatus,
		coalesce(datediff(d.statusdate,p.statusdate),0) duration
	from devappstatus d
		left join devappstatus p on p.devappid = d.devappid and p.statusdate = (select max(statusdate) from devappstatus where devappid = d.devappid and statusdate < d.statusdate)
	order by 
		d.devappid, d.statusdate;
");

//  	where d.devappid in (1010)
print '"'.implode('","',array_keys($rows[0])).'"'."\n";

foreach ($rows as $r) {
	$r['status'] = preg_replace('/"/',"'",$r['status']);
	$r['prevstatus'] = preg_replace('/"/',"'",$r['prevstatus']);
	
	print '"'.implode('","',$r).'"'."\n";
}

?>
