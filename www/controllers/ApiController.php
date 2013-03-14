<?php

class ApiController {

  /* Initialize a "success" result */
  public static function initResult() {
    $result = array();
    $result['status'] = 1;
    $result['message'] = 'ok';
    return $result;
  }

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
    $result = self::initResult();

    if (! preg_match("/^-{0,1}\d+\.\d+/",$lat)) {
      $result['status'] = 0;
      $result['message'] = 'Bad value of latitude';
      return $result;
    }
    if (! preg_match("/^-{0,1}\d+\.\d+/",$lon)) {
      $result['status'] = 0;
      $result['message'] = 'Bad value for longitude';
      return $result;
    }

    # what ward is it in?
    $ward = array();
    #$result['debug'] = " select ward_num,ward_en,ward_fr from wards_2010 where ST_Contains(shape,PointFromText('POINT($lon $lat)')) ";
    $row = getDatabase()->one(" select ward_num,ward_en,ward_fr from wards_2010 where ST_Contains(shape,PointFromText('POINT($lon $lat)')) ");
    if ($row['ward_num']) {
      # load ward details

      $ward = getApi()->invoke('/api/wards/'.$row['ward_num']);
      $result['ward_num'] = $ward['num'];
      $result['ward_name'] = $ward['name'];
      $result['councillor_first'] = $ward['councillor_first'];
      $result['councillor_last'] = $ward['councillor_last'] ;
      $result['councillor_email'] = $ward['councillor_email'] ;
      $result['councillor_phone'] = $ward['councillor_phone'] ;

      #$ward['num'] = $row['ward_num'];
      #$ward['en'] = $row['ward_en'];
      #$ward['fr'] = $row['ward_fr'];
    }

    return $result;
  }

  public static function ward($wardnum) {
    $result = self::initResult();

    $row = getDatabase()->one(" select * from electedofficials where wardnum = :wardnum ",array("wardnum"=>$wardnum));
    if (!$row['id']) {
      return self::errResult("Ward not found");
    }

    $result['num'] = $row['wardnum'];
    $result['name'] = $row['ward'];
    $result['councillor_first'] = $row['first'];
    $result['councillor_last'] = $row['last'];
    $result['councillor_email'] = $row['email'];
    $result['councillor_phone'] = $row['phone'];
    #$result = array_merge(self::initResult(),$row);
    #unset($result['id']);
    return $result;

  }

}
