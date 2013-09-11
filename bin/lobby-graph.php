<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

$rows = getDatabase()->all("
	select 
    *,
    case 
          when activity = 'Meeting' then 5
          when activity = 'Telephone' then 3
          when activity = 'Email' then 1
          when activity = 'Mail' then 1
          when activity = 'Other' then 1
          else 1 end weight
  from lobbyfile f
    join lobbying l on l.lobbyfileid = f.id
");

//  	where d.devappid in (1010)
print '"'.implode('","',array('from','to')).'"'."\n";

foreach ($rows as $r) { 
  foreach ($r as $k => $v) {
    $v = preg_replace('/"/','',$v);
    $r[$k] = $v;
  }
  print '"'.implode('","',array($r['lobbyist'],$r['client'],$r['weight'])).'"'."\n";
#	$r['status'] = preg_replace('/"/',"'",$r['status']);
#	$r['prevstatus'] = preg_replace('/"/',"'",$r['prevstatus']);
#	print '"'.implode('","',$r).'"'."\n";
}

?>
