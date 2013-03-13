<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

# purge any existing data
getDatabase()->execute(" delete from electedofficials ");

$cols = array();
$cols[] = 'District name';
$cols[] = 'District ID';
$cols[] = 'Elected office';
$cols[] = 'First name';
$cols[] = 'Last name';
$cols[] = 'Gender';
$cols[] = 'Party name';
$cols[] = 'Email';
$cols[] = 'URL';
$cols[] = 'Photo URL';
$cols[] = 'Personal URL';
$cols[] = 'Office type';
$cols[] = 'Address line 1';
$cols[] = 'Address line 2';
$cols[] = 'Locality';
$cols[] = 'Postal code';
$cols[] = 'Province';
$cols[] = 'Phone';
$cols[] = 'Fax';

$data = file_get_contents("opendata_electedofficial_2010_2014.csv");
$data = preg_replace("/\r/","",$data);
$lines = explode("\n",$data);
$first = 1;
foreach ($lines as $line) {
  $csv = explode(",",$line);
  if ($first) {
    # burn first row
    $first = 0;
    continue;
  }
  if ($line == '') {
    continue;
  }

  $row = array();
  for ($x = 0; $x < count($cols); $x++) {
    $row[$cols[$x]] = $csv[$x];
  }

  getDatabase()->execute(" 
    insert into electedofficials 
    (ward,wardnum,office,first,last,email,url,photourl,phone)
    values
    (:ward,:wardnum,:office,:first,:last,:email,:url,:photourl,:phone)
    ",array(
      'ward' => $row['District name'],
      'wardnum' => $row['District ID'],
      'office' => $row['Elected office'],
      'first' => $row['First name'],
      'last' => $row['Last name'],
      'email' => $row['Email'],
      'url' => $row['URL'],
      'photourl' => $row['Photo URL'],
      'phone' => $row['Phone'],
  ));

}

?>
