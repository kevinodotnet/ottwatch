<?php

class OpenDataController {

  /*
  Scan the data.ottawa.ca website and injest all datasets and the files within the sets.
  */
  public static function scanOpenData() {
	  $datasets = self::getDatasets();
    $index = 0;

    foreach ($datasets as $d) {

      print "$index / ".count($datasets)." :: $d\n";
      $index++;

      $set = self::getDataset($d);

      $count = getDatabase()->execute(" delete from opendata where guid = :id ",array('id'=>$set->id));
      if ($count > 0) { print "deleted existing data\n"; }

      $values = array();
      $values['guid'] = $set->id;
      $values['name'] = $set->name;
      $values['title'] = $set->title;
      $values['url'] = $set->ckan_url;
      $values['updated'] = $set->metadata_modified;

      $dataid = db_insert('opendata',$values);

      foreach ($set->resources as $r) {
        $values = array();
        $values['dataid'] = $dataid;
        $values['guid'] = $r->id;
        $values['size'] = $r->size;
        $values['description'] = trim($r->description);
        $values['format'] = $r->format;
        $values['name'] = $r->name;
        $values['url'] = $r->url;
        $values['updated'] = $r->last_modified;
        db_insert('opendatafile',$values);
      }

    }
  }

	public static function callData($path) {
	  $json = `wget -qO - "http://data.ottawa.ca/$path"`;
	  $result = json_decode($json);
	  return $result;
	}
	
	public static function getDatasets() {
	  $list = self::callData("/api/1/rest/dataset");
	  return $list;
	}
	
	public static function getDataset($name) {
	  $dataset = self::callData("/api/1/rest/dataset/$name");
	  return $dataset;
	}
	
}


