<?php

/*

Populate candidate table from electedofficials table:

  delete from candidate where incumbent = 1;
  insert into candidate (year,ward,   first,last,nominated,incumbent) 
  select                 2014,case when wardnum > 0 then wardnum else 0 end,first,last,null,     1 from electedofficials
  ;

// and committing this because then I can't lose it
update candidate set twitter = 'AllanHubley_23' where incumbent = 1 and ward = 23;
update candidate set twitter = 'BarrhavenJan' where incumbent = 1 and ward = 3;
update candidate set twitter = 'BobMonette1' where incumbent = 1 and ward = 1;
update candidate set twitter = 'CouncilHolmes' where incumbent = 1 and ward = 14;
update candidate set twitter = 'CouncillorDoug' where incumbent = 1 and ward = 20;
update candidate set twitter = 'CouncillorMcRae' where incumbent = 1 and ward = 16;
update candidate set twitter = 'Eli_Ward5' where incumbent = 1 and ward = 5;
update candidate set twitter = 'Go_Taylor' where incumbent = 1 and ward = 7;
update candidate set twitter = 'JimWatsonOttawa' where incumbent = 1 and ward = 0;
update candidate set twitter = 'Katherine_Hobbs' where incumbent = 1 and ward = 15;
update candidate set twitter = 'KeithEgli' where incumbent = 1 and ward = 9;
update candidate set twitter = 'MathieuFleury' where incumbent = 1 and ward = 12;
update candidate set twitter = 'PeterHumeOttawa' where incumbent = 1 and ward = 18;
update candidate set twitter = 'RickChiarelli' where incumbent = 1 and ward = 8;
update candidate set twitter = 'ScottMoffatt21' where incumbent = 1 and ward = 21;
update candidate set twitter = 'ShadQadri' where incumbent = 1 and ward = 6;
update candidate set twitter = 'StephenBlais' where incumbent = 1 and ward = 19;
update candidate set twitter = 'SteveDesroches' where incumbent = 1 and ward = 22;
update candidate set twitter = 'TimTierney' where incumbent = 1 and ward = 11;
update candidate set twitter = 'chernushenko' where incumbent = 1 and ward = 17;
update candidate set twitter = 'dianedeans' where incumbent = 1 and ward = 10;
update candidate set twitter = 'marianne4kanata' where incumbent = 1 and ward = 4;
update candidate set twitter = 'rainerbloess' where incumbent = 1 and ward = 2;

*/

class ElectionController {

