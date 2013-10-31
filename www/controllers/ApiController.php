<?php

class ApiController {

  public static function feed($count = 10, $before = 0) {
    // can't use PDO for count, so make sure intenger on COUNT
    if (!preg_match('/^\d+$/',$count)) {
      print "ERROR: $count is not an integer\n";
      return;
    }
    if ($before == 0) {
      $rows = getDatabase()->all(" select * from feed order by created desc limit $count ");
    } else {
      $rows = getDatabase()->all(" select * from feed where id < :before order by created desc limit $count ",array('before'=>$before));
    }
    $now = new DateTime;
    foreach ($rows as &$r) {
      $min = $r['id']+0;
      $created = new DateTime($r['created']);
      $diff = $created->diff($now);
      $r['diff'] = $diff->format("%dd, %hhr");
    }

    $nextUrl =  OttWatchConfig::WWW."/api/feed/$count/$min";

    $result = array();
    $result['items'] = $rows;
    if (count($rows) > 0) {
      $result['next'] = array('count'=>$count,'before'=>$min,'url'=>$nextUrl);
    }

    return $result;
  }

  public static function arrayToCsv($array) {
    if (count($array) == 0) {
      return "";
    }
    ob_start();
    $df = fopen("php://output", 'w');
    fputcsv($df, array_keys(reset($array)));
    foreach ($array as $row) {
      fputcsv($df,$row);
    }
    fclose($df);
    return ob_get_clean();
  }

