<?php

class MfippaController {

  public static function pdfToPages($mfippa_id) {

    $pdffile = OttWatchConfig::FILE_DIR."/mfippa/{$mfippa_id}.pdf";
    $pagesdir = OttWatchConfig::FILE_DIR."/mfippa/{$mfippa_id}";

    if (!file_exists($pdffile)) {
      print "ERROR: pdf file not found: $pdffile\n";
      return;
    }

    # delete any calculated data for this PDF, then start fresh directory
    if (file_exists($pagesdir)) {
      $d = opendir($pagesdir);
      while (($file = readdir($d)) !== false) {
        if (preg_match('/^\./',$file)) { continue; }
        unlink("$pagesdir/$file");
      }
      closedir($d);
      rmdir($pagesdir);
    } 
    mkdir($pagesdir);

    # create PNG pages
    $cmd = " pdftoppm ";
    $cmd .= " -png ";
    $cmd .= " '$pdffile' ";
    $cmd .= " '$pagesdir/page' ";
    system($cmd);

  }

}


?>
