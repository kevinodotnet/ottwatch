<?php

/*

Populate candidate table from electedofficials table:

  delete from candidate where incumbent = 1;
  insert into candidate (year,ward,   first,last,nominated,incumbent) 
  select                 2014,case when wardnum > 0 then wardnum else 0 end,first,last,null,     1 from electedofficials
  ;

*/

class ElectionController {

  const year = 2014;

  public static function isRaceOn() {
    $now = date('Y-m-d');
    $raceon = strtotime('2014-01-02');
    $now = time();
    if ($raceon > time()) {
      return false;
    }
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
      order by ward,last,first,middle ",array('ward'=>$race,'year'=>self::year));
    ?>
    <div class="row-fluid">
    <div class="span8">
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
    </div>

    <?php
    $incumbent = getDatabase()->one("select * from candidate where ward = :ward and year = :year and incumbent = 1 ",array('ward'=>$race,'year'=>self::year));
    ?>

    <div class="span4">
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
    </table>

    </div>
    </div><!-- /row -->

    <div class="row-fluid">
    <div class="span8">
    <h2>Discussion</h2>
    <p>
    The discusson thread below will remain open for the entire 2014 year.
    </p>
    <p>
    Comments are not moderated in advance, but @odonnell_k reads them all eventually. Be civil or find another website to exercise your free speech.
    <b>In particular:</b> any hint of misogyny towards women (candidates, or other commenters), will not be tolerated. 
    </p>
    <?php disqus(); ?>
    </div>
    </div>
    <?php

    bottom();
  }
  public static function showMain() {
    top();
    ?>
    <h1>Election <?php print self::year; ?></h1>
    <?php
    $wards = getDatabase()->all(" select distinct(wardnum) wardnum from electedofficials where wardnum is not null and wardnum != '' order by ward, wardnum + 0 ");
    $count = 0;
    array_unshift($wards,array('wardnum'=>0));
    foreach ($wards as $ward) {
      if ($prevWard != $ward['wardnum']) {
        if ($count == 0) {
          ?>
          <div class="row-fluid">
          <?php
        }
        if (++$count % 3 == 0) {
          ?>
          </div><!-- row -->
          <div class="row-fluid">
          <?php
        }
      }
      $prevWard = $ward['wardnum'];

      $raceLink = OttWatchConfig::WWW . "/election";
      if ($ward['wardnum'] == 0) {
        # special case
        $wardInfo = array('ward'=>'Mayor');
        $raceLink .= "/mayor/";
      } else {
        $wardInfo = getApi()->invoke('/api/wards/'.$ward['wardnum']);
        $raceLink .= "/ward/{$ward['wardnum']}";
      }
      ?>
      <div class="row-fluid">
      <div class="span4">
      <h2><a href="<?php print $raceLink; ?>"><?php print "{$wardInfo['ward']}"; ?></a></h2>
      <?php
      $rows = getDatabase()->all("select * from candidate where ward = :ward and year = :year and nominated is not null order by ward,last,first,middle ",array('ward'=>$ward['wardnum'],'year'=>self::year));
      if (count($rows) == 0) {
        print "<i>No Candidates Registered Yet</i>\n";
      }
      foreach ($rows as $row) {
        print "{$row['last']}, {$row['first']}";
        if ($row['incumbent']) { print " *"; }
        print "<br/>\n";
      }
      ?>
      </div><!-- /ward -->
      <?php

      continue;
      
    }
    ?>
    </div><!-- /lastward -->
    </div><!-- /lastrow -->

    <?php
    bottom();
  }

}

?>
