<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/..");
require_once('include.php');
require_once('vendor/autoload.php');

$id = $argv[1];
$last = $argv[2];

$q = getDatabase()->one(" 
	select 
		q.title qTitle,
		q.body qBody,
		c.first, c.last, c.personid,
		a.body answer
	from election_question eq 
		join question q on q.id = eq.questionid 
		join candidate c on c.year = 2014 and c.ward = eq.ward and nominated is not null and withdrew is null
		left join answer a on a.questionid = q.id and a.personid = c.personid
	where 
		eq.id = $id 
		and published = 1 
		and c.last like '%$last%'
");

pr($q);

$answer = '';
while($f = fgets(STDIN)){
	$answer .= "$f";
}

getDatabase()->execute(" insert into answer (questionid,personid,body) values (:q,:p,:b) on duplicate key update body = :b, updated = now() ",array(
	'q'=>$id,
	'p'=>$q['personid'],
	'b'=>$answer
));

#print "-----\n";
#print $answer;
#print "-----\n";
#pr($q);

#pr($q);

