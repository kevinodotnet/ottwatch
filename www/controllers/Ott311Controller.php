<?php

class Ott311Controller {

	static $apiurl = 'http://city-of-ottawa-prod.apigee.net/open311/v2';

	static public function scan() {

	ini_set('memory_limit', '64M');

		$start_date = '2017-01-05T00:00:00Z';
		$end_date =   '2017-01-06T00:00:00Z';

		$url = self::$apiurl."/requests.json?1=1";
	  $url = "$url&start_date=$start_date";
		$url = "$url&end_date=$end_date";

		$url = "https://city-of-ottawa-prod.apigee.net/open311/v2/requests.json?1=1&start_date=2017-03-01T00:00:00Zend_date=2017-03-01T02:00:00Z";
		#$url = "https://city-of-ottawa-dev.apigee.net/open311/v2/requests.json?=1&end_date=2017-01-06T00:00:00Z&start_date=2017-01-05T00:00:00Z";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		$headers = array( 'api_key: '.OttWatchConfig::OTTAPI_KEY);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		#curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		#curl_setopt($ch, CURLOPT_VERBOSE, true);

		#$json = curl_exec ($ch);
		#file_put_contents("c.json",$json);
		$json = file_get_contents("c.json");
		$data = json_decode($json);
		pr($data);
		exit;

		curl_close ($ch);

		#pr($json);

	}

	static public function doMain() {
		top3();
		bottom3();
	}
  
}

