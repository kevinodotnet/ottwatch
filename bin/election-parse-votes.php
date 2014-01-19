<?php

$start = 1;
$handle = fopen($argv[1], "r");
$header = array();
while (($buffer = fgets($handle, 4096)) !== false) {
  $buffer = preg_replace("/\r/",'',$buffer);
  $buffer = preg_replace("/\n/",'',$buffer);
  $vals = explode(",",$buffer);
  if ($start) {
    $header = $vals;
    $start = 0;
    #print "------------------------------------\n";
    #print implode(" XX ",$header)."\n";
    continue;
  }
  if ($vals[0] == '') {
    # empty line between race results
    #print_r($row);
    $start = 1;
    continue;
  }
  $row = array();
  for ($x = 1; $x < count($header); $x++) {
    if ($header[$x] == '') { continue; }
    $ward = $header[0];
    $matches = array();
    if (preg_match('/Mayor/',$header[0],$matches)) {
      $ward = 0;
    } else if (preg_match('/Councillor.*(\d+)$/',$header[0],$matches)) {
      $ward = $matches[1];
    } else {
      $ward = "SKIP {$header[0]}";
    }

    #print " insert into votes (year,vot_location_name,candidate_name,votes) values (2010,{$header[0]},{$header[$x]},$vals[$x]); \n";
    #print_r($header);
    #print_r($vals);
    print " insert into votes (year,vot_location_name,candidate_name,votes) values (2010,{$ward},'{$vals[0]}','{$header[$x]}',{$vals[$x]}); \n";

    #$row[$header[$x]] = $vals[$x];
  }
  #print implode(" :: ",$vals)."\n";
}
fclose($handle);
#print_r($row);

?>
