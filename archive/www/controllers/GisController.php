<?php

class GisController {

	static public function viewLayer() {
		top3();

		$layer = $_GET['layer'];
		$whereK = $_GET['whereK'];
		$whereV = $_GET['whereV'];

		?>
		<b><?php print $layer; ?></b> <i><?php print " where $whereK = $whereV"; ?></i>
		<?php

		$sql =
			" select " .
			"   * " .
			" from ".mysql_escape_string($layer) .
			" limit 1 ";
		$r = getDatabase()->one($sql);

		$cols = array();
		foreach ($r as $k => $v) {
			$cols[] = $k;
		}

		$where = '';
		if ($whereK != '') {
			if (!in_array($whereK,$cols)) {
				print "BAD WHEREK: $whereK";
				bottom();
				return;
			}
			$where = " where ".mysql_escape_string($whereK)." = '".mysql_escape_string($whereV)."' ";
		}
		$limit = intval($_GET['limit']);
		if ($limit != '' && $limit > 0) { 
			$limit = " limit $limit; "; 
		} else {
			$limit = '';
		}

		$sql = " select 1";
		foreach ($cols as $c) {
			if ($c == 'Shape') {
				$sql .= ",astext($c) $c";
			} else {
				$sql .= ",$c";
			}
		}
		$sql .= " from ".mysql_escape_string($layer) ;
		$sql .= " $where ";
		$sql .= " $limit ";
		$rows = getDatabase()->all($sql);

		$js = '';
		foreach ($rows as $r) {
			if (preg_match('/^LINESTRING/',$r['Shape'])) {
				$arr = ApiController::getLinestringAsArray($r['Shape']);
				$latlons = array();
				foreach ($arr as $a) {
					$ll = mercatorToLatLon($a['lon'],$a['lat']);
					$latlons[] = $ll;
				}
				$linecoords = ' [ ';
				foreach ($latlons as $l) {
					$linecoords .= " {lat: {$l['lat']}, lng: {$l['lon']}}, \n";
				}
				$linecoords .= " ]\n";
				$js .= " new google.maps.Polyline({ path: $linecoords, geodesic: true, strokeColor: '#FF0000', strokeOpacity: 1.0, strokeWeight: 2 }).setMap(map); \n";

      	?>
				<?php

# var flightPlanCoordinates = [
#     {lat: 37.772, lng: -122.214},
#     {lat: 21.291, lng: -157.821},
#     {lat: -18.142, lng: 178.431},
#     {lat: -27.467, lng: 153.027}
#   ];
#   var flightPath = new google.maps.Polyline({
#     path: flightPlanCoordinates,
#     geodesic: true,
#     strokeColor: '#FF0000',
#     strokeOpacity: 1.0,
#     strokeWeight: 2
#   });
# 
#   flightPath.setMap(map);



			}
			#break;
		}

		?>
	    <div id="map_canvas" style="width:100%; height:600px;"></div>
			<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
	    <script>
	      $(document).ready(function() {
	        var mapOptions = { 
	          center: new google.maps.LatLng(45.420833,-75.69), 
	          zoom: 10, 
	          mapTypeId: google.maps.MapTypeId.ROADMAP 
	        };
	        map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
		        // var myLatlng = new google.maps.LatLng(<?php print $addr->lat; ?>,<?php print $addr->lon; ?>);
		        // var marker = new google.maps.Marker({ position: myLatlng, map: map, title: '<?php print $addr->addr; ?>' }); 
		        // map.panTo(myLatlng);
	      });
	    </script>
			<script> $(document).ready(function() { <?php print $js; ?> }); </script>
		<?php

		bottom3();
	}

}
