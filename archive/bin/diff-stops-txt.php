<?php


$stops = array();

$s1 = readstops("s1");
$s2 = readstops("s2");
foreach ($s1 as $s) { $stops[$s['stop_code']] = 1; }
foreach ($s2 as $s) { $stops[$s['stop_code']] = 1; }

// print_r($s1); print_r($s2); print_r($stops);

$results = array();

print "ind1,ind2,geo_same,geo_distance,code,s1lat,s1lon,s2lat,s2lon\n";

foreach (array_keys($stops) as $code) {

  $d1 = $s1[$code];
  $d2 = $s2[$code];

  $result = array();
  $result['stop_code'] = $code;

  $ind1 = isset($d1) ? 1 : 0;
  $ind2 = isset($d2) ? 1 : 0;

  $geo_same = 0;
  if ($ind1 && $ind2) {
    // print_r($d1);
    $geo_same = $d1['stop_md5'] == $d2['stop_md5'] ? 1 : 0;
    $geo_distance = 0;
    if ($geo_same == 0) {
      $geo_distance = latlon_distance($d1['stop_lat'], $d1['stop_lon'],$d2['stop_lat'], $d2['stop_lon']);
      if ($geo_distance < 10) {
        // treat 10m and less as 0
        $geo_same = 1;
        $geo_distance = 0;
      }
    }
  }


  print "$ind1,$ind2,$geo_same,$geo_distance,$code,{$d1['stop_lat']},{$d1['stop_lon']},{$d2['stop_lat']},{$d2['stop_lon']}\n";
  if ($ind1 && $ind2 && $geo_same == 0 && $geo_distance < 500 ) {
  }



}

function readstops($file) {
  $csv = file_get_contents($file);
  $lines = explode("\n",$csv);
  $header = explode(",",array_shift($lines));

  $stops = array();

  foreach ($lines as $line) {
    $values = explode(",",$line);
    if ($line == '') { continue; }

    $row = array();
    for ($x = 0; $x < count($header); $x++) {
      $row[$header[$x]] = trim($values[$x]);
      if ($header[$x] == 'stop_lat' || $header[$x] == 'stop_lon') {
        $row[$header[$x]] = trim($values[$x],' 0');
      }
      $row[$header[$x]] = preg_replace('/^0/','',$row[$header[$x]]);
    }

    if ($row['stop_code'] < 1000) {
      $row['stop_code'] = '0'.($row['stop_code']+0);
    }

    $row['stop_md5'] = md5($row['stop_lat'].$row['stop_lon']);

    $stops[$row['stop_code']] = $row;

    /* 
    if ($row['stop_id'] == 'AA030') {
      print_r($row);
      return;
    }
    */
  }

  return $stops;
}

function latlon_distance($lat1, $lon1, $lat2, $lon2) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);
 
  $unit = "m";
  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "m") {
    return ($miles * 1.609344 * 1000);
  } else if ($unit == "N") {
    return ($miles * 0.8684);
  } else {
    return $miles;
  }

}


