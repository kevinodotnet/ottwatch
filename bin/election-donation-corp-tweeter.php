<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');
require_once('twitteroauth.php');

$rows = getDatabase()->all(" 
	select 
		t.id,
		d.name,
		d.address,
		d.city,
		d.amount,
		c.first,
		c.last
	from tmp_tweet_corporate_donations t
		join candidate_donation d on d.id = t.id
		join candidate_return r on r.id = d.returnid
		join candidate c on c.id = r.candidateid
		join election e on e.id = c.electionid
	order by rand()
	limit 1
	");

foreach ($rows as $r) {
	$sql = " update tmp_tweet_corporate_donations set tweeted = 1 where id = " . $r['id'];
	getDatabase()->execute(" update tmp_tweet_corporate_donations set tweeted = 1 where id = " . $r['id']);
	$tweet = "{$r['last']}: \${$r['amount']} from {$r['name']}, {$r['address']}, {$r['city']} http://ottwatch.ca/election/donation/{$r['id']} #ottpoli";
	tweet($tweet);
}

