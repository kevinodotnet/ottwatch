<?php

error_reporting(E_ALL);

class ConsultationController {

  // main entry point for crawling public consultations

  public static function crawlConsultations() {
    # start at the stop level consultation listing.
    $html = file_get_contents("http://ottawa.ca/en/city-hall/public-consultations");
    $html = strip_tags($html,"<a>");
    $matches = array();
    preg_match_all("/<a[^h]+href=\"([^\"]+)\"[^>t]+title=\"([^\"]+)\"[^<]+<\/a>/",$html,$matches);
    #print_r($matches);
    for ($x = 0; $x < count($matches[0]); $x++) {
      if (! preg_match('/Learn/',$matches[2][$x])) { continue; }
      $matches[2][$x] = preg_replace('/Learn more about /','',$matches[2][$x]);
      $url = $matches[1][$x];
      $category = $matches[2][$x];
      self::crawlCategory($category,$url);
      #exit;
    }
  }

  // crawl a category for its consultations

  public static function crawlCategory ($category, $url) {
    #print "CATEGORY: $category, URL: $url\n";
    $html = file_get_contents("http://ottawa.ca{$url}");
    $html = preg_replace("/\n/"," ",$html);
    $html = strip_tags($html,"<a>");
    preg_match_all("/<a[^h]+href=\"\/en\/city-hall([^\"]+public-consultations[^\"]+)\"[^>]*>([^<]*)<\/a>/",$html,$matches);
    #print_r($matches);
    for ($x = 0; $x < count($matches[0]); $x++) {
      $url = $matches[1][$x];
      $url = "http://ottawa.ca/en/city-hall{$url}";
      $title = $matches[2][$x];
      #$title = utf8_decode($title);
      $title = html_entity_decode($title,ENT_QUOTES);
      self::crawlConsultation($category,$title,$url);
    }
  }

  // crawl a specific consultation 

  public static function crawlConsultation ($category, $title, $url) {

    print "---------------------------------------\n";
    print "CATEGORY: $category\n";
    print "TITLE: $title\n";
    print "URL: $url\n";

    #print "$url :: $title\n";
    $html = file_get_contents($url);
    $html = preg_replace("/\n/","KEVINO_NEWLINE",$html);
    $html = preg_replace("/<head.*<body/","<body",$html);
    $html = preg_replace("/<script[^<]+<\/script>/"," ",$html);
    $html = preg_replace("/KEVINO_NEWLINE/","\n",$html);
    $html = preg_replace("/ & /"," and ",$html);
    $html = strip_tags($html,"<div><a><section>");
    # $lines = explode("\n",$html); for ($x = 0; $x < count($lines); $x++) { print "[$x]: {$lines[$x]}\n"; }
    $xml = simplexml_load_string($html);
    $section = $xml->xpath('//section[@id="main-content"]');
    $section = simplexml_load_string($section[0]->asXML());
    $div = $xml->xpath('//div[@id="cityott-content"]');
    $contentMD5 = md5($div[0]->asXML());
    print "MD5: $contentMD5\n";
    $div = simplexml_load_string($div[0]->asXML());
    $links = $div->xpath('//a');
    foreach ($links as $a) {
      #print_r($a);
      $docLink = $a->attributes();
      $docTitle = $a[0];
      print "  DOCUMENT: $docTitle $docLink\n";
    }
  }
  
}

ConsultationController::crawlConsultations();

?>
