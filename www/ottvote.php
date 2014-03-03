<pre>
<span id="progress">Initializing...</span>
<?php

$hoods = array();
$hoods['15-001.3'] = 'broadview';
$hoods['15-002.2'] = 'broadview';
$hoods['15-007.1'] = 'champlainpark';
$hoods['15-012.1'] = 'civichospital';
$hoods['15-013.1'] = 'civichospital';
$hoods['15-013.2'] = 'civichospital';
$hoods['15-013.3'] = 'civichospital';
$hoods['15-013.4'] = 'civichospital';
$hoods['15-013.5'] = 'civichospital';
$hoods['15-013.6'] = 'civichospital';
$hoods['15-006.1'] = 'hamptoniona';
$hoods['15-006.2'] = 'hamptoniona';
$hoods['15-006.3'] = 'hamptoniona';
$hoods['15-006.4'] = 'hamptoniona';
$hoods['15-007.4'] = 'hintonburg';
$hoods['15-008.4'] = 'hintonburg';
$hoods['15-009.1'] = 'hintonburg';
$hoods['15-009.2'] = 'hintonburg';
$hoods['15-009.3'] = 'hintonburg';
$hoods['15-010.1'] = 'hintonburg';
$hoods['15-010.2'] = 'hintonburg';
$hoods['15-010.3'] = 'hintonburg';
$hoods['15-010.4'] = 'hintonburg';
$hoods['15-010.5'] = 'hintonburg';
$hoods['15-011.1'] = 'hintonburg';
$hoods['15-015.1'] = 'hintonburg';
$hoods['15-004.1'] = 'islandpark';
$hoods['15-002.4'] = 'maitland';
$hoods['15-003.1'] = 'maitland';
$hoods['15-001.1'] = 'mckellarpark';
$hoods['15-001.2'] = 'mckellarpark';
$hoods['15-002.1'] = 'mckellarpark';
$hoods['15-009.4'] = 'mechanicsville';
$hoods['15-009.5'] = 'mechanicsville';
$hoods['15-014.1'] = 'royalottawa';
$hoods['15-007.2'] = 'westwellington';
$hoods['15-007.3'] = 'westwellington';
$hoods['15-008.1'] = 'westwellington';
$hoods['15-008.2'] = 'westwellington';
$hoods['15-008.3'] = 'westwellington';
$hoods['15-002.3'] = 'westboro';
$hoods['15-004.3'] = 'westboro';
$hoods['15-005.1'] = 'westboro';
$hoods['15-005.2'] = 'westboro';
$hoods['15-005.3'] = 'westboro';
$hoods['15-005.4'] = 'westboro';
$hoods['15-004.2'] = 'westborobeach';
$hoods['15-004.4'] = 'westborobeach';

$out = array();

if (isset($_POST['csv'])) {
  $csv = $_POST['csv'];
  $csv = preg_replace("/\r/","",$csv);
  $lines = explode("\n",$csv);
  $out[] = $lines[0]."\tWard\tPoll2010\tNeighbourhood";
  $header = explode("\t",$lines[0]);
  array_shift($lines);
  $count = 0;
  foreach ($lines as $l) {
    $count++;
    if ($count % 5 == 0) {
	    ?><script>
	    document.getElementById('progress').innerHTML = '<?php print $count; ?> of <?php print count($lines); ?> completed';
	    </script><?php
    }
    flush();
    # print "$count of ".count($lines)." lines...\n"; flush();
    if ($l == '') { continue; }
    $outline = '';
    #print "line: $l\n";
    $csv = explode("\t",$l);
    $row = array();
    for ($x = 0; $x < count($header); $x++) {
      $outline .= $csv[$x]."\t";
      $row[$header[$x]] = $csv[$x];
    }
    $lat = $row['Geo Code 1'];
    $lon = $row['Geo Code 2'];
    $url = "http://dev.ottwatch.ca/api/point?lat={$lat}&lon={$lon}";
    $d = file_get_contents($url);
    $c = json_decode($d);
    if (isset($c->ward->ward)) {
	    #print_r($c);
	    $row['ward'] = $c->ward->ward;
	    $polls = get_object_vars($c->polls);
	    $row['poll2010'] = $polls['2010'];
      $outline .= $row['ward'];
      $outline .= "\t";
      $outline .= $row['poll2010'];
      $outline .= "\t";
      $outline .= $hoods[$row['poll2010']];
    }

    $out[] = $outline;

    #print_r($c);
    #$row['poll2010'] = $c['polls']['2010'];
    #print_r($row);
  }
  # Geo Code 1  Geo Code 2
}

?>
</pre>

<form method="post">
<input type="submit"><br/>
<textarea name="csv" cols="300" rows="20"><?php print implode("\n",$out); ?></textarea>
</form>
