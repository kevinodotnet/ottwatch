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

	static public function scanOpenForUpdates() {
		$rows = getDatabase()->all(" 
			select *
			from sr 
			where 
				status = 'Open' 
			order by 
				scanned,
				requested desc
			limit 10
		");
		foreach ($rows as $r) {
			$sr = self::scanSR($r['sr_id']);
			if (!isset($sr->service_request_id)) {
				print "SR is gone?\n";
				pr($r);
				# mark that we scanned it
				$dbin = array( 'id' => $r['id']);
				getDatabase()->execute(" update sr set scanned = CURRENT_TIMESTAMP where id = :id ",$dbin);
				continue;
			}
			if ($sr->status == 'Closed') {
				$dbin = array(
					'id' => $r['id'],
					'status' => $sr->status,
					'updated' => self::w3DateTime($sr->updated_datetime),
				);
				#print "\n\nClose detected\n\n";
				#pr($r);
				#pr($sr);
				getDatabase()->execute(" update sr set status = :status, updated = :updated, close_detected = CURRENT_TIMESTAMP, scanned = CURRENT_TIMESTAMP where id = :id ",$dbin);
			} else {
				# mark that we scanned it
				$dbin = array( 'id' => $r['id']);
				getDatabase()->execute(" update sr set scanned = CURRENT_TIMESTAMP where id = :id ",$dbin);
			}
		}
	}

	static public function scanSR($srid) {
		$url = self::$apiurl."/requests/$srid.json";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		$headers = array( 'api_key: '.OttWatchConfig::OTTAPI_KEY);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		#curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		#curl_setopt($ch, CURLOPT_VERBOSE, true);

		$json = curl_exec ($ch);
		if ($json == '') {
			print "WARN: no data returned for srid: $srid;\n";
		}
		curl_close ($ch);

		#file_put_contents("c.json",$json);
		#$json = file_get_contents("c.json");
		$data = json_decode($json);
		if (count($data) > 0) {
			return $data[0];
		}
		return null;
	}

	static public function downloadAllCsv() {
		$rows = getDatabase()->all(" select * from sr order by requested ");
		header("Content-disposition: attachment; filename=\"ottawa_311_sr_all.csv\""); 
		$head = $rows[0];
		$first = 1;
		foreach ($head as $k=>$v) {
			if ($first != 1) { print "\t"; }
			print "$k";
			$first = 0;
		}
		print "\n";
		foreach ($rows as $r) {
		$first = 1;
		foreach ($r as $k=>$v) {
			if ($first != 1) { print "\t"; }
			print $r[$k];
			$first = 0;
		}
		print "\n";
		}
	}

	static public function w3DateTime($d) {
		$dt = new DateTime($d);
		return $dt->format('Y-m-d H:i:s');
	}

	static public function showDate($date) {
		top3();

		$start = new DateTime($date);
		$start->setTime(0,0,0);
		$prev = clone $start;
		$prev->sub(new DateInterval("P1D"));
		$end = clone $start;
		$end->add(new DateInterval("P1D"));

		$start = $start->format('Y-m-d');
		$end = $end->format('Y-m-d');
		$prev = $prev->format('Y-m-d');

		$rows = getDatabase()->all(" select * from sr where requested >= :start and requested < :end order by requested ",array( 'start' => $start, 'end' => $end));

		?>


		<div class="row">
		<div class="col-xs-6"><h1><?php print $start; ?></div>
		<div class="col-xs-3 text-center"><a class="btn btn-default" href="/311/date/<?php print $prev; ?>">previous day</a></div>
		<div class="col-xs-3 text-center"><a class="btn btn-default" href="/311/date/<?php print $end; ?>">next day</a></div>
		</div>

		Found: <?php print count($rows); ?>

		<p><i>note: ottwatch isn't re-scanning SRs to detect updates yet; so what you're looking at is just the state of the SR when it was first detected by OttWatch.  Updates are coming soon....</i> </p>

		<table class="table table-bordered table-hover table-condensed">
		<tr>
		<th>SR</th>
		<th>Status</th>
		<th>Ward</th>
		<th>Description/ward</th>
		<th>Requested</th>
		<th>Updated</th>
		</tr>
		<?php
		foreach ($rows as $r) {
			$r['address'] = preg_replace('/ [A-Z].*/','',$r['address']);
			$r['address'] = preg_replace('/WARD /','',$r['address']);
			?>
			<tr>
			<td><a href="/311/sr/<?php print $r['sr_id']; ?>"><?php print $r['sr_id']; ?></a></td>
			<td><?php print $r['status']; ?></td>
			<td><?php print $r['address']; ?></td>
			<td><?php print $r['description']; ?></td>
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
		$row = db_save('sr',$dbin,'sr_id');
		# automatically close SRs if needed; this should be made to be smarter, but whatever
		getDatabase()->execute(" update sr set close_detected = updated where close_detected is null and status = 'Closed' ");
		return $row;
	}

	static public function scanStartEnd($start_date,$end_date) {
		$url = self::$apiurl."/requests.json";
	  $url = "$url?start_date=$start_date";
		$url = "$url&end_date=$end_date";

		#print "$start_date\n$end_date\n";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		$headers = array( 'api_key: '.OttWatchConfig::OTTAPI_KEY);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		#curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		#curl_setopt($ch, CURLOPT_VERBOSE, true);

		$json = curl_exec ($ch);
		if ($json == '') {
			# api is down, or other network error
			print "no data\n";
			return;
		}
		curl_close ($ch);
		#file_put_contents("c.json",$json);
		#$json = file_get_contents("c.json");
		$data = json_decode($json);
		# print "count: ".count($data)."\n";
		if (count($data) > 0) {
			foreach ($data as $sr) {
				#print "{$sr->requested_datetime} {$sr->service_request_id} {$sr->description}\n";
				self::saveSR($sr);
			}
		}
	}

	static public function scanLatest() {

		$now = new DateTime();
		$end_date = $now->format('Y-m-d\TH:i:s-05:00');
		$now->sub(new DateInterval("PT1H"));
		$start_date = $now->format('Y-m-d\TH:i:s-05:00');

		self::scanStartEnd($start_date,$end_date);
	}

	static public function scanOld() {
		$row = getDatabase()->one(" select datediff(curdate(),min(requested)) d, min(requested) r from sr ");
		$ago = $row['d'];
		$ago++;
		self::scan($ago);
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

		$c = getDatabase()->one(" select count(1) c from sr where requested > curdate() ");
		$c = $c['c'];

		$today = new DateTime($date);
		$today->setTime(0,0,0);
		$today = $today->format('Y-m-d');

		?>

		<div class="row">
		<div class="col-sm-6">
		<h1>311 data</h1>
		</div>
		<div class="col-sm-6">
		More data:<br/>
		<ul>
		<li><a href="/311/date/<?php print $today; ?>" >today's SRs</a></li>
		<li><a href="/311/download/all.csv" >CSV download of all data</a></li>
		</ul>
		</div>
		</div>


		<div class="row">
		<div class="col-sm-6">

		<h2>today's requests, by type</h2>
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
		</div>

		<div class="col-sm-6">
		<h2 id="byward">today's requests, by ward</h2>
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

		</div>
		</div>

		<h2 id="byward">Browse by day</h2>
		<?php
		$rows = getDatabase()->all(" select left(requested,10) day, count(1) c from sr group by left(requested,10) ");
		?>
		<table class="table table-bordered table-hover table-condensed">
		<tr>
		<th>Date</th>
		<th>Count</th>
		</tr>
		<?php
		foreach ($rows as $r) {
			?>
			<tr>
			<td>
			<a href="/311/date/<?php print $r['day']; ?>"><?php print $r['day']; ?></a>
			</td>
			<td><?php print $r['c']; ?></td>
			</tr>
			<?php
		}
		?>
		</table>

		<?php
		bottom3();

	}
  
}

