<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/..");
require_once('include.php');
require_once('vendor/autoload.php');

$rows = getDatabase()->all(" 
	select 
		*
	from 
		candidate 
	where 
		year = 2014 
		and nominated is not null 
		and withdrew is null 
		and email != ''
	order by ward,last 
");

foreach ($rows as $r) {

	$twitter = $r['twitter']; $twitter = ($twitter == '' ? 'None provided' : $twitter);
	$url = $r['url']; $url = ($url == '' ? 'None provided' : $url);
	$facebook = $r['facebook']; $facebook = ($facebook == '' ? 'None provided' : $facebook);
	$phone = $r['phone']; $phone = ($phone == '' ? 'None provided' : $phone);
	$email = $r['email']; $email = ($email == '' ? 'None provided' : $email);

	$qs = getDatabase()->all("
		select
			qp.name,
			q.*, a.body answer, a.created answerDate
		from
			question q
			join people qp on qp.id = q.personid
			join election_question eq on eq.questionid = q.id
			left join answer a on a.questionid = q.id and a.personid = {$r['personid']}
		where
			q.published = 1
			and eq.ward = {$r['ward']}
			and (a.body is null or a.body = '')
	");
	if (count($qs) == 0) {
		# no question unanswered, no email.
		continue;
	}
	#pr($r);
	#pr($qs);
	ob_start(); 

?>Dear <?php print $r['first']; ?>,

OttWatch.ca is providing a forum for voters in your ward to ask you questions. 
The following question(s) have been asked, but you have not yet provided answer.

The easiest way to provide an answer is to log into OttWatch directly. 

Your username is your campaign email address: <?php print $email; ?>
Your password is: 



<?php 
foreach ($qs as $q) {

?><?php print $OTT_WWW."/user/login?next=".urlencode("/election/question/".$q['id']."/"); ?>

<?php } ?>

OttWatch.ca maintains an up-to-date list of campaign contact information. In order to ensure accuracy and fill in any omissions, 
please let me know if any of the below information should be updated:

Website:
<?php print "$url\n"; ?>

Twitter: 
<?php print "$twitter\n"; ?>

Facebook: 
<?php print "$facebook\n"; ?>

Phone: 
<?php print "$phone\n"; ?>

Email: 
<?php print "$email\n"; ?>

The above information is shown on http://ottwatch.ca/election/ward/<?php print "{$r['ward']}\n"; ?>

Cheers,
Kevin O'Donnell
ottwatch@ottwatch.ca
(613) 203-2620

<?php
	$body = ob_get_clean();
	#$to = 'kevino@kevino.net';
	#$subject = 'Your campaign contact information';
	#sendEmail($email,$subject,$body);
	print "$email\n";
	print $body;
	break;

}

