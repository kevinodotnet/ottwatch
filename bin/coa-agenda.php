<?php

parseCoaTxt($argv[1]);

function parseCoaTxt($file) {

  $txt = file_get_contents($file);

  $apps = array();
  $a = array();

  $pages = explode(chr(12),$txt); # split on CTRL-L, which PDF2TEXT puts between pages
  foreach ($pages as $p) {
    $p = preg_replace("/\r/","",$p);
    $o = preg_replace("/\n/"," ",$p);
    if (preg_match('/File No\.:/',$o) || preg_match('/File Nos\.:/',$o)) {
      if (count($a) > 0) {
        $apps[] = array('lines'=>$a);
        $a = array();
      }
    }
    $lines = explode("\n",$p);
    foreach ($lines as $l) {
      $a[] = $l;
    }
  }

  # first one is always the agenda
  array_shift($apps);

  foreach ($apps as $a) {
    #print_r($a);
    $head = "";
    foreach ($a['lines'] as $l) {
      $l = substr($l,0,150);
      if (preg_match('/PURPOSE OF THE APPLICATION/',$l)) {
        break;
      }
      $head .= $l;
    }
    $text =  implode(" ",$a['lines']);
    $matches = array();
    preg_match_all("/D\d\d-\d\d-\d\d\/.-\d\d\d\d\d/",$head,$matches);
    #print_r($matches);
    foreach ($matches[0] as $d) {
      print " update devapp set coadesc = '".preg_replace("/'/","''",implode("<br/>",$a['lines']))."' where devid = '$d'; \n";
      #print " update devapp set coadesc = 'foo' where devid = '$d'; \n";
    }
    continue;

    print "\n\n$text\n\n";
    print_r($matches);
    exit;

    $lines = explode(" ",implode(" ",$a['lines']));
    $devids = preg_grep("/D\d\d-\d\d-\d\d\/.-\d\d\d\d/",$lines);
    # print "$text\n\n";
    #print_r($devids);
    foreach ($devids as $d) {
    }
  }

}
