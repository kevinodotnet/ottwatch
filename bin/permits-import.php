<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
require_once('include.php');

include 'PHPExcel/IOFactory.php';

$data = file_get_contents("http://app06.ottawa.ca/en/city_hall/statisticsdata/opendata/info/constr_demo_pool_permits/index.htm");
#$data = file_put_contents("permits-index.html",$data);
#$data = file_get_contents("permits-index.html");

$lines = explode("<a ",$data);
foreach ($lines as $l) {
  $matches = array();
  if (preg_match('/href="(.*xls)">([^<]+)</',$l,$matches)) {
    $url = $matches[1];
    $title = $matches[2];
    $url = "http://app06.ottawa.ca{$url}";
    injestXLS($url,$title);
  }
}

#injestXLS("http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mzq1/~edisp/cap348601.xls","February 2013");
#injestXLS("http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mzy5/~edisp/cap365801.xls","March 2013");
#injestXLS("file:///Users/kevino/aug2012.xls","August 2012");

function injestXLS ($url,$title) {

#  if (!preg_match('/2011/',$title)) { return; }

  print "INJESTING: $title :: $url\n";

  $data = file_get_contents($url);
	$data = file_put_contents("permits.xls",$data);
	#$data = file_get_contents("permits.xls");

  $objReader = PHPExcel_IOFactory::createReader('Excel5');
  $excel = $objReader->load("permits.xls");
	unlink("permits.xls");

  $names = $excel->getSheetNames();
  for ($x = 0; $x < $excel->getSheetCount(); $x++) {
    if (strtolower($names[$x]) != 'details') {
      continue;
    }
    $rows = $excel->getSheet($x)->toArray(null,true,true,true);
    $skip = 1;
    $permits = array();
    foreach ($rows as $r) {
      $line = implode(" :: ",$r);
      if (preg_match("/Street.*Street.*Postal/",$line)) {
        # header row of actual data
        $header = $r;
        # confirm headers are the same between files; could change over time.
        $t = array_values($header);
        asort($t);
        $t = implode("::",$t);
        if ($t != 'Application Type::Building Type::Contractor::D.U.::Description::Lot Number::Municipality::Permit Issued Date::Permit Number::Plan Number::Postal Code::Sq. Ft.::Street Name::Street Number::Value::Ward') {
          print "ERROR: column names have changed!\n";
          exit;
        }
        $skip = 0;
        continue;
      }
      if (!$skip) {
        $permit = array();
        foreach ($header as $k => $v) {
          $permit[$v] = $r[$k];
        }
        if ($permit['Permit Number'] != '') {
          $permits[] = $permit;
        }
      }
    }
    foreach ($permits as $p) {

      # "Ward 3" becomes "3"
      $p['Ward'] = preg_replace("/Ward /",'',$p['Ward']);
      if ($p['Ward'] == '') {
        $p['Ward'] = 0;
      }
      # Excel to Unix time.
      if (preg_match('/^201\d\//',$p['Permit Issued Date'])) {
        # convert from string date
        # seems to only apply to outlyer August 2012 :(
        $p['Permit Issued Date'] = strtotime($p['Permit Issued Date']);
      } else {
        $p['Permit Issued Date'] = ($p['Permit Issued Date'] - 25569) * 86400;
      }

      print "  {$p['Permit Number']} :: {$p['Street Number']} {$p['Street Name']} :: {$p['Building Type']}\n ";

			# [Street Number] => 420  
			# [Street Name] => VIA VERONA AVE 
			# [Postal Code] => 
			# [Ward] => Ward 3
			# [Plan Number] => 
			# [Lot Number] => 18-19
			# [Contractor] => CAMPANALE HOMES
			# [Building Type] => Rowhouse
			# [Municipality] => Nepean
			# [Description] => Finish the basement in a 2 storey rowhouse
			# [D.U.] => 0
			# [Value] => 6575
			# [Sq. Ft.] => 263
			# [Permit Number] => 1300563
			# [Application Type] => Construction
			# [Permit Issued Date] => 41306.407210648

      # ignore duplicate key
      getDatabase()->execute("
        insert into permit (st_num, st_name, postal, ward, plan_num, lot_num, contractor, building_type, description, du, value, area, permit_number, app_type, issued_date)
        values (:st_num, :st_name, :postal, :ward, :plan_num, :lot_num, :contractor, :building_type, :description, :du, :value, :area, :permit_number, :app_type, from_unixtime(:issued_date))
        on duplicate key update st_num = st_num
        ",array(
					'st_num' => $p['Street Number'],
					'st_name' => $p['Street Name'],
					'postal' => $p['Postal Code'],
					'ward' => $p['Ward'],
					'plan_num' => $p['Plan Number'],
					'lot_num' => $p['Lot Number'],
					'contractor' => $p['Contractor'],
					'building_type' => $p['Building Type'],
					'description' => $p['Description'],
					'du' => $p['D.U.'],
					'value' => $p['Value'],
					'area' => $p['Sq. Ft.'],
					'permit_number' => $p['Permit Number'],
					'app_type' => $p['Application Type'],
					'issued_date' => $p['Permit Issued Date']
      ));
    }
  }
}

# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mty4/~edisp/odata0070.xls,July 2011
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mty4/~edisp/odata0066.xls,August 2011
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mty4/~edisp/odata0076.xls,September 2011
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mty4/~edisp/odata0075.xls,October 2011
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mty4/~edisp/odata0074.xls,November 2011
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mty4/~edisp/odata0067.xls,December 2011
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mty4/~edisp/odata0069.xls,January 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mty4/~edisp/odata0068.xls,February 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mty4/~edisp/odata0072.xls,March 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mty4/~edisp/odata0065.xls,April 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mty4/~edisp/odata0343.xls,May 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mtcx/~edisp/cap181001.xls,June 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mtk0/~edisp/cap205201.xls,July 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mjqy/~edisp/cap251205.xlsx,August 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mjcw/~edisp/cap278006.xls,September 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mzax/~edisp/cap306201.xls,October 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mze1/~edisp/cap320201.xls,November 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mzix/~edisp/cap324801.xls,December 2012
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mzmw/~edisp/cap335601.xls,January 2013
# http://app06.ottawa.ca/cs/groups/content/@webottawa/documents/pdf/mdaw/mzq1/~edisp/cap348601.xls,February 2013

exit();

?>
