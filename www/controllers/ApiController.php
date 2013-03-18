<?php

class ApiController {

  /* Initialize a "success" result */

  public static function errResult($message) {
    $result = array();
    $result['status'] = 0;
    $result['message'] = $message;
    return $result;
  }

  public static function getLinestringAsArray($text) {
    # LINESTRING(-75.74452847405216 45.38879029192849, ..., -75.74384484749845 45.389000882100305)
    $text = preg_replace('/LINESTRING\(/',"",$text);
    $text = preg_replace('/\)/',"",$text);
    $points = array();
    foreach (explode(",",$text) as $p) {
      $t = explode(" ",$p);
      $np = array();
      $np['lat'] = $t[1];
      $np['lon'] = $t[0];
      $points[] = $np;
    }
    return $points;
    
  }

  public static function getLatLonFromPoint($text) {
    # POINT(-75.74431034266786 45.38770326435866)
    $matches = array();
    $result = array();
    if (preg_match("/POINT\(([^ ]+) ([^\)]+)\)/",$text,$matches)) {
      $result['lat'] = $matches[2];
      $result['lon'] = $matches[1];
    }
    return $result;
  }

  public static function road($number,$name,$suff) {
    $result = array();
    $result['number'] = $number;
    $result['name'] = $name;

    /*
        :number between left_from and left_to bet_left_f_t,
        :number between left_to and left_from bet_left_t_f,
        :number between right_from and right_to bet_right_f_t,
        :number between right_to and right_from bet_right_t_f,
    */
    $where_suffix = '';
    if ($suff != '') {
      $where_suffix = " and rd_suffix = upper(:suffix) ";
    }

    $row = getDatabase()->all(" 
      select 
        OGR_FID id,
        astext(shape) as points, 
        astext(pointN(shape,numpoints(shape)/2)) midpoint,
        rd_name,
        rd_suffix,
        rd_directi,
        left_from,
        left_to,
        right_from,
        right_to 
      from roadways 
      where 
        rd_name  = upper(:name) 
        $where_suffix
        and (
          (:number % 2 = left_from % 2 and (:number between cast(left_from as unsigned) and cast(left_to as unsigned)))
          or (:number % 2 = left_from % 2 and (:number between cast(left_to as unsigned) and cast(left_from as unsigned)))
          or (:number % 2 = right_from % 2 and (:number between cast(right_from as unsigned) and cast(right_to as unsigned)))
          or (:number % 2 = right_from % 2 and (:number between cast(right_to as unsigned) and cast(right_from as unsigned)))
        )
      ",array(
      'name' => $name,
      'number' => $number,
      'suffix' => $suff
      ));
    if (count($row) == 0) {
      $result['error'] = "Street name and number match not found";
      return $result;
    } 
    if (count($row) > 1) {
      # should not happen
      $result['error'] = "Matched ".count($row)." streets; should not be possible. Try appending a street suffix?";
      $result['raw'] = $row;
      return $result;
    } 
    $row = $row[0];
    foreach ($row as $k => $v) {
      $result[$k] = $v;
    }
    $midpoint =  self::getLatLonFromPoint($row['midpoint']);
    unset($result['midpoint']); # = $midpoint;
    $points = self::getLinestringAsArray($row['points']);
    $result['points'] = $points;
    $t = self::point($midpoint['lat'],$midpoint['lon']);
    $result['ward'] = $t['ward'];

    return $result;
  }

  /* Takes "lat" and "long" GET parameters and returns data about the point */
  public static function point($lat,$lon) {
    if ($lat == '') {
      $lat = $_GET['lat'];
    }
    if ($lon == '') {
      $lon = $_GET['lon'];
    }

    if (! preg_match("/^-{0,1}\d+\.\d+/",$lat)) {
      return self::errResult("Bad lat");
    }
    if (! preg_match("/^-{0,1}\d+\.\d+/",$lon)) {
      return self::errResult("Bad lon");
    }

    $result = array();
    $result['lat'] = $lat;
    $result['lon'] = $lon;

    # what ward is it in?
    $row = getDatabase()->one(" select ward_num,ward_en,ward_fr from wards_2010 where ST_Contains(shape,PointFromText('POINT($lon $lat)')) ");
    if ($row['ward_num']) {
      $result['ward'] = getApi()->invoke('/api/wards/'.$row['ward_num']);
    } else {
      $result['ward'] = self::errResult("Point is not within Ottawa city limits");
    }

    return $result;
  }

  public static function ward($wardnum) {
    $row = getDatabase()->one(" select * from electedofficials where wardnum = :wardnum ",array("wardnum"=>$wardnum));
    if (!$row['id']) {
      return self::errResult("Ward not found");
    }
    return getApi()->invoke('/api/councillors/'.$row['id']);
  }

  public static function committees() {
    # select the most recent meeting for each cateogory
    $rows = getDatabase()->all(" select category,max(id) id from meeting group by category order by category ");
    $committees = array();
    foreach ($rows as $r) {

      $m = getDatabase()->one(" select * from meeting where id = :id ",array('id'=>$r['id']));
      $committee = array();
      $committee['category'] = $m['category'];
      $committee['name'] = meeting_category_to_title($m['category']);

      $members = array();
      foreach (json_decode($m['members']) as $memberid) {
        $member = getApi()->invoke('/api/councillors/'.$memberid);
        $members[] = $member;
      }
      $committee['members'] = $members;

      $committees[] = $committee;
    }
    $result['committees'] = $committees;

    return $result;
    
  }

  public static function councillorById($id) {
    $row = getDatabase()->one(" select * from electedofficials where id = :id ",array('id'=>$id));
    return $row;
  }

  public static function councillorByName($last,$first) {
    $row = getDatabase()->one(" select * from electedofficials where lower(last) = lower(:last) and lower(first) = lower(:first) ",array('last'=>$last,'first'=>$first));
    return $row;
  }

}
