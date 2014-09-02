<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

$canInHtml = array();
getCandidates("http://ottawa.ca/en/city-hall/your-city-government/elections/councillor",0);
getCandidates("http://ottawa.ca/en/city-hall/your-city-government/elections/mayor",1);
$indb = getDatabase()->all(" select * from candidate where year = 2014 and withdrew is null and nominated is not null ");
foreach ($indb as $c) {
	$ok = 0;
	foreach ($canInHtml as $h) {
		if ($c['ward'] == $h['ward']
			&& $c['first'] == $h['first']
			&& $c['last'] == $h['last']) {
			$ok = 1;
		}
	}
	if ($ok == 0) {
		print "Withdraw? ward:{$c['ward']} {$c['first']} {$c['last']}\n";
	}
}
createPeople();
exit;

############################################################################################################################
# END
############################################################################################################################

function createPeople() {
	$rows = getDatabase()->all(" select * from candidate where email > '' and personid is null and nominated is not null and year = 2014 ");
	foreach ($rows as $r) {
		$o = getDatabase()->one(" select * from people where email = '" . $r['email'] . "'");
		if ($o['id']) {
			$values = array();
			$values['id'] = $r['id'];
			$values['personid'] = $o['id'];
			db_update('candidate',$values,'id');
			continue;
		}
		$values = array();
		$values['name'] = "{$r['first']} {$r['last']}";
		$values['email'] = $r['email'];
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

function reportUnknownLinks ($html) {

  $html = preg_replace('/"/',' ',$html);
  $html = preg_replace("/'/",' ',$html);
  $html = preg_replace("/</",' ',$html);
  $html = preg_replace("/>/",' ',$html);
	$chunks = explode(" ",$html);
  foreach ($chunks as $c) {
    $matches = array();
    if (preg_match('/http.*twitter.com\/(.*)/',$c,$matches)) {
      $twitter = $matches[1];
      if ($twitter != 'ottawacity') {
        print "twitter $twitter :: $c\n";
        $row = getDatabase()->one(" select * from candidate where year = 2014 and nominated is not null and withdrew is null and lower(twitter) = lower(:twitter) ",array("twitter"=>$twitter));
        pr($row);
      }
    }
  }
  exit;

}

function getCandidates($url,$isMayor) {

	global $canInHtml;
	$html = file_get_contents($url);
	if (strlen($html) == 0) {
		exit;
	}

  reportUnknownLinks($html);
  return;
	
	$tags = "";
	    $html = preg_replace("/\n/","KEVINO_NEWLINE",$html);
	    $html = preg_replace("/<head.*<body/","<body",$html);
	    $html = preg_replace("/&lang=en/","",$html); # not all HTML is escaped property, avoids <a href="...&lang=en" crap
	    $html = preg_replace("/<script[^<]+<\/script>/"," ",$html);
	    $html = preg_replace("/KEVINO_NEWLINE/","\n",$html);
	    $html = preg_replace("/ & /"," and ",$html);
	    $html = preg_replace("/<p[^>]+>/","",$html);
	    $html = preg_replace("/<\/p>/","__BR__",$html);
	    $html = strip_tags($html,"<div><br><a><h1><h2><h3><h4><h5><table><tr><td>");
	    $html = preg_replace("/__BR__/","<br/><br/>",$html);
	
	    # the view-dom-id CLASS changes randomly, so remove it
	    # example: view-dom-id-b477d62d0bdb286acc260d50c820060d
	    $html = preg_replace("/view-dom-id-[a-z0-9]+/","",$html);
	$html = preg_replace('/<br>/','',$html);
	
	    $xml = simplexml_load_string($html);
	#$html = ConsultationController::getCityContent($html,"<tr><td><a><div>");
	#$html = ConsultationController::getCityContent($html,"");
	
	$tables = $xml->xpath('//table');
	foreach ($tables as $t) {
		$t = simplexml_load_string($t->asXML());
		if ($isMayor) {
			$ward = 0;
		} else {
		$ward = $t->xpath("//div");
		$ward = $ward[0];
		$ward = preg_replace("/\n/","",$ward);
		$ward = preg_replace("/\r/","",$ward);
		$ward = trim($ward);
		if (!preg_match('/Ward (\d+) /',$ward,$matches)) {
			print "FAILED TO MATCH WARD\n";
			exit;
		}
		$ward = $matches[1];
		}
		$trs = $t->xpath("//tr");
		array_shift($trs);
	
		foreach ($trs as $tr) {
			$tr = simplexml_load_string($tr->asXML());
			$tds = $tr->xpath("//td");
			$name = trim($tds[0].''); 
			$name = preg_replace("/  /"," ",$name);
			$name = preg_replace("/  /"," ",$name);
			$name = preg_replace("/  /"," ",$name);
			$name = preg_replace("/  /"," ",$name);
			# print "NAME: $name\n";
			$name = explode(" ",$name);
			$first = $name[0];
			$last = $name[count($name)-1];
	
			if ($last == 'LeFaivre') { $last = 'Fortin LeFaivre'; }
	
			if ($first == 'No') { continue; } # no candidates in ward yet.
	
			$row = getDatabase()->one(" select count(1) c from candidate where ward = $ward and year = 2014 and last = '$last' and first = '$first' and nominated is not null ");
			$count = $row['c'];
			if ($count != 1) {
				print "CANDIDATE NOT FOUND: ward: $ward first: $first last: $last\n";
			}
	
			$canInHtml[] = array('ward'=>$ward,'first'=>$first,'last'=>$last);
	
			continue;
			$contact = $tds[1]->asXML();
			$contact = preg_replace("/\n/"," ",$contact);
			$contact = preg_replace("/\r/"," ",$contact);
			$contact = preg_replace("/</"," <",$contact);
			$contact = strip_tags($contact);
			$contact = preg_replace("/  /"," ",$contact);
			$contact = preg_replace("/  /"," ",$contact);
			$contact = preg_replace("/  /"," ",$contact);
			$contact = preg_replace("/  /"," ",$contact);
			$contact = preg_replace("/  /"," ",$contact);
			$contact = preg_replace("/  /"," ",$contact);
			$contact = preg_replace("/  /"," ",$contact);
			$values = explode(" ",$contact);
			$email = '';
			$facebook = '';
			$web = '';
			foreach ($values as $v) {
				if ($v == 'Website:') { continue; }
				if (preg_match('/@/',$v)) {
					$email = $v;
				} else if (preg_match('/faceboom.com/',$v)) {
					$facebook  = $v;
				} else if (preg_match('/^http/',$v)) {
					$web = $v;
				} else if ($v == '') {
					continue;
				} else {
					#print "UNKNOWN: $v\n";
				}
			}
			/*
			pr($values);
			print "name: $name\n";
			print "email: $email\n";
			print "facebook: $facebook\n";
			print "web: $web\n";
			# print "name: $name contact $contact\n";
			#pr($contact);
			*/
		}
	
	}

}


