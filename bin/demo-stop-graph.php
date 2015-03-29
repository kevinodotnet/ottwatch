<?php

# stops: stop_id,stop_code,stop_name,stop_desc,stop_lat,stop_lon,zone_id,stop_url,location_type
# stop_times: trip_id,arrival_time,departure_time,stop_id,stop_sequence,pickup_type,drop_off_type

$stops = readStops();
$edges = readEdges();

print '<?xml version="1.0" encoding="UTF-8"?>
<kml 
  xmlns="http://www.opengis.net/kml/2.2"
  xmlns:gx="http://www.google.com/kml/ext/2.2"
  >
<Document>

<Style id="yellowLineGreenPoly"> 
<LineStyle> 
<color>7f00ffff</color> 
<width>10</width> 
</LineStyle> 
<PolyStyle> 
<color>7f00ffff</color> 
</PolyStyle> 
</Style> 

';

foreach ($edges as $e) {
  $from = $stops[$e->from];
  $to = $stops[$e->to];
  $c = $e->c / 11832 * 2000;
#  $c = $c*10;
  /*
  print "$c,$from,$to\n";
  print_r($e);
  print_r($from);
  print_r($to);

  stop_lat
  stop_lon
    {$from['stop_lat']}
    {$from['stop_lon']}
    {$to['stop_lat']}
    {$to['stop_lon']}
  */

  print "
<Placemark>
  <styleUrl>#yellowLineGreenPoly</styleUrl>
                  <gx:balloonVisibility>1</gx:balloonVisibility> 

<LineString id=\"{$e->from}-{$e->to}\">
  <extrude>1</extrude>
  <tessellate>1</tessellate>                <!-- boolean -->
  <altitudeMode>relativeToGround</altitudeMode>
  <coordinates>
    {$from['stop_lon']},{$from['stop_lat']},$c
    {$to['stop_lon']},{$to['stop_lat']},$c
  </coordinates>
</LineString>
</Placemark>
  ";

}

print '

</Document>
</kml>

';

################################################################################################################
################################################################################################################

function readStops() {
  $stops = array();
	$f = fopen("stops.txt","r");
	$line = fgets($f);
	$head = str_getcsv($line);
	while (($line = fgets($f)) !== false) {
	  $csv = str_getcsv($line);
	  $row = array();
	  for ($x = 0; $x < count($head); $x++) {
	    $row[$head[$x]] = $csv[$x];
	  }
    $stops[$row['stop_id']] = $row;
	}
  fclose($f);
  return $stops;
}

function readEdges() {

  $edges = array();

  if (file_exists('edges.json')) {
    return json_decode(file_get_contents('edges.json'));
  }

	$f = fopen("stop_times.txt","r");
	$line = fgets($f);
	$head = str_getcsv($line);
	$l = 0;
	while (($line = fgets($f)) !== false) {
	#  if ($l++ > 10000) { break; }
	#  if ($l++ % 100 == 0) { print " ($l)\n"; }
	  $csv = str_getcsv($line);
	  $row = array();
	  for ($x = 0; $x < count($head); $x++) {
	    $row[$head[$x]] = $csv[$x];
	  }
	
	
	  if ($row['stop_sequence'] > 1) {
	
	    $from = $prev['stop_id'];
	    $to = $row['stop_id'];
	    $e = $edges["$from-$to"];
	    if (!isset($e)) {
	      $e = array(
	        'from' => $from,
	        'to' => $to,
	        'c' => 0
	      );
	    }
	    $e['c'] ++;
	    $edges["$from-$to"] = $e;
	  }
	
	  $prev = $row;
	}
	
	$json = json_encode($edges);
	file_put_contents("edges.json",$json);
  return json_decode($json);
}