  const year = 2014;
  const prevyear = 2010;

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
		self::showWardMapPriv($ward,-1);
		bottom();
	}

  public static function showWardMapPriv($ward,$height) {

		if ($height <= 0) { $height = 590; }

    $json = file_get_contents(OttWatchConfig::WWW."/api/wards/$ward?polygon=1");
    $data = json_decode($json);
    $poly = $data->polygon;


    ?>
		<center>
    <div id="map_canvas" style="width:90%; height:<?php print $height; ?>px;"></div>
		</center>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
    <script>
    var mapOptions = { center: new google.maps.LatLng(45.420833,-75.59), zoom: 10, mapTypeId: google.maps.MapTypeId.ROADMAP };
    infowindow = new google.maps.InfoWindow({ content: '' });
    map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
    var coords = [
	    <?php
	    foreach ($poly as $latlon) {
	      print "new google.maps.LatLng({$latlon->lat}, {$latlon->lon}), \n"; # 25.774252, -80.190262),
	    }
	    ?>
    ];
    polygon = new google.maps.Polygon({
      paths: coords,
      strokeColor: '#c0c0c0',
      fillColor: '#c0c0c0',
      fillOpacity: 0.35,
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
      $title = "$wardname Ward Race";
    }

		top($title);
		print "<h1>$title <small>(<a href=\"/election\">main election page</a>)</small></h1>\n";

    $rows = getDatabase()->all("
      select * 
      from candidate 
      where ward = :ward and year = :year and nominated is not null 
      order by rand() ",array('ward'=>$race,'year'=>self::year));
    ?>
    <div class="row-fluid">
    <div class="span6">
    <h2>Candidates</h2>
    <?php
    if (count($rows) == 0) {
      ?>
      <i>No registered candidates yet.</i>
      <?php
    } else {
	    ?>
	    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
	    <tr>
	      <th>Name</th>
	      <th>Web</th>
	      <th>Email/Phone/Etc</th>
	      <th>Registered</th>
	    </tr>
	    <?php
	    foreach ($rows as $r) {
	      ?>
	      <tr>
	        <td>
	          <?php print "{$r['last']}, {$r['first']} {$r['middle']}"; ?>
	          <?php if ($r['incumbent'] == TRUE) { /*print "*";*/ } ?>
	        </td>
	        <td>
	        <a target="_blank" href="http://<?php print $r['url']; ?>"><?php print $r['url']; ?></a>
	        </td>
	        <td>
					<?php if ($r['email'] != '') { ?>
	        <a target="_blank" href="mailto:<?php print $r['email']; ?>?Subject=Election 2014"><?php print $r['email']; ?></a><br/>
					<?php } ?>
					<?php if ($r['phone'] != '') { ?>
					<?php print $r['phone']; ?><br/>
					<?php } ?>
          <?php if ($r['twitter'] != '') { ?>
          <a href="https://twitter.com/<?php print $r['twitter']; ?>" class="twitter-follow-button" data-show-count="false" data-lang="en"><?php print $r['twitter']; ?></a>
          <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
          <?php } ?>
          <?php if ($r['facebook'] != '') { ?>
					<div class="fb-like" data-href="<?php print $r['facebook']; ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
          <?php } ?>
	        </td>
	        <td>
	        <?php print substr($r['nominated'],0,10); ?>
	        </td>
	      </tr>
	      <?php
	    }
    }
    ?>
    </table>

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
		    } else {
          if (self::isRaceOn()) {
	          print "Has not (yet) registered as a candidate.";
	          if ($incumbent['twitter'] != '') {
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
    if ($race > 0) { 
      ?>
	    <h2>Map <small>(<a href="/election/ward/<?php print $race; ?>/map">fullsize</a>)</small></h2>
	    <?php
			self::showWardMapPriv($race,200);
    }
    ?>

    </div>

    <div class="span6">
    <p style="text-align: center;">The discussion thread will remain open for the entire 2014 year. Be civil.</p>
    <?php disqus(); ?>
    </div>
    </div><!-- / row -->

    <?php

    bottom();
  }

  public static function showMain() {
    top();
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

      $rows = getDatabase()->all("select * from candidate where ward = :ward and year = :year and nominated is not null order by ward,rand()",array('ward'=>$ward['wardnum'],'year'=>self::year));
      ?>
      <div class="span3">
      <h4><a href="<?php print $raceLink; ?>"><?php print "{$wardInfo['ward']}"; if (count($rows) > 0) { print ' ('.count($rows).')'; } ?></a></h4>
      <?php
      if (count($rows) == 0) {
        print "<i style=\"color: #c0c0c0;\">No Candidates Registered Yet</i>\n";
      }
      foreach ($rows as $row) {
        print "{$row['last']}, {$row['first']}";
        #if ($row['incumbent']) { print " *"; }
        print "<br/>\n";
      }
      ?>
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
		if ($id == '') {
			top();
			#
			# Display list of returns...
			#
			$rows = getDatabase()->all(" select r.id retid,c.year,r.filename,c.* from candidate_return r join candidate c on c.id = r.candidateid order by c.year,c.last,c.first ");
			$returns = array();
			foreach ($rows as $r) {
				$dir = self::getReturnPagesDir($r['year'],$r['filename']);
				if (!file_exists($dir)) { continue; }
				$returns[] = $r;
			}
			?>
			<h1>Returns to process: <?php print count($returns); ?></h1>
	    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
			<?php
			foreach ($returns as $r) {
				?>
				<tr>
				<td><?php print $r['year']; ?></td>
				<td><?php print $r['last']; ?></td>
				<td><?php print $r['first']; ?></td>
				<td><a href="/election/processReturn/<?php print $r['retid']; ?>"><?php print $r['filename']; ?></a></td>
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

		$page = $_GET['page'];
		$png = $_GET['png'];
    if ($_GET['saveA'] == 1) {	
			# click in a <canvass> denoting location of a campaign donation
      $values = array();
			$values['returnid'] = $id;
      $values['x'] = $_GET['x'];
      $values['y'] = $_GET['y'];
      $values['page'] = $page;
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
	      imageObj.src = '<?php print "?png=1&page=$page"; ?>';
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
		top();

		# select a random unprocessed donation, along with the X/Y of the next donation on the same
		# page, if any, for bounding box purposes.
		$row = getDatabase()->one(" 
			select
				d.*
			from 
				candidate_donation d
			where 
				d.amount is null 
			order by rand()
			limit 1
		");
		$next = getDatabase()->one(" select min(y) y from candidate_donation where returnid = {$row['returnid']} and page = {$row['page']} and y > {$row['y']} ");

		$ret = getDatabase()->one(" select c.*,r.filename from candidate_return r join candidate c on c.id = r.candidateid where r.id = {$row['returnid']} ");
		$pages = self::getReturnPages($ret['year'],$ret['filename']);

		$page = $row['page'];
		$pagefile = $pages[$page];
    $size = getimagesize($pagefile);
    $imgW = $size[0];
		if (isset($next['y'])) {
			$imgH = $next['y']-$row['y']+5;
		} else {
	    $imgH = 200;
		}

		# use canvas to display the image
		?>
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
			context.drawImage(imageObj,0,-<?php print $row['y']; ?>);
		};
		imageObj.src = '/election/processReturn/<?php print "{$row['returnid']}?png=1&page=$page"; ?>';
		</script>
		<form method="post" action="/election/processDonation/">
		<input type="hidden" name="id" value="<?php print $row['id']; ?>"/>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
		<tr>
		<td style="vertical-align: top; width: 400px;"><input  style="width: 90%;" type="text" placeholder="name" name="name" /><br/>
		If a corporation or union is shown, put that in the <b>NAME</b> field and ignore any personal names shown.
		</td>
		<td style="vertical-align: top; width: 350px;"><input  style="width: 90%;" type="text" placeholder="address" name="address" />
		Just street address (and unit/apt).
		</td>
		<td style="vertical-align: top; width: 100px;"><input  style="width: 90%;" type="text" value="Ottawa" placeholder="city" name="city" />
		Leave as Ottawa for pre-almalgamation names.
		</td>
		<td style="vertical-align: top; width: 50px;"><input  style="width: 90%;" type="text" value="ON" placeholder="prov" name="prov" />
		Nothing should come in from out-of-province
		</td>
		<td style="vertical-align: top; width: 100px;"><input  style="width: 90%;" type="text" placeholder="postal" name="postal" />
		Postal code is really important for later geo-location reports!
		</td>
		<td style="vertical-align: top; width: 100px;"><input  style="width: 90%;" type="text" placeholder="$" name="amount" />
		</td>
		</tr>
		</table>
		<input class="btn btn-large btn-success" type="submit" value="save"/>
		</form>
		<h3>Instructions</h4>
		<p>
		You are looking at a single donation record from a 2010 campaign return! This is your chance to digitize it for better transparency and accountability.
		</p>
		</center>
		<?php
		bottom();
	}

	public static function processDonationSave() {
		// straight to DB, back to GET
		db_update('candidate_donation',$_POST,'id');
		header("Location: /election/processDonation/");
	}

}

?>
