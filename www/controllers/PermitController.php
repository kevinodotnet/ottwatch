<?php

include_once '../../lib/include.php';
include_once '../../lib/phpexcel/Classes/PHPExcel.php';
include_once '../../lib/phpexcel/Classes/PHPExcel/Reader.php';
include_once '../../lib/phpexcel/Classes/PHPExcel/IOFactory.php';

PermitController::downloadExcelFiles();

class PermitController {

  public static function downloadExcelFiles() { 

    $url = "http://app06.ottawa.ca/en/city_hall/statisticsdata/opendata/info/constr_demo_pool_permits/index.htm";
    #$html = file_get_contents($url);
    #file_put_contents("od.html",$html);
    $html = file_get_contents("od.html");
    $lines = explode("\n",$html);
    foreach ($lines as $l) {
      if (preg_match("/This table contains information about Construction/",$l)) {
        $links = explode("<a ",$l);
        foreach ($links as $l) {
          $matches = array();
          if (preg_match('/href="([^"]+)".*xls/i',$l,$matches)) {
            $href = $matches[1];
            if (!preg_match("/Jan.*2013/",$l)) {
              # TODO: temp
              continue;
            }
            $url = "http://app06.ottawa.ca$href";
            print "url: $url\n";
            #$xls = file_get_contents($url);
            #file_put_contents("file.xls",$xls);
            $xls = file_get_contents("file.xls");
            $phpExcel = PHPExcel_IOFactory::load('file.xls');
            $objWorksheet = $phpExcel->getActiveSheet();
            print print_r($objWorksheet);
          }
        }
      }
    }
  }
  
}

?>
