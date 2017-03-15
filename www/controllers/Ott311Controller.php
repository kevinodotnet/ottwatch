<?php

class Ott311Controller {

	static $apiurl = 'http://city-of-ottawa-prod.apigee.net/open311/v2';

	static public function scan($daysAgo) {

		$start = new DateTime();
		$x = $daysAgo;
		$now = $start->sub(new DateInterval("P" . $x . "D"));
		$now->setTime(0,0,0);
		pr($now);

		for ($h = 0; $h < 24; $h++) {
			$start_date = $now->format('Y-m-d\TH:i:s-05:00');
			$end = clone $now;
			$end->add(new DateInterval("PT1H"));
			$end_date = $end->format('Y-m-d\TH:i:s-05:00');

			$url = self::$apiurl."/requests.json";
		  $url = "$url?start_date=$start_date";
			$url = "$url&end_date=$end_date";

			$now->add(new DateInterval("PT1H"));

		#self::$apiurl = 'http://city-of-ottawa-dev.apigee.net/open311/v2';
		# $url = "https://city-of-ottawa-prod.apigee.net/open311/v2/requests.json?1=1&start_date=$start_date&end_date=$end_date";
		# $url = "https://city-of-ottawa-dev.apigee.net/open311/v2/requests.json?1=1&start_date=$start_date&end_date=$end_date";
		# $url = "https://city-of-ottawa-dev.apigee.net/open311/v2/requests.json?=1&end_date=2017-01-06T00:00:00Z&start_date=2017-01-05T00:00:00Z";
		print "$url\n";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		$headers = array( 'api_key: '.OttWatchConfig::OTTAPI_KEY);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		#curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		#curl_setopt($ch, CURLOPT_VERBOSE, true);

		$json = curl_exec ($ch);
		curl_close ($ch);
		file_put_contents("c.json",$json);
		#$json = file_get_contents("c.json");
		$data = json_decode($json);
		pr($data);
		if ($h > 2) { exit; }
		}


	}

	static public function doMain() {
		top3();
		bottom3();
	}
  
}

