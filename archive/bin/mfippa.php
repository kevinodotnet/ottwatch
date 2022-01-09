<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

if ($argv[1] == 'pdfToPages') {
  $mfippa_id = $argv[2]; # ie: A-2013-00594
  MfippaController::pdfToPages($mfippa_id);
  return;
}

if ($argv[1] == 'createImg') {
  $id = $argv[2];
  MfippaController::createImg($id);
  return;
}

if ($argv[1] == 'summaryOCR') {
  $id = $argv[2];
  MfippaController::summaryOCR($id);
  return;
}

# $p = MfippaController::getPageFiles('A-2013-00594');
# pr($p);

