<?php

class ApiController {

  /* Initialize a "success" result */

  public static function errResult($message) {
    $result = array();
    $result['status'] = 0;
    $result['message'] = $message;
    return $result;
  }

  /* Takes "lat" and "long" GET parameters and returns data about the point */
  public static function point() {
    $lat = $_GET['lat'];
    $lon = $_GET['lon'];

    if (! preg_match("/^-{0,1}\d+\.\d+/",$lat)) {
      return self::errResult("Bad lat");
    }
    if (! preg_match("/^-{0,1}\d+\.\d+/",$lon)) {
      return self::errResult("Bad lan");
    }

    # what ward is it in?
    $ward = array();
    $row = getDatabase()->one(" select ward_num,ward_en,ward_fr from wards_2010 where ST_Contains(shape,PointFromText('POINT($lon $lat)')) ");
    if ($row['ward_num']) {
      return getApi()->invoke('/api/wards/'.$row['ward_num']);
    } else {
      return self::errResult("Point is not within Ottawa city limits");
    }
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
