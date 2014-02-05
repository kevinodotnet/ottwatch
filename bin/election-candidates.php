<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

#
# Create PEOPLE accounts for candidates, when needed.
#
function createPeople() {
	$rows = getDatabase()->all(" select * from candidate where email > '' and personid is null and nominated is not null and year = 2014 ");
	foreach ($rows as $r) {
		$values = array();
		$values['name'] = "{$r['first']} {$r['last']}";
		$values['email'] = $r['email'];
		print "Saving person for {$values['name']}\n";
		pr($values);
		$id = db_insert('people',$values);
		$values = array();
		$values['id'] = $id;
		$values['password'] = md5($id.":".rand(0,20000));
		db_update('people',$values,'id');
		$values = array();
		$values['id'] = $r['id'];
		$values['personid'] = $id;
		db_update('candidate',$values,'id');
	}
}

##########################################################################################
# TRUSTEE
##########################################################################################
/*

$url = "http://ottawa.ca/en/city-hall/your-city-government/elections/school-board-trustee";

$html = file_get_contents($url);
$html = ConsultationController::getCityContent($html,"<h3><table><tr><td><th>");

$html = preg_replace("/\n/",'',$html);
$html = preg_replace("/<tr/","\n<tr",$html);
$html = preg_replace("/<\/tr>/","<\/tr>\n",$html);
$html = preg_replace("/<h2/","\n<h2",$html);
$html = preg_replace("/<\/h2>/","<\/h2>\n",$html);
$html = preg_replace("/<h3/","\n<h3",$html);
$html = preg_replace("/<\/h3>/","<\/h3>\n",$html);
$lines = explode("\n",$html);

foreach ($lines as $l) {

	if (preg_match('/<h2/',$l)) {
		$board = strip_tags($l);
		continue;
	}
	if (preg_match('/<h3>/',$l)) {
		$matches = array();
		if (!preg_match('/<h3>Zone (\d+)/',$l,$matches)) {
			print "ERROR: $l\n";
		}
		$zone = $matches[1];
		continue;
	}

	if (!preg_match('/^<tr/',$l)) { continue; }
	if (preg_match('/Email Address/',$l)) { continue; }
	if (preg_match('/No candidate/i',$l)) { continue; }
  $l = preg_replace('/<td>/',"\t",$l);
  $l = preg_replace('/not provided/',"",$l);
  $l = preg_replace('/not provided/',"",$l);
  $l = strip_tags($l);
  $l = trim($l);
  $row = explode("\t",$l);

  $names = explode(" ",$row[0]);

  $candidate['ward'] = $zone;
  $candidate['wardname'] = $board;
  $candidate['first'] = trim($names[0]);
  $candidate['last'] = trim($names[count($names)-1]);
  $candidate['phone'] = trim(@$row[1]);
  $candidate['fax'] = trim(@$row[2]);
  $candidate['email'] = trim(@$row[3]);

  $c = " select count(1) c from candidate where ward = ${candidate['ward']} and first = '{$candidate['first']}' and last = '{$candidate['last']}'; ";
  $i = "insert into candidate (ward,year,first,last) values ({$candidate['ward']},2014,'{$candidate['first']}','{$candidate['last']}'); ";
  $u = "update candidate set nominated = (case when nominated is null then now() else nominated end) , phone = '{$candidate['phone']}', email = '{$candidate['email']}'  where ward = {$candidate['ward']} and first = '{$candidate['first']}' and last = '{$candidate['last']}'; ";
  $key = "{$candidate['ward']} {$candidate['first']} {$candidate['last']}";
  $sql[] = array('ward'=>$candidate['ward'],'insert'=>$i,'update'=>$u,'count'=>$c,'details'=>$candidate);

}

pr($sql);

return;
*/

##########################################################################################
# MAYOR
##########################################################################################

