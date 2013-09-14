<?php

/*

A quick API tester. Pass in API url as first argument.
Retrieves JSON and then pretty prints it.

*/

$d = file_get_contents($argv[1]);
$c = json_decode($d);

$cols = $c[0]->result->columns;
#print_r($cols);

foreach ($c[0]->result->rows as $row) {
  $hood = array();
  for ($x = 0; $x < count($cols); $x++) {
    $hood[$cols[$x]] = $row[$x];
  }
  #print_r($row);
  $hood['geometry'] = $hood['geometry']->geometry;
  print_r($hood);
  exit;
}

?>
