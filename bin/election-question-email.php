<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/..");
require_once('include.php');
require_once('vendor/autoload.php');

$id = $argv[1];

$q = getDatabase()->one(" 
	select 
		q.title,
		q.body,
		eq.ward,
		p.*
	from election_question eq 
		join question q on q.id = eq.questionid 
		join people p on p.id = q.personid
	where 
		eq.id = $id 
		and published = 1 
");
$title = $q['title'];
$body = $q['body'];
$ward = $q['ward'];

$candidates = getDatabase()->all(" select * from candidate where year = 2014 and ward = $ward and nominated is not null and withdrew is null ");
#pr($q); exit;
#pr($candidates);
#exit;

foreach ($candidates as $c) {
	$ebody = "Dear {$c['first']},

An OttWatch reader, {$q['name']}, has submitted the following question to you via our election coverage page:

$title
$body

Your answer can be provided by reply-email. Mmax 2000 characters.

Sincerely,
Kevin O'Donnell
(613) 203-2620
OttWatch.ca

Please note: the question is online at http://ottwatch.ca/election/question/$id/

";

	$subject = "Campaign Question: $title";
	$email = $c['email'];
	if (strlen($email) > 0) {
		print "Sending to $email\n";
		sendEmail($email,$subject,$ebody);
	}

	#print $ebody;
	
}

sendEmail('kevino@kevino.net',$subject,$ebody);