if (false) {
$url = "http://ottawa.ca/en/city-hall/your-city-government/elections/mayor";
$html = file_get_contents($url);
$html = ConsultationController::getCityContent($html,"<h3><table><tr><td><th>");

$html = preg_replace("/\n/",'',$html);
$html = preg_replace("/<tr/","\n<tr",$html);
$html = preg_replace("/<\/tr>/","<\/tr>\n",$html);
$html = preg_replace("/<h3/","\n<h3",$html);
$html = preg_replace("/<\/h3>/","<\/h3>\n",$html);
$lines = explode("\n",$html);

$ward = 0;
$wardname = 'Mayor';

foreach ($lines as $l) {
	if (!preg_match('/^<tr/',$l)) { continue; }
	if (preg_match('/Email Address/',$l)) { continue; }
  $l = preg_replace('/<td>/',"\t",$l);
  $l = preg_replace('/not provided/',"",$l);
  $l = preg_replace('/not provided/',"",$l);
  $l = strip_tags($l);
  $l = trim($l);
  $row = explode("\t",$l);


  $names = explode(" ",$row[0]);

	$candidate = array();
  $candidate['ward'] = $ward;
  $candidate['wardname'] = $wardname;
  $candidate['first'] = $names[0];
  $candidate['last'] = $names[count($names)-1];
  $candidate['phone'] = @$row[1];
  $candidate['email'] = @$row[2];
  $candidate['web'] = '';

  $c = " select count(1) c from candidate where ward = $ward and first = '{$candidate['first']}' and last = '{$candidate['last']}'; ";
  $i = "insert into candidate (ward,year,first,last) values ($ward,2014,'{$candidate['first']}','{$candidate['last']}'); ";
  $u = "update candidate set nominated = (case when nominated is null then now() else nominated end) , url = '{$candidate['web']}', phone = '{$candidate['phone']}', email = '{$candidate['email']}'  where year = 2014 and ward = $ward and first = '{$candidate['first']}' and last = '{$candidate['last']}'; ";
  $key = "$ward {$candidate['first']} {$candidate['last']}";
  $sql[] = array('ward'=>$ward,'name'=>$key,'insert'=>$i,'update'=>$u,'count'=>$c,'details'=>$candidate);
}

}

##########################################################################################
# CANDIDATES
##########################################################################################

$url = "http://ottawa.ca/en/city-hall/your-city-government/elections/councillor";
$html = @file_get_contents($url);
if (strlen($html) == 0) {
	exit;
}
$html = ConsultationController::getCityContent($html,"<h3><table><tr><td><th>");

$html = preg_replace("/\n/",'',$html);
$html = preg_replace("/<tr/","\n<tr",$html);
$html = preg_replace("/<\/tr>/","<\/tr>\n",$html);
$html = preg_replace("/<h3/","\n<h3",$html);
$html = preg_replace("/<\/h3>/","<\/h3>\n",$html);
$lines = explode("\n",$html);

$ward = -1;
$wardname = '';

foreach ($lines as $l) {
  if (!(preg_match('/<h3>/',$l) || preg_match('/^<tr/',$l))) { continue; }
  if (preg_match('/<th/',$l)) { continue; }
  if (preg_match('/No Candidate/',$l)) { continue; }

	$candidate = array();
	$candidate['twitter'] = '';
	$candidate['facebook'] = '';
	$candidate['web'] = '';

  $matches = array();
  if (preg_match('/^<h3/',$l)) {
		$l = preg_replace('/-/',' - ',$l);
		$l = preg_replace('/–/',' - ',$l);
		$l = preg_replace('/  /',' ',$l);
		$l = preg_replace('/  /',' ',$l);
		$l = preg_replace('/  /',' ',$l);
		$l = preg_replace('/  /',' ',$l);
	  if (preg_match('/Ward (\d+) - ([^<]+)/',$l,$matches)) {
	    #print "$l\n";
	    $ward = $matches[1];
	    $wardname = $matches[2];
			#print "$l\n";
	  } else {
			print "FAIL: $l\n";
		}
    continue;
	}


  $l = preg_replace('/<td/',"\t<td",$l);
  $l = preg_replace('/not provided/',"",$l);
  $l = preg_replace('/not provided/',"",$l);
  $l = strip_tags($l);
  $l = trim($l);
  $row = explode("\t",$l);

  $names = explode(" ",$row[0]);

	if (isset($row[3])) {
		$other = $row[3];
		$other = preg_replace('/website/i',' web ',$other);
		$other = preg_replace('/twitter/i',' twitter ',$other);
		$other = preg_replace('/facebook/i',' facebook ',$other);
		$other = preg_replace('/linkedin/i',' linkedin ',$other);
		$other = preg_replace('/fax number/i',' fax ',$other);
		$other = preg_replace('/http:\/\//i','',$other);
		$other = preg_replace('/https:\/\//i','',$other);
		$other = preg_replace('/:/','',$other);
		$other = preg_replace('/^ */','',$other);
		$other = preg_replace('/ /',' ',$other);
		$other = preg_replace('/  /',' ',$other);
		$other = preg_replace('/  /',' ',$other);
		$other = preg_replace('/  /',' ',$other);
		$other = preg_replace('/  /',' ',$other);
		$other = preg_replace('/  /',' ',$other);
		$prev = '';
		foreach (explode(" ",$other) as $o) {
			if ($prev == 'web' || $prev == 'twitter' || $prev == 'facebook') {
				$candidate[$prev] = trim($o);
				if ($prev == 'facebook' && preg_match('/^\//',$candidate['facebook'])) {
					$candidate['facebook'] = "http://facebook.com{$candidate['facebook']}";
				}
				if ($prev == 'twitter') {
					$candidate['twitter'] = preg_replace('/^@/','',$candidate['twitter']);
				}
			}
			$prev = $o;
		}

		#print "-------------------\n";
		#print "$other\n";
		#pr($parts);
	}


  $candidate['ward'] = $ward;
  $candidate['wardname'] = $wardname;
  $candidate['first'] = $names[0];
  $candidate['last'] = $names[count($names)-1];
  $candidate['phone'] = @$row[1];
  $candidate['email'] = @$row[2];
  foreach ($candidate as $k => $v) {
    $v = preg_replace('/ /','',$v);
    $candidate[$k] = $v;
    #print "$k = '$v'\n";
  }

  $c = " select count(1) c from candidate where 
		(year = 2014 and ward = $ward and first = '{$candidate['first']}' and last = '{$candidate['last']}')
		or (year = 2014 and nominated is null and incumbent = 1 and ward = $ward and first = '{$candidate['first']}' and last = '{$candidate['last']}')
	;
	";
  $i = "insert into candidate (ward,year,first,last) values ($ward,2014,'{$candidate['first']}','{$candidate['last']}'); ";
  $u = "update candidate set 
		nominated = (case when nominated is null then now() else nominated end) , 
		phone = '{$candidate['phone']}', 
		twitter = '{$candidate['twitter']}', 
		facebook = '{$candidate['facebook']}', 
		url = '{$candidate['web']}', 
		email = '{$candidate['email']}'
		where year = 2014 and ward = $ward and first = '{$candidate['first']}' and last = '{$candidate['last']}'; ";
/*  $u = "update candidate set 
		nominated = (case when nominated is null then now() else nominated end) , phone = '{$candidate['phone']}', email = '{$candidate['email']}'  
		where year = 2014 and ward = $ward and first = '{$candidate['first']}' and last = '{$candidate['last']}'; ";*/
  $key = "$ward {$candidate['first']} {$candidate['last']}";
  $sql[] = array('ward'=>$ward,'name'=>$key,'insert'=>$i,'update'=>$u,'count'=>$c,'details'=>$candidate);

#	if ($candidate['web'] != '') {
#		print "update candidate set url = '{$candidate['web']}' where (url is null or url = '') and year = 2014 and ward = {$candidate['ward']} and last = '{$candidate['last']}'; \n";
#	}

  #pr($candidate);
}