  public static function lobbyingAllCsv() {
    $rows = getDatabase()->all("
      select 
        f.id fileid,
        f.lobbyist,
        f.client,
        f.issue,
        l.id activityid,
        l.lobbydate,
        l.activity,
        l.lobbied,
        l.created reportedon
      from lobbying l
        join lobbyfile f on f.id = l.lobbyfileid
      order by
        l.created desc
    ");
    print self::arrayToCsv($rows);
    return;
  }

  public static function devApp($id) {
    $row = getDatabase()->one("select id,appid,devid,apptype,ward,receiveddate,updated,address,description from devapp where appid = :id or devid = :id ",array('id'=>$id));
    if (!$row['appid']) {
	    $result = array();
	    $result['status'] = 0;
	    $result['message'] = 'Not found';
      return $result;
    }
    $row['ward'] = preg_replace("/^Ward /","",$row['ward']);
    $row['ward'] = preg_replace("/ .*/","",$row['ward']);
    $row['address'] = json_decode($row['address']);
    $row['statuses'] = array();
    $dates = getDatabase()->all(" select * from devappstatus where devappid = :devid ",array('devid'=>$row['id']));
    foreach ($dates as $d) {
      unset($d['id']);
      unset($d['devappid']);
      $row['statuses'][] = $d;
    }
    unset($row['id']);
    return $row;
  }

  public static function devAppAll() {
    $rows = getDatabase()->all("select appid,devid,apptype,receiveddate,updated from devapp order by updated desc ");
    return $rows;
  }

  public static function about() {
    top();
    include("about_api.html");
    bottom();
  }

  public static function errResult($message) {
    $result = array();
    $result['status'] = 0;
    $result['message'] = $message;
    return $result;
  }

  public static function getPolygonAsArray($text) {
    # LINESTRING(-75.74452847405216 45.38879029192849, ..., -75.74384484749845 45.389000882100305)
    $text = preg_replace('/POLYGON\(\(/',"",$text);
    $text = preg_replace('/\)\)/',"",$text);
    $points = array();
    foreach (explode(",",$text) as $p) {
      $t = explode(" ",$p);
      $np = array();
      $np['lat'] = $t[1];
      $np['lat'] = preg_replace('/\)/','',$np['lat']);
      $np['lat'] = preg_replace('/\(/','',$np['lat']);
      $np['lon'] = $t[0];
      $np['lon'] = preg_replace('/\)/','',$np['lon']);
      $np['lon'] = preg_replace('/\(/','',$np['lon']);
      $points[] = $np;
    }
    return $points;
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

  /*
  Given the start of a street name, return all matching streets
  */
  public static function roadSearch ($search) {
    if ($search == '') {
      return array();
    }
    $sql = "
      select rd_name,rd_suffix,rd_directi
      from roadways
      where rd_name like :search
      group by rd_name,rd_suffix,rd_directi
      order by rd_name,rd_suffix,rd_directi
    ";
    $rows = getDatabase()->all($sql,array('search'=>"$search%"));
    return $rows;
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
    $midpoint = getLatLonFromPoint($row['midpoint']);
    $result['midpoint'] = $midpoint;
    #unset($result['midpoint']); # = $midpoint;
    $points = self::getLinestringAsArray($row['points']);
    $result['points'] = $points;
    $result['ward'] = $t['ward'];
    $result['polls'] = $t['polls'];

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

	    # also get the poll data
	    $row = getDatabase()->one(" select vot_subd from polls_2010 where ST_Contains(shape,PointFromText('POINT($lon $lat)')) ");
      $polls = array();
      if ($row['vot_subd']) {
        $polls['2010'] = $row['vot_subd'];
      }
      $result['polls'] = $polls;

    } else {
      $result['ward'] = self::errResult("Point is not within Ottawa city limits");
    }

    return $result;
  }

  public static function listWards() {
    $rows = getDatabase()->all(" select * from wards_2010 order by ward_en ");
    $result = array();
    foreach ($rows as $r) {
      $result[$r['ward_num']] = $r['ward_en'];
    }
    return $result;
  }

  public static function wardPollMapStaticUrl($wardnum,$year,$pollnum) {
    $poll = self::wardPoll($wardnum,$year,$pollnum);
    if (!$poll['ward']) {
      return "http://example.com/poll/not/found/brah.png";
    }

    $url = "http://maps.googleapis.com/maps/api/staticmap";
    $url .= "?size=640x640";
    $url .= "&scale=2";
    $url .= "&maptype=roadmap";
    $url .= "&sensor=false";
    $url .= "&path=color:0xff000044|weight:5|fillcolor:0xFF000044"; # |8th+Avenue+%26+34th+St,New+York,NY|\8th+Avenue+%26+42nd+St,New+York,NY|Park+Ave+%26+42nd+St,New+York,NY,NY|\Park+Ave+%26+34th+St,New+York,NY,NY";
    foreach ($poll['polygon'] as $p) {
      $url .= "|{$p['lat']},{$p['lon']}";
    }
    return $url;
  }

  public static function wardPollMapStatic302($wardnum,$year,$pollnum) {
    $url = self::wardPollMapStaticUrl($wardnum,$year,$pollnum);
    header("Location: $url");
  }

  public static function wardPollMapStatic($wardnum,$year,$pollnum) {
    $url = self::wardPollMapStaticUrl($wardnum,$year,$pollnum);
    print "<img src=\"$url\"/>";
  }

  public static function wardPollMapLive($wardnum,$year,$pollnum) {
    $poll = self::wardPoll($wardnum,$year,$pollnum);
    if (!$poll['ward']) {
      print "Not found\n";
      return;
    }
    ?>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
    <div id="map_canvas" style="width:100%; height:100%;"></div>
    <script>

google.maps.Polygon.prototype.getBounds = function() {
    var bounds = new google.maps.LatLngBounds();
    var paths = this.getPaths();
    var path;        
    for (var i = 0; i < paths.getLength(); i++) {
        path = paths.getAt(i);
        for (var ii = 0; ii < path.getLength(); ii++) {
            bounds.extend(path.getAt(ii));
        }
    }
    return bounds;
}

        var mapOptions = { 
          center: new google.maps.LatLng(45.420833,-75.69), 
          zoom: 10, 
          mapTypeId: google.maps.MapTypeId.ROADMAP 
        };
        map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
        var coords = [
        <?php
        $center = $poll['center'];
        foreach ($poll['polygon'] as $p) {
          print "new google.maps.LatLng({$p['lat']},{$p['lon']}),\n";
        }
        ?>
        ];

			  pollPoly = new google.maps.Polygon({
			    paths: coords,
			    strokeColor: "#FF0000",
			    strokeOpacity: 0.8,
			    strokeWeight: 2,
			    fillColor: "#FF0000",
			    fillOpacity: 0.35
			  });
			  pollPoly.setMap(map);

        var centerLatlng = new google.maps.LatLng(<?php print $center['lat']; ?>,<?php print $center['lon']; ?>);
        map.panTo(centerLatlng);
        map.fitBounds(pollPoly.getBounds());

    </script>

    <?php
  }

  public static function wardPoll($wardnum,$year,$pollnum) {
    $table = '';
    if ($year == '2010') {
      $table = 'polls_2010';
    } else {
      $result = array();
      $result['error'] = "unknown election year: $year";
      return $result;
    }
    $row = getDatabase()->one(" 
      select OGR_FID,astext(shape) polygon,astext(centroid(shape)) center 
      from $table 
      where 
        cast(ward as unsigned) = :wardnum 
        and vot_subd = :vot_subd ",
      array("wardnum"=>$wardnum,'vot_subd'=>$pollnum));
    if (!$row['OGR_FID']) {
      $result = array();
      $result['error'] = "poll not found";
      return $result;
    }
    $result = array();
    $result['ward'] = $wardnum;
    $result['year'] = $year;
    $result['pollnum'] = $pollnum;
    $result['polygon'] = self::getPolygonAsArray($row['polygon']);
    $result['center'] = getLatLonFromPoint($row['center']);
   
    # Add roads that are in, or close, to the poll.
    # "close" is required because roadway database takes the centerline, but poll boundaries might be on any 'side' of the road, so no overlap
    $roads = getDatabase()->all("
	    select 
	      rd_name,rd_suffix,rd_directi,left_from,left_to,right_from,right_to 
	    from roadways r 
	      join $table p on 
	        p.vot_subd = :vot_subd
	        and st_distance(r.shape,p.shape) < .0007 
	    where left_from+left_to+right_from+right_to > 0 
	    order by rd_name,left_from
      ",array('vot_subd'=>$pollnum));
    $result['roads'] = $roads;

    return $result;
  }

  public static function wardPolls($wardnum) {
    $rows = getDatabase()->all(" select vot_subd from polls_2010 where cast(ward as unsigned) = :wardnum order by vot_subd ",array("wardnum"=>$wardnum));
    $polls = array();
    foreach ($rows as $r) { $polls[] = $r['vot_subd']; }
    $result['2010'] = $polls;
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



