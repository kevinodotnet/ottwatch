<?php

/*

A quick API tester. Pass in API url as first argument.
Retrieves JSON and then pretty prints it.

*/

$d = file_get_contents($argv[1]);
$c = json_decode($d);
print print_r($c);