$indb = array();
$inhtml = array();

$all  = getDatabase()->all(" select * from candidate where nominated is not null and year = 2014 ");
foreach ($all as $a) {
	$key  = "ward:{$a['ward']} last:{$a['last']} first:{$a['first']}";
	$indb[] = $key;
}
foreach ($sql as $key) {
	$htmlkey  = "ward:{$key['details']['ward']} last:{$key['details']['last']} first:{$key['details']['first']}";
	$inhtml[] = $htmlkey;

	$c = $key['details'];

	$tweet = "NEW candidate: {$c['first']} {$c['last']} ({$c['wardname']}) http://ottwatch.ca/election/ward/{$key['ward']} #ottvote";

	#pr($key['details']);

  $u = $key['update'];
  $i = $key['insert'];
  $c = $key['count'];
  $key = $key['name'];

	$i = preg_replace("/\t/"," ",$i);
	$u = preg_replace("/\t/"," ",$u);
	$i = preg_replace("/\n/"," ",$i);
	$u = preg_replace("/\n/"," ",$u);

  $row = getDatabase()->one($c);

  if ($row['c'] == 0) {
    print "$key added: NEW candidate\n";
		print "$tweet\n";
		print "\n$i\n$u\n";
    #getDatabase()->execute($i);
		#getDatabase()->execute($u);
		continue;
  }

  #$c = getDatabase()->execute($u);
	#print "$u\n";
	if ($c > 0) {
		print "\nupdating just cause maybe\n";
		print "\n$u\n";
		print "$key updated\n";
	}
}

#foreach ($indb as $a) { print "REPORT :: $a :: DATABASE\n"; }
#foreach ($inhtml as $a) { print "REPORT :: $a :: HTML\n"; }

$removed = array_diff($indb,$inhtml);
$added = array_diff($inhtml,$indb);

if (count($added) > 0) {
	print "Need to add to database:\n";
	pr($added);
}
if (count($removed) > 0) {
	print "\nNeed to remove from database\n";
	pr($removed);
}


createPeople();

return;

$no = array('sympatico.ca','gmail.com','hotmail.com','yahoo.com','gmail.com');
$rows = getDatabase()->all(" select id,email from candidate where year = 2014 and (url is null or url = '') and (email is not null and email != '') ");
foreach ($rows as $r) {
	$url = $r['email'];
	$url = preg_replace('/.*@/','',$url);
	$yes = 1;
	foreach ($no as $n) {
		if (preg_match("/$n/",$url)) {
			$yes = 0;
		}
	}
	if ($yes) {
		$sql = " update candidate set url = '$url' where id = {$r['id']}; ";
		print "$sql\n";
	}
}

?>
