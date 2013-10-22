<?php

class ElectionController {

  const year = 2014;

  public static function showRace($race) {
    top();
    if ($race == 'mayor') { $race = 0; }
    $rows = getDatabase()->all("select * from candidate where ward = :ward and year = :year order by ward,last,first,middle ",array('ward'=>$race,'year'=>self::year));
    if (count($rows) == 0) {
      print "<i>No Candidates Registered Yet</i>\n";
    }
    #pr($rows);
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
      $rows = getDatabase()->all("select * from candidate where ward = :ward and year = :year order by ward,last,first,middle ",array('ward'=>$ward['wardnum'],'year'=>self::year));
      if (count($rows) == 0) {
        print "<i>No Candidates Registered Yet</i>\n";
      }
      foreach ($rows as $row) {
        print "{$row['id']}<br/>\n";
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
