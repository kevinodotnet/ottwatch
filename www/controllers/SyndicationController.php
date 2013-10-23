<?php

class SyndicationController {
	public static function publish() {

		# time of last run
    $last = getvar('syndicate.last');
    if ($last == '') { $last = time(); }

		# time of this run
		$now = time();
    setvar('syndicate.last',$now);

		# whats new doc?
    $rows = getDatabase()->all(" 
			select
				f.*,
				from_unixtime(:last) last,
				from_unixtime(:now) now
			from 
				feed f
			where 
				f.created >= from_unixtime(:last)
				and f.created < from_unixtime(:now)
			order by
				f.created
    ",array('last'=>$last));

		foreach ($rows as $r) {
			$message = $r['message'];
			$path = $r['path'];
			$url = $r['url'];

			print "\n-----------------------------------------------\n";
			pr($r);
			print "\n-----------------------------------------------\n";
			self::facebook($r);

		}

	}

	public static function facebook($r) {
		# POST variables to FB
		$data = array();

		# page post permission
		$row = getDatabase()->one(" select * from variable where name = 'fb_page_access_token' ");
		if (! $row['value']) {
			print "ERROR: no fb_page_access_token\n";
			return;
		}
		$data['access_token'] = $row['value'];

		$data['message'] = $r['message'];
		$data['link'] = $r['url'];
		if ($data['link'] == null) {
			# go with path
			$data['link'] = OttWatchConfig::WWW.$r['path'];
		}

		$url = "https://graph.facebook.com/".OttWatchConfig::FACEBOOK_PAGE_ID."/links";
		$json = sendPost($url,$data);
		$result = json_decode($json);

		if (!isset($result->id)) {
			print "\n!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n";
			print "ERROR posting to Facebook\n";
			pr($r);
			print "\n";
			print "----- DATA -----\n";
			pr($data);
			print "\n";
			print "----- JSON -----\n";
			print "$json\n";
			print "----- RESULT -----\n";
			pr($result);
			print "\n";
		}
		
	}
}

?>
