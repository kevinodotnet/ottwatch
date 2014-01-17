<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/..");
require_once('include.php');
require_once('vendor/autoload.php');

$rows = getDatabase()->all(" 
	select * from candidate 
	where 
		year = 2014 
		and nominated is not null 
		and email != ''
	order by ward,last ");

foreach ($rows as $r) {
	ob_start(); 

	$twitter = $r['twitter']; $twitter = ($twitter == '' ? 'None provided' : $twitter);
	$url = $r['url']; $url = ($url == '' ? 'None provided' : $url);
	$facebook = $r['facebook']; $facebook = ($facebook == '' ? 'None provided' : $facebook);
	$phone = $r['phone']; $phone = ($phone == '' ? 'None provided' : $phone);
	$email = $r['email']; $email = ($email == '' ? 'None provided' : $email);
	
?>Dear Candidate,

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
	$to = 'kevino@kevino.net';
	$subject = 'Your campaign contact information';
	sendEmail($email,$subject,$body);
	print "$email\n";
	#print $body;

}

?>
