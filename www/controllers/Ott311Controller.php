<?php

class Ott311Controller {

	static $apiurl = 'http://city-of-ottawa-prod.apigee.net/open311/v2';

#             [service_request_id] => 201700462980
#             [status] => Open
#             [status_notes] => 
#             [service_name] => General Road Maintenance
#             [service_code] => 2000164-1
#             [description] => Roads Maintenance / Travelled Surface / Plowing or sanding is overdue
#             [agency_responsible] => 
#             [service_notice] => 
#             [requested_datetime] => 2017-03-15T02:18:37-05:00
#             [updated_datetime] => 
#             [expected_datetime] => 2017-03-22T08:30:00-05:00
#             [address] => WARD 21 RIDEAU-GOULBOURN
#             [address_id] => 
#             [zipcode] => 
#             [lat] => 
#             [long] => 
#             [media_url] => 

	static public function w3DateTime($d) {
		$dt = new DateTime($d);
		return $dt->format('Y-m-d H:i:s');
	}

	static public function showSR($srid) {
		top3();
		?>
		<i>note: ottwatch isn't re-scanning SRs to detect updates yet; so what you're looking at is just the state of the SR when it was first detected by OttWatch.
		Updates are coming soon....</i>
		<?php
		$row = getDatabase()->one(" select * from sr where sr_id = $srid ");
		pr($row);
		bottom3();
	}

	static public function saveSR($sr) {
		$dbin = array(
			'sr_id' => $sr->service_request_id,
			'status' => $sr->status,
			'service_code' => $sr->service_code,
			'description' => $sr->description,
			'requested' => self::w3DateTime($sr->requested_datetime),
			'updated' => self::w3DateTime($sr->updated_datetime),
			'expected' => self::w3DateTime($sr->expected_datetime),
			'address' => $sr->address
		);
		return db_save('sr',$dbin,'sr_id');
	}

	static public function scanStartEnd($start_date,$end_date) {
		$url = self::$apiurl."/requests.json";
	  $url = "$url?start_date=$start_date";
		$url = "$url&end_date=$end_date";


		#self::$apiurl = 'http://city-of-ottawa-dev.apigee.net/open311/v2';
		# $url = "https://city-of-ottawa-prod.apigee.net/open311/v2/requests.json?1=1&start_date=$start_date&end_date=$end_date";
		# $url = "https://city-of-ottawa-dev.apigee.net/open311/v2/requests.json?1=1&start_date=$start_date&end_date=$end_date";
		# $url = "https://city-of-ottawa-dev.apigee.net/open311/v2/requests.json?=1&end_date=2017-01-06T00:00:00Z&start_date=2017-01-05T00:00:00Z";
		# print "$url\n";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		$headers = array( 'api_key: '.OttWatchConfig::OTTAPI_KEY);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		#curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		#curl_setopt($ch, CURLOPT_VERBOSE, true);

		$json = curl_exec ($ch);
		curl_close ($ch);
		#file_put_contents("c.json",$json);
		#$json = file_get_contents("c.json");
		$data = json_decode($json);
		foreach ($data as $sr) {
			#print "{$sr->requested_datetime} {$sr->service_request_id} {$sr->description}\n";
			self::saveSR($sr);
		}
	}

	static public function scanLatest() {

		$now = new DateTime();
		$end_date = $now->format('Y-m-d\TH:i:s-05:00');
		$now->sub(new DateInterval("PT1H"));
		$start_date = $now->format('Y-m-d\TH:i:s-05:00');

		self::scanStartEnd($start_date,$end_date);
	}

	static public function scan($daysAgo) {

		$start = new DateTime();
		$x = $daysAgo;
		$now = $start->sub(new DateInterval("P" . $x . "D"));
		$now->setTime(0,0,0);

		for ($h = 0; $h < 24; $h++) {
			$start_date = $now->format('Y-m-d\TH:i:s-05:00');
			$end = clone $now;
			$end->add(new DateInterval("PT1H"));
			$end_date = $end->format('Y-m-d\TH:i:s-05:00');
			self::scanStartEnd($start_date,$end_date);
			$now->add(new DateInterval("PT1H"));
		}

	}

	static public function doMain() {
		top3();

		$max = getDatabase()->one(" select max(requested) m from sr ");
		$max = $max['m'];

		$c = getDatabase()->one(" select count(1) c from sr where requested > curdate() ");
		$c = $c['c'];

		?>
		<h1>Today in 311 <small>as of: <?php print $max; ?></small></h1>

		jump to: 
		<a href="#byward" class="btn btn-default">by ward</a>
		<a href="#latestSR" class="btn btn-default">latest SRs</a>

		<h2>by type</h2>
		<?php
		$rows = getDatabase()->all(" select description,count(1) c from sr where requested > curdate() group by description order by count(1) desc ");
		?>
		<table class="table table-bordered table-hover table-condensed">
		<tr>
		<th>Count</th>
		<th>%</th>
		<th>Description</th>
		</tr>
		<?php
		foreach ($rows as $r) {
			?>
			<tr>
			<td><?php print $r['c']; ?></td>
			<td><?php print number_format($r['c']/$c*100,1); ?></td>
			<td><?php print $r['description']; ?></td>
			</tr>
			<?php
		}
		?>
		</table>

		<h2 id="byward">by ward</h2>
		<?php
		$rows = getDatabase()->all(" select address,count(1) c from sr where requested > curdate() and address != 'null' group by address order by count(1) desc ");
		?>
		<table class="table table-bordered table-hover table-condensed">
		<tr>
		<th>Count</th>
		<th>%</th>
		<th>Ward</th>
		</tr>
		<?php
		foreach ($rows as $r) {
			?>
			<tr>
			<td><?php print $r['c']; ?></td>
			<td><?php print number_format($r['c']/$c*100,1); ?></td>
			<td><?php print $r['address']; ?></td>
			</tr>
			<?php
		}
		?>
		</table>

		<h2 id="latestSR">Latest SRs</h2>
		<table class="table table-bordered table-hover table-condensed">
		<i>note: ottwatch isn't re-scanning SRs to detect updates yet; so what you're looking at is just the state of the SR when it was first detected by OttWatch.
		Updates are coming soon....</i>
		<tr>
		<th>SR</th>
		<th>Status</th>
		<th>Description/ward</th>
		<th>Requested</th>
		<th>Updated</th>
		</tr>
		<?php
		$rows = getDatabase()->all(" select * from sr order by requested desc limit 50 ");
		foreach ($rows as $r) {
			?>
			<tr>
			<td><a href="/311/sr/<?php print $r['sr_id']; ?>"><?php print $r['sr_id']; ?></a></td>
			<td><?php print $r['status']; ?></td>
			<td><?php print $r['description']; ?><br/>
			<?php print $r['address']; ?></td>
			<td><nobr><?php print $r['requested']; ?></nobr></td>
			<td><nobr><?php print $r['updated']; ?></nobr></td>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
		bottom3();

	}
  
}

