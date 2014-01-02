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
      strokeColor: '#ff0000',
      fillColor: '#ff0000',
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
		print "<h1>$title</h1>\n";

    $rows = getDatabase()->all("
      select * 
      from candidate 
      where ward = :ward and year = :year and nominated is not null 
      order by ward,nominated desc,last,first,middle ",array('ward'=>$race,'year'=>self::year));
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
	      <th>Email</th>
	      <th>Phone</th>
	      <th>Twitter</th>
	      <th>Facebook</th>
	      <th>Registered</th>
	    </tr>
	    <?php
	    foreach ($rows as $r) {
	      ?>
	      <tr>
	        <td>
	          <?php print "{$r['last']}, {$r['first']} {$r['middle']}"; ?>
	          <?php if ($r['incumbent'] == TRUE) { print "*"; } ?>
	        </td>
	        <td>
	        <a target="_blank" href="<?php print $r['url']; ?>"><?php print $r['url']; ?></a>
	        </td>
	        <td>
	        <a target="_blank" href="mailto:<?php print $r['email']; ?>?Subject=Election 2014"><?php print $r['email']; ?></a>
	        </td>
					<td>
					<?php print $r['phone']; ?>
					</td>
	        <td>
          <?php if ($r['twitter'] != '') { ?>
          <a href="https://twitter.com/<?php print $r['twitter']; ?>" class="twitter-follow-button" data-show-count="false" data-lang="en"><?php print $r['twitter']; ?></a>
          <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
          <?php } ?>
	        </td>
	        <td>
          <?php if ($r['facebook'] != '') { ?>
	        <a target="_blank" href="<?php print $r['facebook']; ?>"><i class="icon-share"></i> facebook</a>
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
	          print "<p>Has not (yet) registered as a candidate.</p>";
	          if ($incumbent['twitter'] != '') {
	            ?>
	            <p>The incumbent is on Twitter. Hit the tweet button to ask them if they are running again, and when they plan to register as a candidate:<br/>
	            <a href="https://twitter.com/share" class="twitter-share-button" 
	              data-via="ottwatch"
	              data-text=".@<?php print $incumbent['twitter']; ?> are you running again? When will you be officially registered?"
								data-hashtags="ottvote"
	              data-lang="en"
	              >.@<?php print $incumbent['twitter']; ?> are you running again? When will you be officially registered?</a>
	            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	            </p>
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
    </table>
    <?php 
    if ($race > 0) { 
      ?>
	    <h2>Map (<a href="/election/ward/<?php print $race; ?>/map">fullsize</a>)</h2>
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

      $rows = getDatabase()->all("select * from candidate where ward = :ward and year = :year and nominated is not null order by ward,last,first,middle ",array('ward'=>$ward['wardnum'],'year'=>self::year));
      ?>
      <div class="span3">
      <h3><a href="<?php print $raceLink; ?>"><?php print "{$wardInfo['ward']}"; if (count($rows) > 0) { print ' ('.count($rows).')'; } ?></a></h3>
      <?php
      if (count($rows) == 0) {
        print "<i style=\"color: #c0c0c0;\">No Candidates Registered Yet</i>\n";
      }
      foreach ($rows as $row) {
        print "{$row['last']}, {$row['first']}";
        if ($row['incumbent']) { print " *"; }
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

}

?>
