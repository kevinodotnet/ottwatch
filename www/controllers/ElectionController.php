<?php

class ElectionController {

  const year = 2014;
  const prevyear = 2010;

	public static function showDonor($id) {

		$donor = getDatabase()->one(" select * from election_donor where id = :id ",array('id'=>$id));
		if (!$donor['id']) {
			top3();
			print "Donor $id not found";
			bottom3();
			return;
		}

		top3("{$donor['name']} - Election Donations");

		##
		## Find related donors
		##
		$parts = explode(" ",$donor['name']);
		$keeps = array();
		$ignores = array('ontario','corp','canada','inc','ltd','limited','incorporated','development','property','properties');
		$ignores[] = 'Group';
		$ignores[] = 'Management';
		$ignores[] = 'Homes';
		$ignores[] = 'Developments';
		$ignores[] = 'Construction';
		$ignores[] = 'Associates';
		$ignores[] = 'Holdings';
		$ignores[] = 'Ottawa';
		$ignores[] = 'Consultants';
		$ignores[] = 'Professional';
		$ignores[] = 'Consulting';
		$ignores[] = 'Restaurant';
		$ignores[] = 'Investments';
		$ignores[] = 'Association';
		$ignores[] = 'financial';
		$ignores[] = 'Sales';
		$ignores[] = 'International';
		$ignores[] = 'partnership';
		$ignores[] = 'land';
		$ignores[] = 'llp';
		$ignores[] = 'and';
		foreach ($parts as $p) {
			$keep = 1;
			if (strlen($p) == 1) { $keep = 0; }
			if (strlen($p) == 2 && preg_match('/\.$/',$p)) { $keep = 0; }
			foreach ($ignores as $i) {
				if (preg_match("/^$i$/i",$p)) { $keep = 0; }
				if (preg_match("/^$i\.$/i",$p)) { $keep = 0; }
			}
			if ($keep) {
				$keeps[] = $p;
			}
		}
		$sql = " 
			select 
				e.id,
				e.name,
				count(1) c ,
				min(c.year) minyear,
				max(c.year) maxyear
			from election_donor e 
				join candidate_donation d on d.donorid = e.id 
				join candidate_return r on r.id = d.returnid
				join candidate c on c.id = r.candidateid
			where 
				e.id != :id and 
				d.type = 1 and (";
		$first = 1;
		foreach ($keeps as $k) {
			if ($first == 0) {
				$sql .= " or ";
			}
			$sql .= " e.name like '%".mysql_escape_string($k)."%' ";
			$first = 0;
		}
		$sql .= " ) ";
		$sql .= " group by e.id, e.name ";
		$sql .= " order by max(c.year) desc, count(1), e.name ";
		$related = getDatabase()->all($sql,array('id'=>$id));
		##
		## /relatd
		##

		$rows = getDatabase()->all("
			select
				c.id cid,
				c.year,
				c.ward,
				c.last,
				c.first,
				case when c.incumbent = 0 then 'No' when c.incumbent = 1 then 'Yes' else 'Unk' end incumbent,
				case when c.winner = 0 then 'No' when c.winner = 1 then 'Yes' else 'Unk' end winner,
				case when r.supplemental = 0 then 'No' when r.supplemental = 1 then 'Yes' else 'Unk' end supplemental,
				e.name,
				d.id did,
				d.type,
				d.address,
				d.city,
				d.postal,
				d.amount
			from
				election_donor e
				join candidate_donation d on d.donorid = e.id
				join candidate_return r on r.id = d.returnid
				join candidate c on c.id = r.candidateid
			where
				e.id = :id
			order by
				c.year desc,
				c.ward,
				c.last,
				c.first
		",array('id'=>$id));

		?>
		<div class="row">
			<div class="col-sm-8">
				<?php print "<h1>{$donor['name']} - Election Donations</h1>"; ?>
			</div>
			<div class="col-sm-4">
				<b>
				<?php print "Number of donations: ".count($rows); ?>
				</b>
				<br/>
				<a href="/election/listDonations" class="btn btn-primary">Return to Donation Search Page</a>
			</div>
		</div>
		<?php


		?>
    <table class="table table-bordered table-hover table-condensed">
		<tr>
		<th>year</th>
		<th>ward</th>
		<th>amount</th>
		<th>candidate</th>
		<th>incumbent</th>
		<th>winner</th>
		<th>supplemental</th>
		<th>address</th>
		<th>city</th>
		<th>postal</th>
		</tr>
		<?php

		foreach ($rows as $r) {
			print "<tr>";
			print "<td>{$r['year']}</td>";
			print "<td>{$r['ward']}</td>";
			print "<td><a href=\"/election/donation/{$r['did']}\">$".formatMoney($r['amount'],true)."</a></td>";
			print "<td><a href=\"/election/listDonations?candidate[]={$r['cid']}\">{$r['last']}, {$r['first']}</a></td>";
			print "<td>{$r['incumbent']}</td>";
			print "<td>{$r['winner']}</td>";
			print "<td>{$r['supplemental']}</td>";
			print "<td>{$r['address']}</td>";
			print "<td>{$r['city']}</td>";
			print "<td><a href=\"/election/listDonations?postal={$r['postal']}\">{$r['postal']}</a></td>";
			print "</tr>\n";
		}

		?>
		</table>

		<?php
		if (count($related) > 0) {
			?>
				<h2>Possibly Related Corporate/Union Donors</h2>
				There are <?php print count($related); ?> corporate donors with similar names.
				Donations from regular people with similar names are not shown.
				<br/>
				<?php
				print "<ul>";
				foreach ($related as $rel) {
					print "<li><a href=\"/election/donor/{$rel['id']}\">{$rel['name']}</a>, {$rel['c']} donations in years {$rel['minyear']} to {$rel['maxyear']}</li>";
				}
				print "</ul>";
				
		}

		disqus();
		bottom3();
		return;

		//
		// OLD
		//

		$url = "/election/listDonations";
		$rows = getDatabase()->all(" select distinct(id) id from candidate_donation where donorid = :id ",array('id'=>$id));
		$sep = '?';
		foreach ($rows as $r) {
			$url .= "$sep";
			$url .= urlencode("pinid[]");
			$url .= "=";
			$url .= $r['id'];
			$sep = '&';
		}
		header("Location: $url");
		return;
	}

	public static function processDonationScoreboard() {
		top3();
		$sql = "
			select 
				case when name is null then 'Anonymous' else name end Name,
				Entries
			from (
				select 
					p.id,
					p.name,
					count(1) entries
				from 
					candidate_donation d
					left join people p on p.id = d.peopleid
				where
					d.created > '2015-01-01' 
					and d.amount is not null
				group by 
					p.id, 
					p.name
				order by
					count(1) desc
			) t ";
		$rows = getDatabase()->all($sql);
		?>
		<div class="row">
			<div class="col-sm-6">
				<h2>Campaign Donation Data-Entry Scoreboard!</h2>
			</div>
			<div class="col-sm-6">
				<p>As PDFs of the 2014 campaign finance documents become available, awesome people in Ottawa donate their time to <a href="/election/listDonations">digitize them into the OttWatch database</a>.
				Here's the scoreboard! Many thanks to everyone on the list, and those Anonymous people who would like to remain anonymous!</p>
			</div>
		</div>
		<?php
		rowsToTable($rows);
		bottom3();
	}

	public static function raceResults ($electionid,$race) {

		$totalVotes = getDatabase()->one(" 
			select 
				sum(votes) votes
			from election_vote v
			where 
				electionid = :electionid 
				and race = :race 
			",array('electionid'=>$electionid,'race'=>$race));
		$totalVotes = $totalVotes['votes'];

		$rows = getDatabase()->all(" 
			select 
				c.id,
				c.first,
				c.last,
				c.winner,
				c.incumbent,
				sum(v.votes) votes,
				round(sum(v.votes)/$totalVotes*100,1) perc
			from election_vote v
				join candidate c on c.id = v.candidateid
			where 
				c.electionid = :electionid 
				and v.race = :race 
			group by 
				c.id,
				c.first,
				c.last
			order by
				sum(v.votes) desc
			",array('electionid'=>$electionid,'race'=>$race));

		#pr($rows);
		top("Detailed Election Results");
		?>
    <table id="racesummary" class="table table-bordered table-hover table-condensed">
		<tr>
			<th>Candidate</th>
			<th>% Votes</th>
			<th>Votes</th>
		</tr>
		<?php
		foreach ($rows as $r) {
			?>
			<tr>
				<td>
					<?php print "{$r['first']} {$r['last']}"; ?>
					<?php if ($r['winner']) { print "<small>(winner)</small>"; } ?>
					<?php if ($r['incumbent']) { print "<small>(incumbent)</small>"; } ?>
				</td>
				<td><?php print $r['perc']; ?>
				<td><?php print $r['votes']; ?>
			</tr>
			<?php
		}
		?>
		</table>
		<?php

		$rows = getDatabase()->all(" 
			select 
				c.id candidateid,
				c.first,
				c.last,
				c.incumbent,
				c.winner,
				v.ward,
				v.poll,
				v.precinct,
				v.type,
				v.votes,
				md5(concat(v.ward,v.poll,v.precinct,v.type)) md5
			from election_vote v
				join candidate c on c.id = v.candidateid
			where 
				c.electionid = :electionid 
				and v.race = :race 
			order by
				case when v.type like 'A%' or v.type like 'S%' then 1 else 0 end,
				v.type,
				v.race,
				v.ward,
				v.poll,
				v.precinct,
				c.winner desc,
				c.incumbent desc,
				c.last,
				c.first,
				c.id
			",array('electionid'=>$electionid,'race'=>$race));

		# convert to LIST of PRECIENT that has LIST of candidate results

		$prevmd5 = '';
		$voterows = array();
		$candidates = array();
		foreach ($rows as $r) {
			if ($prevmd5 != $r['md5']) {
				if (count($candidates) > 0) {
					$voterows[] = $candidates;
				}
				$candidates = array();
			}
			$candidates[] = $r;
			$prevmd5 = $r['md5'];
		}

		?>
    <table id="pollbypollresults" class="table table-bordered table-hover table-condensed">
		<tr>
		<th style="text-align: center;" rowspan="2">Precinct</th>
		<th style="text-align: center;" colspan="<?php print count($voterows[0]); ?>">Vote Count</th>
		<th style="text-align: center;" colspan="<?php print count($voterows[0]); ?>">Percentage of Vote</th>
		</tr>
		<tr>
			<?php
			foreach ($voterows[0] as $r) {
				print "<th >{$r['first']} {$r['last']}";
				if ($r['winner'] || $r['incumbent']) { print "<br/>"; }
				if ($r['winner']) { print " <small>(winner)</small>"; }
				if ($r['incumbent']) { print " <small>(incumbent)</small>"; }
				print "</th>";
			}
			print "<th>Total</th>";
			foreach ($voterows[0] as $r) {
				print "<th >{$r['first']} {$r['last']}";
				if ($r['winner'] || $r['incumbent']) { print "<br/>"; }
				if ($r['winner']) { print " <small>(winner)</small>"; }
				if ($r['incumbent']) { print " <small>(incumbent)</small>"; }
				print "</th>";
			}
			?>
		</tr>

		<?php
		foreach ($voterows as $row) {
			$total = 0;
			$maxvotes = 0;
			foreach ($row as $r) {
				$total += $r['votes'];
				if ($r['votes'] > $maxvotes) {
					$maxvotes = $r['votes'];
				}
			}
			$winnercount = 0;
			foreach ($row as $r) {
				if ($r['votes'] > 0 && $r['votes'] == $maxvotes) {
					$winnercount ++;
				}
			}

			print "<tr>";
			print "<td>{$row[0]['precinct']}</td>";

			foreach ($row as $r) {
				$style = 'style="text-align: center;" ';
				if ($r['votes'] == $maxvotes) {
						$style = ' style="text-align: center; background: #17E859;" ';
					if ($winnercount > 1) {
						$style = ' style="text-align: center; background: #E2E86B;" ';
					}
					if ($r['votes'] == 0) {
						$style = ' text-align: center; ';
					}
				}
				print "<td $style >{$r['votes']}</td>";
				#print "<td style=\"background: #f0c0f0; text-align: center;\" >".round(100*$r['votes']/$total,1)."%</td>";
			}
			print "<td style=\"background: #e0e0e0; text-align: center; border-left: solid 2px; border-right: solid 2px;\" >$total</td>";
			foreach ($row as $r) {
				$style = 'style="text-align: center;" ';
				if ($r['votes'] == $maxvotes) {
						$style = ' style="text-align: center; background: #17E859;" ';
					if ($winnercount > 1) {
						$style = ' style="text-align: center; background: #E2E86B;" ';
					}
					if ($r['votes'] == 0) {
						$style = ' text-align: center; ';
					}
				}
				#print "<td $style >{$r['votes']}</td>";
				print "<td $style  >".round(100*$r['votes']/$total,1)."%</td>";
			}
			print "</tr>";
		}
		?>
		</table>

		<?php
		bottom();
		return;

		$prevmd5 = '';
		foreach ($rows as $r) {
			if ($prevmd5 != $r['md5']) {
				print "</tr><tr>";
				print "<td>{$r['precinct']}</td>";
			}
			print "<td>{$r['votes']}</td>";
			$prevmd5 = $r['md5'];
		}
		print "</tr>";
		?>

		<table>
		<?php

		#pr($rows);

		bottom();

	}

	public static function showCandidate($id) {
		$row = getDatabase()->one(" select * from candidate where id = :id ",array('id'=>$id));
		if (!isset($row['id'])) {
			top();
			?>
			<h1>Candidate #<?php print $id; ?> not found</h1>
			<?php
			bottom();
			return;
		}

		$title = "{$row['first']} {$row['last']}: {$row['year']} Election Candidate Information";
		top($title);
		print "<h1>$title</h1>";
		print "<a href=\"/election/listDonations?candidate[]={$row['id']}\">List Campaign Donations</a>";
		mapToTable($row);
		bottom();
	}

	public static function candidatesCSV() {
		$rows = getDatabase()->all(" 
			select 
				id ottwatch_id,
				year, ward, first, middle, last, url,
				email, twitter, facebook, nominated, incumbent, phone, gender
			from candidate
			where
				year = ".ElectionController::year."
				and nominated is not null
				and withdrew is null
			order by
				year,ward,last,first,middle
		");

		header("Content-disposition: attachment; filename=\"ottvote_candidates_".ElectionController::year.".csv\""); 

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
		#pr($rows);
	}

	public static function tmp() {
		top();

		if (!LoginController::isLoggedIn()) { 
			print "Not logged in!";
			bottom();
			return;
		}

		$rows = getDatabase()->all(" select * from candidate_donation where address != '' and prov != 'BROKEN' and returnid != 18 and location is null order by rand() ");
		if (count($rows) == 0) {
			print "No coding to do\n";
			bottom();
			return;
		}

		print "<h1>".count($rows)." records to code...</h1>";

		$r = $rows[0];

		$r['addr'] = preg_replace("/'/","''",$r['addr']);
		?>

		<div class="row-fluid">

		<div class="span6">
			<h2>Console</h2>
			<div id="wardmsg">
			Postal code magic coming your way!
			</div>
		</div>

		<div class="span6">
			<h2>Record</h2>
		<?php pr($r); ?>
		</div>


		</div><!-- /row -->

    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
		<script>
      var geocoder = new google.maps.Geocoder();
      var addr = "<?php print "{$r['address']}, {$r['city']}, {$r['prov']}"; ?>";
      geocoder.geocode({address: addr},
        function(results, status) { 
          if (status != 'OK') {
            $('#wardmsg').html('Error mapping address');
            return;
          }
          $('#wardmsg').html('GEO worked');
          lat = results[0].geometry.location.lat();
          lon = results[0].geometry.location.lng();
					googlepostal = '';
					console.log(results);
					results[0].address_components.forEach(function(entry){
						if (entry.types[0] == 'postal_code') {
							googlepostal = entry.long_name;
						}
					});
					$.post( '/election/processDonation', 
						{ 
							ajax: 1, 
							id: <?php print $r['id']; ?>, 
							lat: lat,
							lon: lon
						} , function( data ) {
	            $('#wardmsg').html(data);
							location.reload(); 
					});
        }
      );

			$(document).ready( function() { 
				setTimeout(function() { location.reload(); }, 2000); 
			}); 
		</script>
		<?php
		bottom();
	}

	public static function getReturnPagesDir($year,$filename) {
		$filename = preg_replace('/\.pdf/','',$filename);
		return OttWatchConfig::FILE_DIR."/election/$year/financial_returns/$filename";
	}

	public static function getReturnPages($year,$filename) {
		$dir = self::getReturnPagesDir($year,$filename);
    $d = opendir($dir);
		$pages = array();
    while (($file = readdir($d)) !== false) {
      if (preg_match('/^\./',$file)) { continue; }
      if (!preg_match('/^page-\d+\.png/',$file)) { continue; }
			if (file_exists("$dir/$file.rotated")) { continue; }
      $pages[] = "$dir/$file";
    }
    closedir($d);
		asort($pages);
		$t = array();
		foreach ($pages as $p) {
			$t[] = $p;
		}
		return $t;
	}

  public static function showWardMap($ward) {
    top();
		self::showWardMapPriv($ward,-1,1);
		bottom();
	}

  public static function showWardMapPriv($ward,$height,$showPolls) {

		if ($height <= 0) { $height = 590; }

    ?>
		<center>
    <div id="map_canvas" style="width:90%; height:<?php print $height; ?>px;"></div>
		</center>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
    <script>
    var mapOptions = { center: new google.maps.LatLng(45.420833,-75.59), zoom: 10, mapTypeId: google.maps.MapTypeId.ROADMAP };
    infowindow = new google.maps.InfoWindow({ content: '' });
    map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);

    <?php
    if ($showPolls > 0) {
	    # add polygon poll data too
      $polls = $_GET['polls'];
	    $year = 2010;
      if (!isset($polls)) {
		    $json = file_get_contents(OttWatchConfig::WWW."/api/wards/$ward/polls");
		    $polls = json_decode($json);
		    $polls = get_object_vars($polls);
		    $polls = $polls[$year];
        $polls = array();
      } else {
        # use CSV list of polls, for a subset map
        $polls = explode(',',$polls);
      }
	    $index = 0;
	    foreach ($polls as $p) {
	      $json = file_get_contents(OttWatchConfig::WWW."/api/wards/$ward/polls/$year/$p");
	      $data = json_decode($json);
	      $poly = $data->polygon;
	      $index++;
	      ?>
          <?php print "// $p\n"; ?>
			    var coords<?php print $index; ?> = [
				    <?php
				    foreach ($poly as $latlon) {
				      print "new google.maps.LatLng({$latlon->lat}, {$latlon->lon}), \n"; # 25.774252, -80.190262),
				    }
				    ?>
			    ];
			    polygon<?php print $index; ?> = new google.maps.Polygon({
			      paths: coords<?php print $index; ?>,
			      strokeColor: '#ff0000',
			      fillColor: '#ff0000',
			      fillOpacity: 0.10,
			    });
			    polygon<?php print $index; ?>.setMap(map);

          var marker<?php print $index; ?> = new google.maps.Marker({ 
            position: new google.maps.LatLng(<?php print $data->center->lat; ?>,<?php print $data->center->lon; ?>), 
            map: map
          });
	        google.maps.event.addListener(marker<?php print $index; ?>, 'click', function() {
	          infowindow.setContent( 
              '<p>Poll: <?php print $p; ?><br/>' + 
              '<a target="_blank" href="<?php print OttWatchConfig::WWW; ?>/api/wards/<?php print $ward; ?>/polls/<?php print $year; ?>/<?php print $p; ?>/map/live">Live Map</a><br/>' + 
              '<a target="_blank" href="<?php print OttWatchConfig::WWW; ?>/api/wards/<?php print $ward; ?>/polls/<?php print $year; ?>/<?php print $p; ?>/map/static">Static Map</a><br/>' + 
              '<a target="_blank" href="<?php print OttWatchConfig::WWW; ?>/api/wards/<?php print $ward; ?>/polls/<?php print $year; ?>/<?php print $p; ?>/map/img">PNG Image</a><br/>' + 
              '</p>'
            );
	          infowindow.open(map,marker<?php print $index; ?>);
	        });

	      <?php
	      #if ($index > 5) { break; }
	    }
    }
    ?>

    <?php
    $json = file_get_contents(OttWatchConfig::WWW."/api/wards/$ward?polygon=1");
    $data = json_decode($json);
    $poly = $data->polygon;
    ?>

    var coords = [
	    <?php
	    foreach ($poly as $latlon) {
	      print "new google.maps.LatLng({$latlon->lat}, {$latlon->lon}), \n"; # 25.774252, -80.190262),
	    }
	    ?>
    ];
    polygon = new google.maps.Polygon({
      paths: coords,
      strokeColor: '#ff0000',
      fillColor: '#000000',
      fillOpacity: 0.0,
    });
    polygon.setMap(map);


    // from http://stackoverflow.com/questions/2177055/how-do-i-get-google-maps-to-show-a-whole-polygon
    // TODO: move this to an include, perhaps the one that can also import the script tag for google maps
		google.maps.Polygon.prototype.getBounds = function() {
		    var bounds = new google.maps.LatLngBounds();
		    var paths = this.getPaths();
		    var path;        
		    for (var i = 0; i < paths.getLength(); i++) {
		        path = paths.getAt(i);
		        for (var ii = 0; ii < path.getLength(); ii++) {
		            bounds.extend(path.getAt(ii));
		        }
		    }
		    return bounds;
		}

    map.fitBounds(polygon.getBounds());
    </script>
    <?php

  }

  public static function isRaceOn() {
		return true;
  }

  public static function showRace($race) {
    if ($race == 'mayor') { $race = 0; }

    $wardname = getDatabase()->one(" select ward from electedofficials where wardnum = $race ");
    $wardname = $wardname['ward'];

    if ($race == 0) {
      $title = "Mayoral Race";
    } else {
      $title = "$wardname";
    }

		top3($title);
		print "<h1>$title <small>(<a href=\"/election/\">main election page</a>)</small></h1>\n";

    $summ = getDatabase()->one("
      select sum(votes) votes
      from candidate 
      where ward = :ward and year = :year and nominated is not null 
      ",array('ward'=>$race,'year'=>self::year));
		$totalVotes = $summ['votes'];
    $rows = getDatabase()->all("
      select 
				id,electionid,first,last,votes,round(votes/$totalVotes*100,2) perc,withdrew,winner
      from candidate 
      where ward = :ward and year = :year and nominated is not null 
      order by votes desc, rand() ",array('ward'=>$race,'year'=>self::year));

    ?>
    <h2><?php print self::year; ?> Results</h2>
		<a href="/election/<?php print $rows[0]['electionid']; ?>/race/<?php print $race; ?>/results/">Full Results for this race</a>.
    <table class="table table-bordered table-hover table-condensed">
    <tr>
      <th rowspan="2">Name</th>
			<th class="text-center" colspan="2">Election Result</th>
			<th class="text-center" colspan="4">Campaign Donations</th>
    </tr>
    <tr>
      <th class="text-center">Votes</th>
      <th class="text-center">%</th>
      <th class="text-center">Individuals $100 or less</th>
      <th class="text-center">Over $100</th>
      <th class="text-center">Corporate/Union</th>
      <th class="text-center">Total</th>
    </tr>
		<?php
    foreach ($rows as $r) {
			$style = '';
			if ($r['winner']) {
				$style = 'style="font-weight: bold;"';
			}
			if (isset($r['withdrew'])) { continue; }
			?>
	      <tr>
	        <td>
						<a href="/election/candidate/<?php print $r['id']; ?>"><?php print "<span $style >{$r['first']} {$r['middel']} {$r['last']}</span>"; ?></a>
	        </td>
					<td <?php print $style; ?> >
						<?php print $r['votes']; ?>
					</td>
					<td <?php print $style; ?> >
						<?php print $r['perc']; ?>
					</td>
						<?php
							$donationsSQL = "
						  select
								c.id,
						    c.last,
						    c.first,
						    sum(case when d.type = 0 then d.amount else 0 end) individual,
						    sum(case when d.type = 1 then d.amount else 0 end) corpunion,
						    sum(case when d.type = 2 then d.amount else 0 end) individualSmall,
						    sum(d.amount) total,
						    sum(case when d.type = 0 then d.amount else 0 end)/sum(d.amount)*100 perc_individual,
						    sum(case when d.type = 1 then d.amount else 0 end)/sum(d.amount)*100 perc_corpunion,
						    sum(case when d.type = 2 then d.amount else 0 end)/sum(d.amount)*100 perc_individualSmall
						  from
						    candidate_donation d
						    join candidate_return r on r.id = d.returnid
						    join candidate c on c.id = r.candidateid
						  where
								c.id = {$r['id']} ";
						$d = getDatabase()->one($donationsSQL);
						print "<td>$".formatMoney($d['individualSmall'],true)." (".formatPercent($d['perc_individualSmall']).")</td>";
						print "<td>$".formatMoney($d['individual'],true)." (".formatPercent($d['perc_individual']).")</td>";
						print "<td>$".formatMoney($d['corpunion'],true)." (".formatPercent($d['perc_corpunion']).")</td>";
						print "<td><a href=\"/election/listDonations?candidate[]=".$r['id']."\">$".formatMoney($d['total'],true)."</a></td>";
						// pr($row);
						?>
	      </tr>
			<?php
		}
		?>
		</table>

		<?php if (false) { ?>
    <h2>Candidates</h2>
    <?php
    if (count($rows) == 0) {
      ?>
      <i>No registered candidates yet.</i>
      <?php
    } else {
	    ?>
	    <table class="table table-bordered table-hover table-condensed">
	    <tr>
	      <th>Name</th>
	      <th>Contact</th>
	      <th>Registered</th>
	    </tr>
	    <?php
	    foreach ($rows as $r) {
				if (isset($r['withdrew'])) {
					continue;
				}
	      ?>
	      <tr>
	        <td>
	          <?php print "<span $style >{$r['first']} {$r['middel']} {$r['last']}</span>"; ?>
	          <?php if ($r['incumbent'] == TRUE) { /*print "*";*/ } ?>
	        </td>
	        <td>
					<?php if ($r['url'] != '') { ?>
	        <a target="_blank" href="http://<?php print $r['url']; ?>"><?php print $r['url']; ?></a><br/>
					<?php } ?>
					<?php if ($r['email'] != '') { ?>
	        <a target="_blank" href="mailto:<?php print $r['email']; ?>?Subject=Election 2014"><?php print $r['email']; ?></a><br/>
					<?php } ?>
					<?php if ($r['phone'] != '') { ?>
					<?php print $r['phone']; ?><br/>
					<?php } ?>
          <?php if (false && $r['twitter'] != '') { ?>
          <a href="https://twitter.com/<?php print $r['twitter']; ?>" class="twitter-follow-button" data-show-count="false" data-lang="en"><?php print $r['twitter']; ?></a>
          <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
          <?php } ?>
          <?php if (false && $r['facebook'] != '') { ?>
					<div class="fb-like" data-href="<?php print $r['facebook']; ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
          <?php } ?>
          <?php if ($r['twitter'] != '') { ?>
					<a target="_blank" href="http://twitter.com/<?php print $r['twitter']; ?>"><i class="fa fa-share"></i>@<?php print $r['twitter']; ?></a><br/>
          <?php } ?>
          <?php if ($r['facebook'] != '') { ?>
					<a target="_blank" href="<?php print $r['facebook']; ?>"><i class="fa fa-share"></i>Facebook</a>
          <?php } ?>
	        </td>
	        <td>
	        <nobr><?php print substr($r['nominated'],0,10); ?></nobr>
	        </td>
	      </tr>
	      <?php
	    }
	    foreach ($rows as $r) {
				if (!isset($r['withdrew'])) {
					continue;
				}
	      ?>
	      <tr>
	        <td>
						<span style="text-decoration: line-through;">
	          <?php print "{$r['first']} {$r['middle']} {$r['last']}"; ?>
						</span>
	        </td>
	        <td>
	        </td>
	        <td>
					<span style="text-decoration: line-through;"><?php print substr($r['nominated'],0,10); ?></span><br/>
					Withdrew on or before:<br/><?php print substr($r['withdrew'],0,10); ?>
	        </td>
	      </tr>
	      <?php
	    }
    }
    ?>
    </table>
		<?php
		} // if false
		?>

    <?php
    $incumbent = getDatabase()->one("select * from candidate where ward = :ward and year = :year and incumbent = 1 ",array('ward'=>$race,'year'=>self::year));
    $prev = getDatabase()->all("
			select * 
			from candidate c
				left join candidate_return r on r.candidateid = c.id
			where 
				ward = :ward 
				and year = :prevyear 
				and first = :first 
				and last = :last
			",array('ward'=>$race,'prevyear'=>self::prevyear,
			'first'=>$incumbent['first'],
			'last'=>$incumbent['last']
			));
    ?>

		<?php if (false) { ?>
    <h2>Questions to Candidates</h2>
		<?php
		$qs = getDatabase()->all("
			select 
				q.*
			from
				election_question eq
				join question q on q.id = eq.questionid
			where
				eq.ward = $race 
				and q.published = 1
		");
		if (count($qs) == 0) {
			?>
			Nobody has posed a question to the candidates in this race yet. You should be the first!<br/>
			<?php
		} else {
			print count($qs)." question(s) have been put to the candidates:<br/><br/>";
			print "<ul>";
			foreach ($qs as $q) {
				?>
				<li><a href="/election/question/<?php print $q['id']; ?>/"><?php print htmlentities($q['title']); ?></a></li>
				<?php
			}
			print "</ul>";
		}
		?>
		<br/>
		<!--
		<a href="/election/question/add?race=<?php print $race; ?>">
		<i class="fa fa-list fa-4" style="font-size: 125%;"></i>
		Ask your own question!
		</a>
		-->
		<?php } ?>

		<?php if (false) { ?>
    <h2>Incumbent</h2>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <tr>
      <th>Name</th>
      <td><?php print "{$incumbent['first']} {$incumbent['last']}"; ?></td>
    </tr>
    <tr>
      <th>Status</th>
      <td>
		    <?php
		    if ($incumbent['nominated'] != '') {
          print "Registered as candidate on ". substr($incumbent['nominated'],0,10);
			    if ($incumbent['withdrew'] != '') {
	          print " and subsequently withdrew on ". substr($incumbent['withdrew'],0,10);
					}
		    } else {
          if (self::isRaceOn()) {
	          if ($incumbent['retiring'] == 1) {
							print "{$incumbent['first']} is retiring from this position.";
						} else {
		          print "Has not (yet) registered as a candidate.";
		          if (false && $incumbent['twitter'] != '') {
		            ?>
		            Is the incumbent running again? Ask them with this tweet button:
		            <a href="https://twitter.com/share" class="twitter-share-button" 
		              data-via="ottwatch"
		              data-text=".@<?php print $incumbent['twitter']; ?> are you running again? When will you be officially registered?"
									data-hashtags="ottvote"
		              data-lang="en"
		              >.@<?php print $incumbent['twitter']; ?> are you running again? When will you be officially registered?</a>
		            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		            <?php
							}
	          }
          } else {
            ?>
            <p>
            Will <?php print $incumbent['first'] ?> run again? 
            Nominations open on January 2nd - check back then!
            </p>
            <?php
          }
		    }
		    ?>
      </td>
    </tr>
		<!--
    <tr>
      <th>Donations</th>
      <td>
      <a href="/election/listDonations?candidate[]=<?php print $incumbent['t']; ?>">View past donations</a>
      </td>
    </tr>
		-->
    <tr>
      <th>Record</th>
      <td>
      <a href="/meetings/votes/member/<?php print substr($incumbent['first'],0,1).'. '.$incumbent['last'] ?>">All votes by <?php print $incumbent['first'] ?></a> (since mid-2012)
      </td>
    </tr>
    <tr>
      <th><nobr>Lobbying</nobr></th>
			<td>
			<?php
			$lastfirst = "{$incumbent['last']}, {$incumbent['first']}";
			$thelobbiedurl = "/lobbying/thelobbied/$lastfirst";
			$lobbycount = getDatabase()->one(" select count(1) c from lobbying where lobbiednorm = '$lastfirst' ");
			?>
			<a href="<?php print $thelobbiedurl; ?>"><?php print $incumbent['first']; ?> has been lobbied <?php print $lobbycount['c']; ?> times</a>
			</td>
		</tr>
    <tr>
      <th><nobr>Financial Return(s)</nobr></th>
      <td>
			<?php 
			if (count($prev) == 0) {
				?>
				Not available?
				<?php 
			} else { 
				foreach ($prev as $p) {
				?>
				<a target="_blank" href="http://documents.ottawa.ca/sites/documents.ottawa.ca/files/documents/<?php print $p['filename']; ?>"><?php print $p['year']; ?> - <?php print $p['filename']; ?></a><br/>
				<?php 
				}
			} 
			?>
      </td>
    </tr>
    </table>
		<?php 
		} // incumbent 
		?>

    <?php 
    if ($race > 0) {
      ?>
	    <h2>Map <small>(<a href="/election/ward/<?php print $race; ?>/map">fullsize</a>)</small></h2>
	    <?php
			self::showWardMapPriv($race,200,0);
    }
    ?>

    <?php
    bottom3();
  }

  public static function showMain() {
    top("Election Dashboard");
    ?>
    <div class="row-fluid">
    <div class="span4">
    <h1>Election <?php print self::year; ?></h1>
    <p class="lead">
    <b>October 27</b> is the day you vote.<br/>
    <b>Everyday</b> is a good day to be involved.
    </p>
    </div>
    <div class="span4">
    <p class="lead" style="text-align: center;">
    Find your ward: 
    </p>
		<?php 
		ApiController::widgetFindWardInner();
		?>
    </div><!-- findward -->
    <div class="span4">
		  <div style="background: #08c; color: #ffffff; padding: 10px; font-size: 125%; border-radius: 4px;">
		  <center>
		  <a href="/election/listDonations" style="color: #ffffff;">
		  <i class="fa fa-search fa-4" style="font-size: 125%;"></i>
			Search Donations
		  </a>
		  </center>
		  </div>

		  <div style="background: #08c; color: #ffffff; padding: 10px; font-size: 125%; border-radius: 4px; margin-top: 5px;">
		  <center>
		  <a href="/election/candidates.csv" style="color: #ffffff;">
		  <i class="fa fa-download fa-4" style="font-size: 125%;"></i>
			Download Candidate Info (CSV)
		  </a>
		  </center>
		  </div>

		  <div style="background: #08c; color: #ffffff; padding: 10px; font-size: 125%; border-radius: 4px; margin-top: 5px;">
		  <center>
		  <a href="/election/question/list" style="color: #ffffff;">
		  <i class="fa fa-list fa-4" style="font-size: 125%;"></i>
			Questions to the Candidates
		  </a>
		  </center>
		  </div>

    </div>
    </div>

    <?php
    $wards = getDatabase()->all(" select distinct(wardnum) wardnum from electedofficials where wardnum is not null and wardnum != '' order by ward, wardnum + 0 ");
    $count = 0;
    array_unshift($wards,array('wardnum'=>0));
    $count = 0;
    foreach ($wards as $ward) {

      $mod = $count++ % 4;
      if ($mod == 0) {
        ?>
        <div class="row-fluid">
        <?php
      }

      if ($ward['wardnum'] == 0) {
        # special case
        $wardInfo = array('ward'=>'Mayor');
        $raceLink .= "/mayor/";
        $raceLink = OttWatchConfig::WWW . "/election/mayor/";
      } else {
        $wardInfo = getApi()->invoke('/api/wards/'.$ward['wardnum']);
        $raceLink = OttWatchConfig::WWW . "/election/ward/{$ward['wardnum']}";
      }

      $rows = getDatabase()->all("select * from candidate where ward = :ward and year = :year and nominated is not null order by ward,withdrew,rand()",array('ward'=>$ward['wardnum'],'year'=>self::year));
      $countNomed = getDatabase()->one("select count(1) c from candidate where ward = :ward and year = :year and nominated is not null and withdrew is null ",array('ward'=>$ward['wardnum'],'year'=>self::year));
      $countNomed = $countNomed['c'];
      ?>
      <div class="span3">
      <h4><a href="<?php print $raceLink; ?>"><?php print "{$wardInfo['ward']}"; if (count($rows) > 0) { print ' <small>('.$countNomed.' candidates)</small>'; } ?></a></h4>
      <?php
      if (count($rows) == 0) {
        print "<i style=\"color: #c0c0c0;\">No Candidates Registered Yet</i>\n";
      }
      foreach ($rows as $row) {
				$style = '';
				if (isset($row['withdrew'])) {
					$style = ' style="text-decoration: line-through;" ';
				}
				# print "({$row['id']}) ";
        print "<span $style>";
				if (isset($row['url']) && !isset($row['withdrew'])) {
					#print "<a target=\"_blank\" href=\"http://{$row['url']}\">";
					print "<a target=\"_blank\" href=\"$raceLink\">";
				}
				print "{$row['first']} {$row['last']}";
				if (isset($row['url']) && !isset($row['withdrew'])) {
					print "</a>";
				}
				print "</span>";
        #if ($row['incumbent']) { print " *"; }
        print "<br/>\n";
      }
      ?>
      <div style=""><a href="<?php print $raceLink; ?>">(full ward <?php print $wardInfo['wardnum']; ?> info)</a></div>
      </div>
      <?php
      if ($mod == 3) {
        ?>
        </div>
        <?php
      }
    }

    bottom();
  }

	public static function showTools() {
		top();
		?>
		<h4>Corrections or requests for some other tool?</h4>
		email 'em to ottwatch@ottwatch.ca
		<h4>Email addresses: mayor and councillor candidates</h4>
		<?php
		$values = array();
		$rows = getDatabase()->all(" select email from candidate where year = " . year . " and nominated is not null and (email is not null and email != '') order by lower(email) ");
		foreach ($rows as $r) { $values[] = $r['email']; }
		print '<a target="_blank" href="mailto:'.implode(",",$values).'?Subject=Campaign%20question">mailto</a>: ';
		print implode(", ",$values);
		?>
		<h4>Email/Twitter: by race</h4>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
		<?php
		$races = getDatabase()->all(" select distinct(ward) race from candidate where year = ".year." order by ward");
		foreach ($races as $race) {
			$ward = $race['race'];
			if ($race['race'] == 0) {
				$wardname = "Mayor";
			} else {
		    $wardname = getDatabase()->one(" select ward from electedofficials where wardnum = {$race['race']} ");
		    $wardname = $wardname['ward'];
			}
			print "<tr>";
			print "<td>$ward</td><td>$wardname</td>";
			$values = array();
			$rows = getDatabase()->all(" select email from candidate where ward = $ward and year = " . year . " and nominated is not null and (email is not null and email != '') order by lower(email) ");
			foreach ($rows as $r) { $values[] = $r['email']; }
			print "<td>";
			if (count($values) > 0) {
				print '<a target="_blank" href="mailto:'.implode(",",$values).'?Subject='.$wardname.'%20campaign%20question">mailto</a>: ';
				print implode(", ",$values);
			}
			print "</td>";
			$twitters = array();
			$rows = getDatabase()->all(" select twitter from candidate where ward = $ward and year = " . year . " and nominated is not null and (twitter is not null and twitter != '') order by lower(twitter) ");
			foreach ($rows as $r) { $twitters[] = $r['twitter']; }
			print "<td>";
			if (count($twitters) > 0) {
				print "@".implode(" @",$twitters);
			}
			print "</td>";
			print "</tr>";
			
		}
		?>
		</table>
		<h4>Got Web?</h4>
		<?php
		$rows = getDatabase()->all(" select first,last,url from candidate where year = " . year . " and nominated is not null and (url is not null and url != '') order by lower(url) ");
		foreach ($rows as $r) { 
			print "<a href=\"{$r['url']}\">{$r['first']} {$r['last']} --- {$r['url']}</a><br/>";
		}
		?>
		<h4>Got Follow? One set of twitter follow buttons to rule them all</h4>
		<?php
		$rows = getDatabase()->all(" select first,last,twitter from candidate where year = " . year . " and nominated is not null and (twitter is not null and twitter != '') order by lower(twitter) ");
		foreach ($rows as $r) { 
			print "<a href=\"{$r['twitter']}\">{$r['first']} {$r['last']}</a>: ";
			?>
      <a href="https://twitter.com/<?php print $r['twitter']; ?>" class="twitter-follow-button" data-show-count="false" data-lang="en"><?php print $r['twitter']; ?></a><br/>
			<?php
		}
		?>
		<h4>Got Facebook? One set of like buttons to rule them all</h4>
		<?php
		$rows = getDatabase()->all(" select first,last,facebook from candidate where year = " . year . " and nominated is not null and (facebook is not null and facebook != '') order by lower(facebook) ");
		foreach ($rows as $r) { 
			print "<a href=\"{$r['facebook']}\">{$r['first']} {$r['last']}</a>: ";
			?>
			<div class="fb-like" data-href="<?php print $r['facebook']; ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div><br/>
			<?php
		}
		?>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		<?php
		bottom();
	}

	public static function processReturn ($id) {

		$page = $_GET['page'];
		$png = $_GET['png'];
		$rotate = $_GET['rotate'];

		if ($png == '' && !LoginController::blockUnlessLoggedIn()) { 
			// allow PNG=1 to fall through for public access to images;
			// should move the whole image thing to a dedicated GET path.
			return;
		}

		if ($id == '') {
			top();
			#
			# Display list of returns...
				# case when p.id is null then 0 else 1 end desc,
				# left join ( select id from candidate where last in (select last from candidate where year = 2014) ) p on p.id = c.id
			#
			$rows = getDatabase()->all(" 
			select 
				r.done, r.id retid,c.year,r.filename,d.donations,c.* 
			from 
				candidate_return r 
				join candidate c on c.id = r.candidateid 
				left join ( select returnid, count(1) donations from candidate_donation group by returnid ) d on d.returnid = r.id
			where
				c.year in (2014)
			order by 
				case when r.done is null or r.done = 0 then 0 else 1 end,
				case when filename is null then 1 else 0 end,
				c.winner desc,
				d.donations,
				c.ward,
				c.year desc,
				rand()
			");
			$returns = array();
			foreach ($rows as $r) {
				$dir = self::getReturnPagesDir($r['year'],$r['filename']);
#				if (!file_exists($dir)) { continue; }
				$returns[] = $r;
			}
			?>
			<h1>Returns to process: <?php print count($returns); ?></h1>
	    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
			<?php
			foreach ($returns as $r) {
				if ($r['filename'] == null) {
					$r['filename'] = 'return not avail';
				}
				?>
				<tr>
				<td><?php print $r['year']; ?></td>
				<td><?php print $r['ward']; ?></td>
				<td><?php print $r['last']; ?></td>
				<td><?php print $r['first']; ?></td>
				<td><a href="/election/processReturn/<?php print $r['retid']; ?>?page=0"><?php print $r['filename']; ?></a></td>
				<td><?
					$sql = " select count(1) c ,sum(case when amount is not null and amount != '' then 1 else 0 end ) filled from candidate_donation d where returnid = {$r['retid']} group by returnid ";
					$c = getDatabase()->one($sql);
					if (isset($c['c'])) {
						$style = "";
						if ($c['filled'] < $c['c']) {
							$style = " style=\"color: #f00;\" ";
						}
						print "<a $style href=\"/election/processDonation/?returnid={$r['retid']}\">{$c['filled']} of {$c['c']}</a>";
					} else {
						print "-";
					}
				?>
				</td>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
			bottom();
			return;
		}

		# load data about the return
		$ret = getDatabase()->one(" select c.*,r.filename from candidate_return r join candidate c on c.id = r.candidateid where r.id = $id ");
		$pages = self::getReturnPages($ret['year'],$ret['filename']);

    if ($_GET['saveA'] == 1) {
			# click in a <canvass> denoting location of a campaign donation
      $values = array();
			$values['returnid'] = $id;
      $values['x'] = $_GET['x'];
      $values['y'] = $_GET['y'];
      $values['page'] = $page;
#			if (LoginController::isLoggedIn()) {
#	      $values['peopleid'] = getSession()->get("user_id");
#			}
      $id = db_insert('candidate_donation',$values);
			return;
		}

		if ($page == '' || !preg_match('/^\d+$/',$page)) {
			top();
			#
			# List pages in this return.
			#
			print "<h1>Select a page</h1>";
			foreach ($pages as $k => $v) {
				print "<a href=\"?page=$k\">page-$k</a> ";
			}
			bottom();
			return;
		}

    $dots = getDatabase()->all(" select * from candidate_donation where returnid = $id and page = $page ");
		$pagefile = $pages[$page];

		if ($rotate == 1) {
			# rotation requested, so do that then continue;
			$cmd = MfippaController::CONVERT . " '$pagefile' -rotate 90 '$pagefile.rotated' ";
			system($cmd);
			# redirect to non rotate=1 GET request so the hard refresh can be done.
			header("Location: ?page=$page");
			return;
		}

		if (file_exists("$pagefile.rotated")) {
			$pagefile = "$pagefile.rotated";
		}

    $size = getimagesize($pagefile);

		if ($png != '') {
			#
			# return PNG data for page
			#
      $data = file_get_contents($pagefile);
      header('Content-Type: image/png');
			$expires = 60*60*24*14;
			header("Pragma: public");
			header("Cache-Control: maxage=".$expires);
			header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
      print $data;
      return;
		}

		#
		# Show page on the canvass, and accept "clicks" of individual lines
		#

		top();
		print "<center>";
		print "<a href=\"?page=".($page-1)."\">PREV</a>";
		print " | <a href=\"?rotate=1&page=".($page)."\">ROTATE</a> ";
		if (isset($pages[($page+1)])) {
		print " | <a href=\"?page=".($page+1)."\">NEXT</a> ";
		}
		print "<br/>";
    $imgW = $size[0];
    $imgH = $size[1];
		?>
    <canvas id="canvas" width="<?php print $imgW; ?>" height="<?php print $imgH; ?>" style="border: solid 1px #c0c0c0;">
    </canvas><br/>
      <script>
	      var canvas = document.getElementById('canvas');
	      var context = canvas.getContext('2d');

	      var imageObj = new Image();
	      imageObj.onload = function() {
	        context.drawImage(imageObj,0,0);
          <?php
          foreach ($dots as $d) {
            ?>
		        context.beginPath();
		        context.arc(<?php print $d['x']; ?>, <?php print $d['y']; ?>, 5, 0, Math.PI*2, true); 
		        context.closePath();
		        context.fill();
            <?php
          }
          ?>
	      };
	      imageObj.src = '<?php print "?png=1&page=$page&rand=".rand(0,20000); ?>';
        canvas.addEventListener('click', function(event) { 
          c = document.getElementById('canvas');
          x = event.pageX - c.offsetLeft;
          y = event.pageY - c.offsetTop;

		        context.beginPath();
            context.fillStyle = '#f00';
            context.strokeStyle = '#f00';
		        context.arc(x, y, 5, 0, Math.PI*2, true); 
		        context.closePath();
		        context.fill();

          url = '?saveA=1&x=' + x + '&y=' + y + '&page=<?php print $page; ?>';
          $.get( url );
        }, false);

      </script>
		<?php
		print "<a href=\"?page=".($page-1)."\">PREV</a>";
		if (isset($pages[($page+1)])) {
		print " | <a href=\"?page=".($page+1)."\">NEXT</a> ";
		}
		print "</center>";
		bottom();
	}

	public static function processDonation() {
		top3("Process a Campaign Donation");

		$id = '';
		if (LoginController::isLoggedIn()) {
			$id = $_GET['id'];
		}

		$done = getDatabase()->one(" select count(1) c from candidate_donation where created > '2015-01-01' and amount is not null ");
		$done = $done['c'];
		$total = getDatabase()->one(" select count(1) c from candidate_donation where created > '2015-01-01' ");
		$total = $total['c'];

		$remaining = getDatabase()->one(" select count(1) c from candidate_donation where created > '2015-01-01' and amount is null ");
		if ($id == '' && $remaining['c'] == 0) {
			?>
			<center>
			<h1>All Done!</h1>
			<p class="lead">All donation records have been processed! More might get scanned in though - so check back again!</p>
			<p class="lead"><a href="/election/listDonations">Browse them here!</a></p>
			</center>
			<?php
			bottom3();
			return;
		}

		$donePerc = sprintf('%0.2f',$done/$total*100);

		$sql = "select timestampdiff(MINUTE,max(updated),now()) minutes, max(updated) latest,count(1) count from candidate_donation "; # where timediff(now(),updated) < '02:00:00' ";
		$stats = getDatabase()->one($sql);

			?>
			<div class="row" style="margin-bottom: 10px;">
				<div class="col-sm-4">
				Please type in the details of this donation record.
				</div>
				<div class="col-sm-4 text-center">
				We've finished data entry on <?php print $done; ?> of <?php print $total; ?> (<?php print $donePerc; ?>%) donations.<br/>
				</div>
				<div class="col-sm-4 text-right">
				It's been <b><span style="color: #ff0000;"><?php print $stats['minutes']; ?> minutes</span></b> since the last data-entry.
				</div>
			</div>
			<?php

		# select a random unprocessed donation, along with the X/Y of the next donation on the same
		# page, if any, for bounding box purposes.
		$returnWhere = "";
		if (preg_match('/^\d+$/',$_GET['returnid'])) {
			$returnWhere = " and returnid = {$_GET['returnid']} ";
		}
		if ($id != '') {
			$row = getDatabase()->one(" 
				select
					d.*
				from 
					candidate_donation d
				where 
					id = :id
			",array('id'=>$id));
		} else {
			$row = getDatabase()->one(" 
				select
					d.*
				from 
					candidate_donation d
				where 
					d.amount is null
					$returnWhere
				order by rand()
				limit 1
			");
		}

		if (!isset($row['id'])) {
			# should not happen
			?>
			Um, looks like you're done?
			<a href="/election/processDonation/">Maybe click here to refresh and double-check.</a>
			<?php
			bottom3();
			return;
		}

		$next = getDatabase()->one(" select min(y) y from candidate_donation where returnid = {$row['returnid']} and page = {$row['page']} and y > {$row['y']} ");

		$ret = getDatabase()->one(" select c.*,r.filename from candidate_return r join candidate c on c.id = r.candidateid where r.id = {$row['returnid']} ");
		$pages = self::getReturnPages($ret['year'],$ret['filename']);

		$page = $row['page'];
		$pagefile = $pages[$page];
    $size = getimagesize($pagefile);
    $imgW = $size[0];
		$padding = 100;
		if (isset($next['y'])) {
			$imgH = $next['y']-$row['y']+$padding;
		} else {
	    $imgH = 200;
		}

		$next = $row['id']+1;
		$prev = $row['id']-1;
		if (false) { ?> <a href="?id=<?php print $prev; ?>">PREV</a> | <a href="?id=<?php print $next; ?>">NEXT</a><br/> <?php }
		?>

		<center>
    <canvas id="canvas" width="<?php print $imgW; ?>" height="<?php print $imgH; ?>" style="border: solid 1px #c0c0c0; margin-bottom: 10px;">
    </canvas>
		</center>

    <script>
		var canvas = document.getElementById('canvas');
		var context = canvas.getContext('2d');
		var imageObj = new Image();
		context.fillStyle = "blue";
		context.font = "bold 16px Verdana";
	  context.fillText("... loading donation image ... could take a few seconds ... chill!", 20,<?php print $imgH/2; ?>);
		imageObj.onload = function() {
			context.drawImage(imageObj,-<?php print $row['x']; ?>+50,-<?php print $row['y']-($padding/2); ?>);
		        context.beginPath();
		        context.moveTo(0,<?php print ($padding/2); ?>);
		        context.lineTo(40,<?php print ($padding/2); ?>);
						context.stroke();
		        context.closePath();
		        context.beginPath();
		        context.arc(40,<?php print ($padding/2); ?>, 5, 0, Math.PI*2, true); 
		        context.closePath();
		        context.fill();
		};
		imageObj.src = '/election/processReturn/<?php print "{$row['returnid']}?png=1&page=$page"; ?>';
		</script>

		<div style="text-align: center; margin-bottom: 10px;"><b>Look for the blue dot - the record you should copy is below it (or right at it) but never above it.</b></div>

		<form method="post" action="/election/processDonation/" class="form-horizontal">
		<input type="hidden" name="id" value="<?php print $row['id']; ?>"/>
		<input type="hidden" name="returnid" value="<?php print $_GET['returnid']; ?>"/>

		<div class="form-group">
			<label class="col-sm-1 control-label" for="name">Donor</label>
			<div class="col-sm-5">
				<input class="form-control" id="name" value="<?php print $row['name']; ?>" type="text" placeholder="name" name="name" autofocus="1"/>
			</div>
			<div class="col-sm-6 processDonation-help">
				If a corporation or union is shown, put that in the <b>NAME</b> field and ignore any personal names shown.
				For people, please do "Last, First" if you can. Some of the records are shown as "First Last". No biggie either way.
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-1 control-label" for="address">Address</label>
			<div class="col-sm-5">
				<input id="address" class="form-control typeahead" autocomplete="off" value="<?php print $row['address']; ?>" type="text" placeholder="address" name="address" />
			</div>
			<div class="col-sm-6 processDonation-help">
				Just street number, name and apt/unit/PO box.
				Convert to "345 Example Street, Apt 34" format if possible.
			</div>
		</div>

		<?php if ($row['city'] == '') { $row['city'] = 'Ottawa'; } ?>
		<div class="form-group">
			<label class="col-sm-1 control-label" for="city">City</label>
			<div class="col-sm-5">
				<input id="city" class="form-control" value="<?php print $row['city']; ?>" type="text" placeholder="city" name="city" />
			</div>
			<div class="col-sm-6 processDonation-help">
				Leave as Ottawa if it's "Kanata", "Orleans", etc. Only change if it's outside the amalgamated city.
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-1 control-label" for="postal">Postal</label>
			<div class="col-sm-5">
			<input id="postal" class="form-control typeaheadpostal" autocomplete="off" value="<?php print $row['postal']; ?>" type="text" placeholder="postal" name="postal" />
			</div>
			<div class="col-sm-6 processDonation-help">
				Lowercase and no-space is fine, easier to type. ex: k1c3e5
			</div>
		</div>

		<script>
		$('input.typeahead').typeahead({
			ajax: {
				triggerLength: 3,
				url: '/api/typeahead/address',
				displayField: 'address'
			}
		});
		$('input.typeaheadpostal').typeahead({
			ajax: {
				triggerLength: 3,
				url: '/api/typeahead/postal',
				displayField: 'postal',
				preDispatch: function(query) {
					addr = $('#address').val();
					return {
						search: addr
					}
				}
			}
		});
		</script>

		<div class="form-group">
			<label class="col-sm-1 control-label" for="amount">Amount</label>
			<div class="col-sm-5">
			<input id="amount" class="form-control" value="<?php print $row['amount']; ?>" type="text" placeholder="$" name="amount" />
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-5 col-sm-offset-1">
				<input class="btn btn-large btn-success" type="submit" value="Save" />
				&nbsp;
				&nbsp;
				&nbsp;
				&nbsp;
				<input name="report" class="btn btn-large btn-danger" type="submit" value="(Unreadable)" />
			</div>
				<?php
				if (LoginController::isLoggedIn()) {
					?>
					<div class="col-sm-6 processDonation-help" style="font-size: 150%">

					<?php
					$rows = getDatabase()->one("
						select 
							count(1) c 
						from candidate_donation 
						where 
							peopleid = ".getSession()->get("user_id")."
							and returnid in (
								select id from candidate_return where candidateid in ( 
									select id from candidate where year = 2014 
								)
							)
					");
					print getSession()->get("user_name");
					print ", you've processed {$rows['c']} records!";

					?>
					<a href="/election/processDonation/scoreboard">View the Scoreboard!</a>
					</div>
					<?php
				} else {
					?>
					<div class="col-sm-2 processDonation-help">
					<a class="btn btn-primary" href="<?php print LoginController::getLoginUrl(); ?>">Twitter/Facebook Login</a>
					</div>
					<div class="col-sm-4">
					<p>
					You are doing data-entry anonymously, which is totally cool. But if you log in, you can compete for bragging rights for doing data-entry!<br/>
					<b>NOTE: Your twitter/facebook name will be made public on a scoreboard. If that's a problem, stay anonymous.</b>.
					</p>
					<p>
					<a href="/election/processDonation/scoreboard">View the Scoreboard!</a>
					<p>
					</div>
					<?php
				}
				?>
		</div>

		</form>
		<?php
		bottom3();
	}

	public static function processDonationSave() {
		if (isset($_POST['report'])) {
			# the "is broken" submit button was pressed, 
			unset($_POST['report']);
			# mark the broken-ness by hacking on province.
			$_POST['prov'] = 'BROKEN';
		}

		$ajax = 0;
		if (isset($_POST['ajax'])) {
			$ajax = 1;
			unset($_POST['ajax']);
		}

		foreach ($_POST as $k => $v) {
			if ($k == 'postal') {
				$_POST[$k] = strtoupper(preg_replace('/ /','',$_POST[$k]));
			}
			if ($v == '') {
				unset($_POST[$k]);
			}
		}

		$geo = 0;
		if (isset($_POST['lat'])) {
			$geo = 1;
			$lat = $_POST['lat'];
			$lon = $_POST['lon'];
			unset($_POST['lat']);
			unset($_POST['lon']);
		}

		# do not allow mutation of the FK
		$returnid = $_POST['returnid'];
		unset($_POST['returnid']);
		$_POST['updated'] = date('Y-m-d H:i:s');

		// first save the current row
		$cur = getDatabase()->one(" select * from candidate_donation where id = :id ",array('id'=>$_POST['id']));
		db_insert('archive_candidate_donation',$cur);

		if (LoginController::isLoggedIn()) {
			# put editor id into the database
			$_POST['peopleid'] = getSession()->get("user_id");
		} else {
			$_POST['peopleid'] = null;
		}
		
		// update in bulk
 		db_update('candidate_donation',$_POST,'id');

		// normalize, the bulk lazy way!
		// getDatabase()->execute(" update candidate_donation set postal = replace(upper(postal),' ','') where postal != upper(postal) or postal like '% %' ");

		// assume 'burn' the location on save, for now
		getDatabase()->execute(" update candidate_donation set location = null where id = :id ",array('id'=>$_POST['id']));

		if ($geo) {
			getDatabase()->execute(" update candidate_donation set location = PointFromText('POINT($lon $lat)') where id = :id ",array('id'=>$_POST['id']));
		}

		if ($ajax) {
			$after = getDatabase()->one(" select id,postal,astext(location) location from candidate_donation where id = :id ",array('id'=>$_POST['id']));
			pr($after);
			return;
		}

		if (LoginController::isLoggedIn()) {
#			header("Location: /election/processDonation/?id=".$_POST['id']);
#			return;
		}

		// send them back for MOAR!
		header("Location: /election/processDonation/?thanks=yes&returnid=$returnid");
	}

	public static function listDonations() {

		$toptitle = 'Campaign Donations Report';

		$postal = $_GET['postal'];
		$postal = strtoupper($postal);
		$postal = preg_replace('/ /','',$postal);
		$postalE = mysql_escape_string($postal);
		$year = $_GET['year'];
		$pinid = $_GET['pinid'];
		$ward = $_GET['ward'];
		$type = $_GET['type'];
		$donor = $_GET['donor'];
		$donorE = mysql_escape_string($donor);
		$candidate = $_GET['candidate'];
		$candidateE = mysql_escape_string($candidate);
		$where = '';

		$filtered = 0;

		if (preg_match('/^\d$/',$type)) {
			$where .= " and d.type = $type ";
			$filtered = 1;
		}

		if ($donor != '') {
			$toptitle = "Campaign Donations Report for donor $donorE";
			$where .= " and d.name like '%$donorE%' ";
			$filtered = 1;
		}
		if ($postal != '') {
			$where .= " and d.postal = '$postalE' ";
			$toptitle = "Campaign Donations Report for postal code $postalE";
			$filtered = 1;
		}
		if (count($candidate) > 0) {
			$toptitle = "Campaign Donations Report for candidates"; # $candidateE";
			$where .= " and c.id in (-999 ";
			foreach ($candidate as $c) {
				if (!preg_match('/^\d+$/',$c)) { continue; }
				$where .= ",{$c}";
			}
			$where .= " ) ";
			$filtered = 1;
		}
		$pinidwhere = '';
		if (count($pinid) > 0) {
			$ok = 1;
			foreach ($pinid as $w) {
				if (!preg_match('/^\d+$/',$w)) {
					$ok = 0;
				}
			}
			if ($ok) {
				$pinidwhere .= " d.id in ( ".implode(",",$pinid).")";
			}
		}
		if (count($year) > 0) {
			$ok = 1;
			foreach ($year as $w) {
				if (!preg_match('/^\d+$/',$w)) {
					$ok = 0;
				}
			}
			if ($ok) {
				$filtered = 1;
				$where .= " and c.year in ( ".implode(",",$year).")";
			}
		}
		if (count($ward) > 0) {
			$ok = 1;
			foreach ($ward as $w) {
				if (!preg_match('/^\d+$/',$w)) {
					$ok = 0;
				}
			}
			if ($ok) {
				$filtered = 1;
				$where .= " and c.ward in ( ".implode(",",$ward).")";
			}
		}

		#print "where $where \n"; return;

		$orderby = " c.year desc, c.ward, c.last, c.first, case when d.type = 1 then 0 else 1 end, d.type, d.name ";
		if ($_GET['format'] == 'json') {
			# not actually filtered, but we want the SQL to run
			$filtered = 1;
			$orderby = " c.id, r.id, d.page, d.y ";
		}
		if ($_GET['format'] == 'csv') {
			# not actually filtered, but we want the SQL to run
			$filtered = 1;
			$orderby = " c.id, r.id, d.page, d.y ";
		}

		$where_master = "
			d.amount is not null 
			and d.amount != ''
			$where
		";
		if (strlen($pinidwhere) > 0) {
			if ($filtered == 1) {
				$where_master = "
					(
					d.amount is not null 
					and d.amount != ''
					$where
					) or
					$pinidwhere
				";
			} else {
				$filtered = 1;
				$where_master = "
					$pinidwhere
				";
			}
		}

		$sql = "
			select 
				d.id,
				d.type,
				d.name donor,
				d.address,
				d.city,
				d.postal,
				d.amount,
				c.year,
				c.ward,
				c.first,
				c.last,
				c.id candidateid,
				c.incumbent,
				c.winner,
				d.page,
				d.x,
				d.y,
				c.gender,
				d.donor_gender,
        astext(d.location) location,
				r.id retid,
				r.supplemental,
				d.donorid
			from
				candidate_donation d
				join candidate_return r on d.returnid = r.id
				join candidate c on r.candidateid = c.id
			where 
				$where_master
			order by 
				$orderby
		";

		$rows = array();
		if ($filtered == 1) {
			$rows = getDatabase()->all($sql);
		}

		foreach ($rows as &$r) {
			$m = array();
			# POINT(-75.743323639372 45.388638525446)
			if (preg_match('/POINT\(([^ ]+) ([^\)]+)\)/',$r['location'],$m)) {
				$r['lat'] = $m[2];
				$r['lon'] = $m[1];
			}
			unset($r['location']);
		}

		if ($_GET['format'] == 'json') {
			$data = json_encode($rows);
			header("Content-Disposition: attachment; filename=ottawa_election_donations.json");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Description: File Transfer");             
			print $data;
			return;
		}
		if ($_GET['format'] == 'csv') {
			header("Content-Disposition: attachment; filename=ottawa_election_donations.csv");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Description: File Transfer");             
			$cols = array( 'id', 'type', 'donor', 'address', 'city', 'postal', 'amount', 'year', 'ward', 'first', 'last', 'incumbent', 'winner', 'page','x','y','retid','supplemental','gender','donor_gender','lat','lon');
			foreach ($cols as $c) {
				print "{$c}\t";
			}
			print "\n";
			foreach ($rows as $r) {
				foreach ($cols as $c) {
					print "{$r[$c]}\t";
				}
				print "\n";
			}
			return;
		}

    $mapMode = $_GET['map'];

		top3($toptitle);
		?>
		<div class="row">
		<div class="col-sm-6">
		<h1>Campaign Donations</h1>
    </div>
		<div class="col-sm-6">
		<!--
		<p class="lead">Like this data? <a href="/election/processDonation/">Help create more of it</a> - 10 seconds at a time.</p>
		-->
		</div><!-- /span -->
		</div><!-- /row -->

		<?php
		$candidates = getDatabase()->all(" 
			select id,year,ward,first,last from candidate order by last,first,year desc,ward 
		");
		$years = getDatabase()->all(" 
			select distinct(year) year from candidate order by year desc
		");
		$wards = getDatabase()->all(" 
			select * from (
			select 0 ward ,'Mayor'  ward_en
			union
			select ward_num ward , ward_en from wards_2010 )
			s order by ward+0
		");

		?>

<form action="/election/listDonations" class="form-horizontal">

<div class="form-group">
	<label class="col-sm-2 control-label" for="inputDonor">Donor Name</label>
	<div class="col-sm-9">
		<input type="text" id="inputDonor" class="form-control" name="donor" placeholder="optional" value="<?php print $donor; ?>"/> 
	</div>
</div>

<div class="form-group">
	<label class="col-sm-2 control-label" for="inputCandidate">Candidate(s)</label>
	<div class="col-sm-3">
		<select id="inputCandidate" class="form-control" name="candidate[]" multiple="yes" size="5">
		<?php
		foreach ($candidates as $c) {
			$selected = '';
			if (in_array($c['id'],$candidate)) {
				$selected = ' selected="1" ';
			}
			print "<option $selected value=\"{$c['id']}\">{$c['last']}, {$c['first']} ({$c['year']} ward:{$c['ward']})</option>";
		}
		?>
		</select>
	</div>

	<label class="col-sm-1 control-label" for="inputYear">Year(s)</label>
	<div class="col-sm-2">
		<select id="inputYear" class="form-control" name="year[]" multiple="yes" size="5">
		<?php
		foreach ($years as $y) {
			$selected = '';
			if (in_array($y['year'],$year)) {
				$selected = ' selected="1" ';
			}
			print "<option $selected value=\"{$y['year']}\">{$y['year']}</option>\n";
		}
		?>
		</select>
	</div>

	<label class="col-sm-1 control-label" for="inputWard">Ward(s)</label>
	<div class="col-sm-2">
		<select id="inputWard" class="form-control" name="ward[]" multiple="yes" size="5">
		<?php
		foreach ($wards as $y) {
			$selected = '';
			if (in_array($y['ward'],$ward)) {
				$selected = ' selected="1" ';
			}
			print "<option $selected value=\"{$y['ward']}\">{$y['ward']} - {$y['ward_en']}</option>\n";
		}
		?>
		</select>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-2 control-label" for="inputPostalCode">Postal Code</label>
	<div class="col-sm-3">
		<input type="text" id="inputPostalCode" class="form-control" name="postal" placeholder="optional" value="<?php print $postal; ?>"/> 
	</div>
	<label class="col-sm-1 control-label" for="inputFormat">Output</label>
	<div class="col-sm-2">
		<select id="inputFormat" class="form-control" name="format">
			<option value="" selected="1">HTML page</option>
			<option value="csv" >CSV</option>
			<option value="json" >JSON</option>
		</select>
	</div>
	<label class="col-sm-1 control-label" for="inputMap">Map</label>
	<div class="col-sm-2">
		<select id="inputMap" class="form-control" name="map">
			<option <?php print ($mapMode == 0 ? ' selected="1" ' : ''); ?> value="0">No</option>
			<option <?php print ($mapMode == 1 ? ' selected="1" ' : ''); ?> value="1">Heatmap</option>
			<option <?php print ($mapMode == 2 ? ' selected="1" ' : ''); ?> value="2">Placemarks</option>
		</select>
	</div>
</div>

<div class="form-group">
	<div class="col-sm-1 col-sm-offset-1">
	<label class="col-sm-2 control-label" for="inputType">Type</label>
	</div>
	<div class="col-sm-3">
		<?php if ($type == '') { $type = -1; } ?>
		<select id="inputType" class="form-control" name="type">
			<option value="">All</option>
			<option <?php if ($type == 0) { print ' selected="1" '; } ?> value="0">Individuals over $100</option>
			<option <?php if ($type == 1) { print ' selected="1" '; } ?> value="1">Corporate/Union</option>
			<option <?php if ($type == 2) { print ' selected="1" '; } ?> value="2">Individuals $100 or less</option>
		</select>
	</div>
	<div class="col-sm-6 col-sm-offset-1">
		<button type="submit" class="btn btn-primary">Search</button> 
		<a class="btn btn-primary" href="?format=csv">Download all: CSV</a>
		<a class="btn btn-primary" href="?format=json">Download all: JSON</a>
		<a class="btn btn-primary" href="/election/listDonations">Reset (New Search)</a>
	</div>
</div>


		<?php 
    if (count($rows) > 0) {

      if ($mapMode > 0) {

      $noLocationCount = 0;
      foreach ($rows as $r) { 
        if ($r['lat'] == '') { 
          $noLocationCount ++;
          continue; 
        }
      }
      $perc = round((count($rows)-$noLocationCount)/count($rows)*100);
      ?>
      <a name="donationheatmap"></a>
      <h3>Donation Map <small><?php print (count($rows)-$noLocationCount); ?> donations (<?php print $perc; ?>%) have location data</small></h3>
      <div id="map_canvas" style="width:100%; height:600px;"></div>
      <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=visualization&key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>"></script>
      <script>
      var map, pointarray, heatmap;
      var heatpoints = [
      <?php 
      foreach ($rows as $r) {
        if ($r['lat'] == '') { 
          continue; 
        }
        $lat = $r['lat'];
        $lon = $r['lon'];
        ?>
        {location: new google.maps.LatLng(<?php print $lat; ?>, <?php print $lon; ?>), weight: <?php print $r['amount']; ?>},
        <?php 
      } 
      ?>
      ];

      var mapOptions = { center: new google.maps.LatLng(45.35,-75.70), zoom: 11, mapTypeId: google.maps.MapTypeId.ROADMAP };
      map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);

			var bounds = new google.maps.LatLngBounds();
			<?php
      foreach ($rows as $r) {
        if ($r['lat'] == '') { 
          continue; 
        }
        $lat = $r['lat'];
        $lon = $r['lon'];
				?>
				bounds.extend(new google.maps.LatLng(<?php print $lat; ?>, <?php print $lon; ?>));
        <?php 
      } 
      ?>

      var pointArray = new google.maps.MVCArray(heatpoints);
      heatmap = new google.maps.visualization.HeatmapLayer({data: pointArray });
      var gradient = [ 
        'rgba(0, 255, 255, 0)', 'rgba(0, 255, 255, 1)', 'rgba(0, 191, 255, 1)', 'rgba(0, 127, 255, 1)', 'rgba(0, 63, 255, 1)', 'rgba(0, 0, 255, 1)',
        'rgba(0, 0, 223, 1)', 'rgba(0, 0, 191, 1)', 'rgba(0, 0, 159, 1)', 'rgba(0, 0, 127, 1)', 'rgba(63, 0, 91, 1)', 'rgba(127, 0, 63, 1)',
        'rgba(191, 0, 31, 1)', 'rgba(255, 0, 0, 1)' ];
      heatmap.set('gradient', heatmap.get('gradient') ? null : gradient);
      heatmap.set('opacity', 1);
      // heatmap.set('radius', 10);
      <?php if ($mapMode == 1) { ?>
      heatmap.setMap(map);
      <?php } ?>
      var infowindow = new google.maps.InfoWindow();
      <?php 
      if ($mapMode == 2) {
      foreach ($rows as $r) { 
        $pinColor = '';
        if ($r['type'] == 0) {
          $pinColor = '00ff00';
        } else if ($r['type'] == 1) {
          $pinColor = 'ff0000';
        } else {
          $pinColor = '0000ff';
        }
				if (!isset($r['type'])) {
					$r['type'] = 'Unclassified';
				} elseif ($r['type'] == 0) {
					$r['type'] = 'Individual over $100';
				} elseif ($r['type'] == 1) {
					$r['type'] = 'Corporate/Union';
				} elseif ($r['type'] == 2) {
					$r['type'] = 'Individuals $100 or less';
				} else {
          $r['type'] = 'Huh?';
        }
        if ($r['lat'] == '') { 
          continue; 
        }
        $lat = $r['lat'];
        $lon = $r['lon'];

				$randShift = rand(0,10000);
				if ($randShift % 2 == 0) { $randShift = $randShift * -1; }
				$randShift = $randShift/100000000;
				$lat += $randShift;
				$randShift = rand(0,10000);
				if ($randShift % 2 == 0) { $randShift = $randShift * -1; }
				$randShift = $randShift/100000000;
				$lon += $randShift;

        ?>
        var pinColor = "<?php print $pinColor; ?>";
        var pinImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + pinColor, new google.maps.Size(21, 34), new google.maps.Point(0,0), new google.maps.Point(10, 34));
        var pinShadow = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_shadow", new google.maps.Size(40, 37), new google.maps.Point(0, 0), new google.maps.Point(12, 35));
        var marker<?php print $r['id']; ?> = new google.maps.Marker({ 
          position: new google.maps.LatLng(<?php print $lat; ?>, <?php print $lon; ?>), 
          map: map, 
          title: '<?php print $r['amount'];?>',
          icon: pinImage,
          shadow: pinShadow
        });
        google.maps.event.addListener(marker<?php print $r['id'] ?>, 'click', function() {
          infowindow.setContent(
            '<p>Amount: <?php print $r['amount']; ?> ' + 
						'<a target="_blank" href="/election/donation/<?php print $r['id']; ?>">Details</a></p> ' +
            '<p>Type: <?php print $r['type']; ?><br/>' +
            '<a target="_blank" href="/election/listDonations?postal=<?php print $r['postal']; ?>&map=0"><?php print $r['postal']; ?></a></p>' 
          );
          infowindow.open(map,marker<?php print $r['id'] ?>);
        });
        <?php 
      } 
      }
      ?>
			map.fitBounds(bounds);  
      </script>
      &nbsp;
        <?php 
      } // mapMode
      ?>

			<script>
			function clearPinIds() {
				$( "input[name='pinid[]']" ).each(function(){
					this.checked = false;
				});
			}
			</script>

		  <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
			<tr>
					<th>year</th>
					<th>ward</th>
					<th>candidate</th>
					<th>donor</th>
					<th>amount</th>
					<th>address</th>
					<th>city</th>
					<th>postal</th>
					<th>type</th>
					<th>pin<br/>(<a href="javascript:clearPinIds()">clear</a>)</th>
			</tr>
			<?php
			$total = 0;
			$totalType = array();
			$candidates = array();
			foreach ($rows as $r) {
				if (!isset($r['type'])) {
					$r['type'] = 'Unclassified';
				} elseif ($r['type'] == 0) {
					$r['type'] = 'Individual over $100';
				} elseif ($r['type'] == 1) {
					$r['type'] = 'Corporate/Union';
				} elseif ($r['type'] == 2) {
					$r['type'] = 'Individuals $100 or less';
				} else {
          $r['type'] = 'Huh?';
        }
	
				$totalType[$r['type']] += $r['amount'];
	
				print "<tr>";
				print "<td>{$r['year']}</td>";
				print "<td>{$r['ward']}</td>";
				print "<td><nobr><a href=\"/election/listDonations?candidate[]={$r['candidateid']}\">{$r['last']}, {$r['first']}</a><nobr></td>";
				print "<td><a href=\"/election/donor/{$r['donorid']}\">{$r['donor']}</a></td>";
				print "<td><a href=\"/election/donation/{$r['id']}\">\$".formatMoney($r['amount'],true)."</a></td>";
				print "<td>{$r['address']}</td>";
				print "<td>{$r['city']}</td>";
				print "<td><a href=\"/election/listDonations?postal={$r['postal']}\">{$r['postal']}</a></td>";
				print "<td>";
				print "{$r['type']}";
				if ($r['supplemental'] == 1) {
					print " (supplemental)";
				}

				print "</td>";
				print "<td><input ".(in_array($r['id'],$pinid) ? ' checked="1" ' : '')." type=\"checkbox\" name=\"pinid[]\" value=\"{$r['id']}\"</td>";
				print "</tr>";
				$total += $r['amount'];
				$candidates[$r['candidateid']] = 1;
			}
				print "<tr>";
				print "<td></td>";
				print "<td></td>";
				print "<td></td>";
				print "<td><b>Total Amount</b></td>";
				print "<td><b>\$".formatMoney($total,true)."</b></td>";
				print "<td></td>";
				print "<td></td>";
				print "<td></td>";
				print "<td></td>";
				print "</tr>";
				foreach ($totalType as $k => $v) {
					print "<tr>";
					print "<td></td>";
					print "<td></td>";
					print "<td></td>";
					print "<td><b>Total from {$k}</b></td>";
					print "<td><b>\$".formatMoney($v,true)." (".round($v/$total*100)."%)</b></td>";
					if ($k == 'Individuals $100 or less') {
						$totalIndividualU100 = $v;
						print "<td colspan=\"4\">
						Minimum of ".round($totalIndividualU100/100)." additional donors, assuming each contributed $100.<br/>
						The actual number of donors in this class is likely much higher.<br/>
						It's probable the average donation ranged between $20 and $100.
						</td>";
					} else {
						print "<td></td>";
						print "<td></td>";
						print "<td></td>";
						print "<td></td>";
					}
					print "</tr>";
				}
				print "<tr>";
				print "<td></td>";
				print "<td></td>";
				print "<td></td>";
				print "<td><b>Total Donations</b></td>";
				print "<td><b>".count($rows)."</b></td>";
				print "<td colspan=\"4\">";
				print "<i>Does not include the number of donations of $100 or less, if any.</i>";
				print "</td>";
				print "</tr>";
				print "<tr>";
				print "<td></td>";
				print "<td></td>";
				print "<td></td>";
				print "<td><b>Total Candidates</b></td>";
				print "<td><b>".count($candidates)."</b></td>";
				print "<td></td>";
				print "<td></td>";
				print "<td></td>";
				print "<td></td>";
				print "</tr>";
			?>
			</table>
  		<?php 
    } else {
      # serach returned nothing, so prompt the user with some useful candidate based queries.
      ?>
			<center><h3>No records found for this search</h3></center>
	
			<div class="row-fluid">
			<div class="span12">
			<h3>Browse by Candidate</h3>
	    <table class="table table-bordered table-hover table-condensed">
			<tr>
				<th>Last</th>
				<th>First</th>
				<th>Donations*</th>
				<th>Amount*</th>
				<th>Year</th>
			</tr>
			<?php
			$sql = " 
				select c.id, c.last, c.first, min(c.year) minyear, max(c.year) maxyear, sum(case when d.id is null then 0 else 1 end) as donations, sum(amount) as total
				from candidate c
					left join candidate_return r on r.candidateid = c.id
					left join candidate_donation d on d.returnid = r.id
				group by c.id, c.last, c.first 
				having sum(case when d.id is null then 0 else 1 end) > 0
				order by max(c.year) desc, c.last, c.first ";
			$rows = getDatabase()->all($sql);
			foreach ($rows as $r) {
				print "<tr>
				<td><a href=\"/election/listDonations?candidate[]={$r['id']}\">{$r['last']}</a></td>
				<td>{$r['first']}</td>
				<td>{$r['donations']}</td>
				<td>\$".formatMoney($r['total'])."</td>
				";
				if ($r['minyear'] == $r['maxyear']) {
					print "<td>{$r['minyear']}</td>";
				} else {
					print "<td>{$r['minyear']} - {$r['maxyear']}</td>";
				}
				print "</tr>";
			}
			?>
			</table>
			<p>*database is not yet complete</p>
			</div><!-- /span -->
			</div><!-- /row -->

  		<?php 
    } 

		?>
		</form>
		<?php

		bottom3();
	}

	public static function showDonation($id) {
		$sql = "
			select 
				d.id,
				d.type,
				d.name donor,
				d.address,
				d.city,
				d.postal,
				d.amount,
				d.page,
				d.x,
				d.y,
				astext(d.location) as geo,
				c.year,
				c.ward,
				c.first,
				c.last,
				r.filename,
				r.id retid,
				c.id candid,
				d.donorid
			from
				candidate_donation d
				join candidate_return r on d.returnid = r.id
				join candidate c on r.candidateid = c.id
			where d.id = $id
		";
		$r = getDatabase()->one($sql);
		if (!$r['id']) {
			top();
			print "The donation with ID $id was not found";
			bottom();
			return;
		}

		$r['donor'] = htmlentities($r['donor']);
		$r['address'] = htmlentities($r['address']);
		$r['city'] = htmlentities($r['city']);
		$r['postal'] = htmlentities($r['postal']);
		$r['amount'] = htmlentities($r['amount']);

		$next = getDatabase()->one(" select min(y) y from candidate_donation where returnid = {$r['retid']} and page = {$r['page']} and y > {$r['y']} ");

		$pages = self::getReturnPages($r['year'],$r['filename']);
		$page = $r['page'];
		$pagefile = $pages[$page];
    $size = getimagesize($pagefile);
    $imgW = $size[0];
		$padding = 150;
		if (isset($next['y'])) {
			$imgH = $next['y']-$r['y']+$padding;
		} else {
	    $imgH = 200;
		}
			top("Campaign Donation from " . $r['donor'] . ' to ' . $r['first'] . ' ' . $r['last'] . ' for $' . $r['amount'] . ' in ' . $r['year']);
			?>
			<h1>Campaign Donation Details</h1>

		<center>
    <canvas id="canvas" width="<?php print $imgW; ?>" height="<?php print $imgH; ?>" style="border: solid 1px #c0c0c0; margin-bottom: 20px;">
    </canvas><br/>
    <script>
		var canvas = document.getElementById('canvas');
		var context = canvas.getContext('2d');
		var imageObj = new Image();
		context.fillStyle = "blue";
		context.font = "bold 16px Verdana";
	  context.fillText("... loading donation image ... could take a few seconds ... chill!", 20,<?php print $imgH/2; ?>);
		imageObj.onload = function() {
			context.drawImage(imageObj,0,-<?php print $r['y']-($padding/2); ?>);
		        context.beginPath();
		        context.arc(<?php print $r['x']; ?>-5,<?php print ($padding/2)+2; ?>, 5, 0, Math.PI*2, true); 
		        context.closePath();
		        context.fill();
		};
		imageObj.src = '/election/processReturn/<?php print "{$r['retid']}?png=1&page={$r['page']}"; ?>';
		</script>

			<div class="row-fluid">
			<div class="span6">
	    <table class="table table-bordered table-hover table-condensed">
			<?php

			if (!isset($r['type'])) {
				$r['type'] = 'Unclassified';
			} elseif ($r['type'] == 0) {
					$r['type'] = 'Individual over $100';
				} elseif ($r['type'] == 1) {
					$r['type'] = 'Corporate/Union';
				} elseif ($r['type'] == 2) {
					$r['type'] = 'Individuals $100 or less';
			}

			print "<tr><th>Donor Name</th><td><a href=\"/election/donor/{$r['donorid']}\">{$r['donor']}</a></td></tr>";
			print "<tr><th>Donor Type</th><td>{$r['type']}</td></tr>";
			print "<tr><th>Amount</th><td>{$r['amount']}</td></tr>";
			print "<tr><th>Address</th><td>{$r['address']}</td></tr>";
			print "<tr><th>City</th><td>{$r['city']}</td></tr>";
			print "<tr><th>Province</th><td>{$r['prov']}</td></tr>";
			print "<tr><th>Postal</th><td><a href=\"/election/listDonations?postal={$r['postal']}\">{$r['postal']}</a></td></tr>";
			print "<tr><th>Candidate</th><td><a href=\"/election/listDonations?candidate[]={$r['candid']}\">{$r['last']}, {$r['first']} ({$r['year']})</a></td></tr>";
			print "<tr><th>Ward</th><td>{$r['ward']}</td></tr>";
			?>
			<tr><th>Location</th>
			<td>
				<a target="_blank" href="http://documents.ottawa.ca/sites/documents.ottawa.ca/files/documents/<?php print $r['filename']; ?>"><?php print $r['filename']; ?></a>,
				page <?php print ($r['page']+1); ?>, <?php print round($r['y']*100/$size[1]); ?>% down from top
			</td>
			</tr>
			<tr><th>Found an error?</th>
			<td>
				<?php if (LoginController::isLoggedIn()) { ?>
				<a href="/election/processDonation/?id=<?php print $r['id']; ?>">You are logged in, so just go fix it!</a>
				<?php } else { ?>
				<a href="/user/login?next=<?php print urlencode("/election/processDonation?id={$r['id']}"); ?>">Log in to fix it!</a>
				<?php } ?>
			</td>
			</tr>
			</table>


			</div>
			<div class="span6">

			<?php
			if ($r['geo'] != '') {
      $ll = getLatLonFromPoint($r['geo']);
      $lat = $ll['lat'];
      $lon = $ll['lon'];
			?>
	    <div id="map_canvas" style="width:100%; height:300px;"></div>
	    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
	    <script>
	    var mapOptions = { center: new google.maps.LatLng(<?php print $lat; ?>,<?php print $lon; ?>), zoom: 14, mapTypeId: google.maps.MapTypeId.ROADMAP };
	    map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
      var marker = new google.maps.Marker({ 
        position: new google.maps.LatLng(<?php print $lat; ?>, <?php print $lon; ?>), 
        map: map
      });
			</script>
			<?php } // map ?>

			</div>
			</div>

			<?php disqus(); ?>

			<?php
		bottom();
	}

  public static function questionAdd() {
		
		top();
		?>
		Times is up! No more asking questions until 2018!
		<?php
		bottom();

		return;
		if (!LoginController::blockUnlessLoggedIn()) { 
      return;
    }
    top();

		$race = $_GET['race'];

    ?>
    <h3>Submit an Election Question</h3>


    <form class="form-horizontal" method="post">

    <div class="control-group">
    <label class="control-label" for="inputtitle">Question Title</label>
    <div class="controls">
		<b>Are you a candidate in this race? Don't ask a question here. It's for voters only.</b><br/>
		<b>YES</b> ask questions on matters of policy, or platform, or vision, etc.<br/>
		<b>DO NOT</b> ask factual questions with only one answer (ie: what is your website; are there all-candidate debates planned)<br/>
		<b>DO NOT</b> ask the same question in all the wards (I notice that, and will delete all of them)<br/>
		<b>YES</b> have fun!
    <input type="text" id="inputtitle" name="title" placeholder="" class="input-block-level">
    <i>(100 chars max)</i>
    </div>
    </div>

    <div class="control-group">
    <label class="control-label" for="inputbody">Question Body</label>
    <div class="controls">
    <textarea id="inputbody" name="body" class="input-block-level" rows="5"></textarea>
    </div>
    </div>

    <div class="control-group">
    <label class="control-label" for="inputrace">Race</label>
    <div class="controls">
    <select name="race" class="input-block-level">
			<?php
      // <option value="-1">City Wide (mayor and councillor races)</option>
      // <option value="0">Mayor Race Only</option>
			?>
      <option value="---">-----Choose Your Ward-----</option>
      <?php
      $races = getDatabase()->all(" select ward,wardnum from electedofficials where ward != '' order by ward ");
      foreach ($races as $r) {
				$selected = ($r['wardnum'] == $race ? ' selected="yes" ' : '');
        ?>
        <option <?php print $selected; ?> value="<?php print $r['wardnum']; ?>"><?php print $r['ward']; ?></option>
        <?php
      }
      ?>
    </select>
		<i>We are only accepting questions at the local ward level. If you are not sure what ward you live in, use the "Find Your Ward" widget below.</i>
    </div>
    </div>

    <div class="control-group">
    <div class="controls">
    <button type="submit" class="btn">Submit Question</button>
    </div>
    </div>

		<h3>Find Your Ward</h3>
		<iframe style="width: 100%; height: 100px; border: 0px solid #c0c0c0;" src="http://ottwatch.ca/api/widget/findward"></iframe>

    </form>
    <?php
    bottom();
  }

  public static function questionVote() {
		if (abs($_POST['vote']) != 1) {
			print "BAD VOTE value\n";
			return;
		}
		getDatabase()->execute(" delete from question_vote where questionid = :id and personid = ".getSession()->get("user_id"),array('id'=>$_POST['id']));
		$values = array();
		$values['questionid'] = $_POST['id'];
		$values['vote'] = $_POST['vote'];
    $values['personid'] = getSession()->get("user_id");
    $id = db_insert('question_vote',$values);

		$votes = getDatabase()->one(" 
			select 
			count(1) votes, 
			sum(vote) tally,
			sum(case when vote = 1 then 1 else 0 end) up,
			sum(case when vote = -1 then 1 else 0 end) down
			from question_vote 
			where questionid = {$_POST['id']} "
		);
		print json_encode($votes);
  }

  public static function questionAddPost() {
    $values = array();
    $values['title'] = $_POST['title'];
    $values['body'] = $_POST['body'];
    $values['published'] = 1; # no pre-moderation, for now
    $values['personid'] = getSession()->get("user_id");
    $id = db_insert('question',$values);

    $values = array();
    $values['questionid'] = $id;
    $values['ward'] = $_POST['race'];
    $id = db_insert('election_question',$values);

    sendEmail("ottwatch@gmail.com","New question ($id)", "link: ".OttWatchConfig::WWW."/election/question/$id/ \n\ntitle: {$_POST['title']}\n\nbody: {$_POST['body']}\n\n");

    header("Location: /election/question/$id/".urlencode($_POST['title']));
  }

  public static function saveAnswer() {
    $values = array();
    $values['body'] = $_POST['answer'];
    $values['questionid'] = $_POST['questionid'];
    $values['personid'] = getSession()->get("user_id");
    $id = db_insert('answer',$values);
    header("Location: /election/question/{$_POST['electionquestionid']}/");
  }

  public static function showQuestion($id,$title) {

    $races = getDatabase()->all(" select ward,wardnum from electedofficials where ward != '' order by ward ");
    $wards = array();
    $wards[-1] = 'City Wide';
    $wards[0] = 'Mayor';
    foreach ($races as $r) {
      $wards[$r['wardnum']] = $r['ward'];
    }

    $q = getDatabase()->one(" 
      select 
				e.ward,
				q.id,title,body,published,e.id electionquestionid,p.name,q.created,
				p.twitter,
				p.facebookid
      from election_question e 
        join question q on q.id = e.questionid 
        join people p on p.id = q.personid
      where published = 1 and e.id = :id ",array('id'=>$id));
    if (!isset($q['title'])) {
      top();
      ?>
      <h1>Not found</h1>
      This question was not found, or it has not been published yet. Or it was un-published after the fact by the moderator. Or all hell has broken loose.
      <?php
      bottom();
      return;
    }
    $dbtitle = $q['title'];
    if ($dbtitle != $title) {
      # make title match the database
      header("Location: /election/question/$id/".urlencode($dbtitle));
      return;
    }

		$votes = getDatabase()->one(" select count(1) votes, sum(vote) tally from question_vote where questionid = {$q['id']} ");

    $ward = $q['ward'];
    if ($ward == -1) {
      $wardname = 'City Wide';
      $candidates = getDatabase()->all(" select * from candidate where withdrew is null and nominated is not null and year = " . self::year . " order by ward,rand() ");
    } else if ($ward == 0) {
      $wardname = 'Mayor';
      $candidates = getDatabase()->all(" select * from candidate where withdrew is null and nominated is not null and ward = 0 and year = " . self::year . " order by ward,rand() ");
    } else {
      $candidates = getDatabase()->all(" select * from candidate where withdrew is null and nominated is not null and ward = $ward and year = " . self::year . " order by ward,rand() ");
      $wardname = getDatabase()->one(" select ward from electedofficials where wardnum = $ward ");
      $wardname = $wardname['ward'];
    }

    top("Election Question: $title");
    ?>

    <div class="row-fluid">
    <div class="span8">

		<script>
		</script>

    <div style="background: #f0f0f0; padding: 20px; border-radius: 5px; margin-bottom: 5px;">
    <h1><?php print htmlentities($title); ?></h1>
    <p style="float: right; text-align: right; padding-left: 5px;">
		Asked by <b><?php print htmlentities($q['name']); ?></b>
		<?php 
		if ($q['twitter'] != null) {
			print "(<a href=\"http://twitter.com/@{$q['twitter']}\">";
			print "@{$q['twitter']}";
			print "</a>)";
		} else if ($q['facebookid'] != null) {
			print "(<a href=\"http://facebook.com/{$q['facebookid']}\">";
			print "facebook";
			print "</a>)";
		}
		?>
		<br/><?php print $q['created']; ?><br/>
		<?php if (LoginController::isLoggedIn()) { ?>
			<span style="font-size: 150%;">
			<a href="javascript:voteOnQuestion('qv',<?php print $q['id']; ?>,1);"><i class="fa fa-thumbs-o-up"></i></a>
			<a href="javascript:voteOnQuestion('qv',<?php print $q['id']; ?>,-1);"><i class="fa fa-thumbs-o-down"></i></a>
			</span><br/>
		<?php } else { ?>
		<a href="<?php print LoginController::getLoginUrl(); ?>">Login to vote this question up or down</a><br/>
		<?php } ?>
		Score <span id="qvTally"><?php print $votes['tally']; ?></span> (<span id="qvVotes"><?php print $votes['votes']; ?></span> votes)
		<span id="qvResult"></span>

		</p>
    <p class="lead"><?php print htmlentities($q['body']); ?></p>
		<div style="clear: both"></div>
    </div>


		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>

		<?php if (true) { ?>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <?php
    $prevward = -1;
    foreach ($candidates as $c) {
      if ($prevward != $c['ward']) {
        ?>
        <tr>
        <th colspan="2"><h3><a href="/election/ward/<?php print $c['ward']; ?>">Ward <?php print $c['ward']; ?>: <?php print $wards[$c['ward']]; ?></a></h3></th>
        </tr>
        <?php
      }
      $prevward = $c['ward'];
      ?>
      <tr>
      <!--<th><h5><?php print "{$c['first']} {$c['last']}"; ?></h5></th>-->
      <td><nobr><a name="candidate<?php print $c['id']; ?>"></a><?php print "{$c['first']} {$c['last']}"; ?></nobr></td>
      <td>
      <?php

      if (LoginController::isLoggedIn() && getSession()->get("user_id") == $c['personid']) {
        ?>
        <form class="form-incine" action="/election/question/answer" method="post">
        <input type="hidden" name="questionid" value="<?php print $q['id']; ?>"/>
        <input type="hidden" name="electionquestionid" value="<?php print $q['electionquestionid']; ?>"/>
        <textarea class="input-block-level" rows="10" name="answer"><?php print htmlentities($answer['body']); ?></textarea>
    		<input class="btn btn-success" type="submit" value="Save Answer"/>
        </form>
        <?php
        continue;
      }

			$answer = array();
      if (isset($c['personid'])) {
	      $answer = getDatabase()->one(" select * from answer where questionid = {$q['id']} and personid = {$c['personid']} order by created desc limit 1 ");
				if (!isset($answer['body']) || $answer['body'] == '') {
	        ?>
					<span style="color: #c0c0c0;">(no answer yet)</span>
					<?php
					if (false && $c['twitter'] != '') {
						$text = ".@{$c['twitter']} I want to know: {$q['title']}";
						$url = OttWatchConfig::WWW."/election/question/".$id."/".urlencode($title);
						?>
						Remind <?php print $c['first']; ?> to answer!<br/>
						<a target="_blank" href="mailto:<?php print $c['email']; ?>?Subject=Election Question: <?php print htmlentities($q['title']); ?>&Body=<?php print $url; ?>">Email <?php print $c['email']; ?></a><br/>
						Twitter:
						<a href="https://twitter.com/share" class="twitter-share-button" 
							data-text="<?php print htmlentities($text); ?>"
							data-count="none"
							data-hashtags="ottvote"
							data-lang="en">Tweet to <?php print $c['twitter']; ?></a>
						<?php
					}
					?>
	        </td>
	        </tr>
	        <?php
				} else {
		      $bb = $answer['body'];
					$bb = preg_replace('/\r/','',$bb);
					$bb = preg_replace('/\n/','<br/>',$bb);
					print $bb;
					?>
					<?php
				}
			} else {
				?>
        <i>No known email address for the candidate. Let ottwatch@gmail.com know if you know it.</i><br/>
				<?php
			}
			?>
      </td>
      </tr>
      <?php
    }
    ?>
    </table>
		<?php } else { // hide candidate table for now ?>
		<center><i>
		We're just collecting questions for now. When enough candidates have registered
		we'll start prompting candidates to answer questions (and show if they have 
		declined to do so). Two questions per person, max.
		</i></center>
		<?php } ?>

		<?php disqus(); ?>
    </div><!-- /span -->
    <div class="span4">
    <a href="/election/question/list"><h3>See Other Questions</h3></a>
		See what other questions have been put to candidates.
		<!--
    <a href="/election/question/add"><h3>Want to ask a question?</h3></a>
    Anyone can ask a question using OttWatch. After logging in with Twitter or Facebook create a question title
    and body, and pick the ward you live in. <a href="/election/question/add">Ask your question</a>.
		-->
    </div><!-- /span -->
    </div><!-- /row -->
    <?php
    bottom();

  }

	public static function questionList() {
		top();
		?>
		<h1>Election Questions
		<small><a href="/election/">main election page</a></small>
		</h1>
		<p class="lead">
		Important questions from regular people. What do you want to know from candidates?
    <!-- <b><a href="/election/question/add">Ask one</a></b>. -->
		</p>
		<p>Click through to see the answers, and vote questions up or down.</p>
		<div class="row-fluid">

		<?php
		$questions = getDatabase()->all("
			select 
				eq.id electionquestionid,
				q.id,
				q.title,
				q.body,
				q.created,
				p.name,
				case 
					when eq.ward > 0 then eo.ward
					when eq.ward = 0 then 'Mayor'
					when eq.ward = -1 then 'City Wide'
					else 'Unclassified'
				end wardname,
				case when a.count is null then 0 else a.count end count,
				case when a.count is null then 'never' else a.latest end latest,
				case when v.questionid is null then 0 else v.votes end votes,
				case when v.questionid is null then 0 else v.score end score
			from question q
				join election_question eq on eq.questionid = q.id
				join people p on p.id = q.personid
				left join electedofficials eo on eo.wardnum = eq.ward
				left join (
					select questionid,count(1) count,max(created) latest from answer group by questionid
				) a on a.questionid = q.id
				left join (
					select questionid,sum(vote) score,count(1) votes from question_vote group by questionid
				) v on v.questionid = q.id
			where
				q.published = 1
			order by 
				case when eq.ward <= 0 then 0 else 1 end,
				wardname,
				rand()
		");

		?>

		<?php
		$count = 0;
		foreach ($questions as $q) {
			if ($count++ % 3 == 0) {
				?>
				</div>
				<div class="row-fluid">
				<?php
			}
			?>
			<div class="span4">
			<a href="/election/question/<?php print $q['electionquestionid']; ?>/<?php print urlencode($q['title']); ?>"><h3><?php print htmlentities($q['title']); ?></h3></a>
			<p>
			<?php if ($q['body'] != '') { ?>
			<i><?php print htmlentities($q['body']); ?> </i>
			<!--
			<b>(Score: <?php print $q['score']; ?> based on <?php print $q['votes']; ?> votes)</b></i>
			-->
			<?php } ?>
			<i>(<?php print $q['wardname']; ?>)</i>
			<!--
			<?php print $q['count']; ?> answers.<br/>
			Asked: <?php print $q['created']; ?><br/>
			Latest:  <?php print $q['latest']; ?>
			-->
			</p>
			</div>
			<?php
		}
		?>

    </div><!-- /row -->
		<?php
		bottom();
	}

}
