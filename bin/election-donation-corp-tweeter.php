<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');
require_once('twitteroauth.php');

# $rows = getDatabase()->all(" select * from candidate_donation where city = 'Ottawa' and location is null order by updated desc ");
# $rows = getDatabase()->all(" select * from candidate_donation where returnid = 18 and location is null order by rand() limit 10  ");
# $rows = getDatabase()->all(" select * from candidate_donation where created > '2015-01-01' and location is null order by rand() limit 10  ");

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
	where 
		e.id = 1
		and d.type = 1
		and c.winner = 1
	order by rand()
	limit 1
	");

foreach ($rows as $r) {
	$sql = " update tmp_tweet_corporate_donations set tweeted = 1 where id = " . $r['id'];
	getDatabase()->execute(" update tmp_tweet_corporate_donations set tweeted = 1 where id = " . $r['id']);
	$tweet = "{$r['last']}: \${$r['amount']} from {$r['name']}, {$r['address']}, {$r['city']} http://ottwatch.ca/election/donation/{$r['id']} #ottpoli";
	print "$tweet\n";
	tweet($tweet);
}

