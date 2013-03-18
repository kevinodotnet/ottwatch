<?php

class UserController {

  static public function addPlace () {
    $rd_num = $_POST['rd_num'];
    $rd_name = $_POST['rd_name'];
    $rd_suff = $_POST['rd_suff'];
    $road = getApi()->invoke("/api/roads/{$rd_num}/{$rd_name}/{$rd_suff}");
    if ($road['error']) {
      top();
      print "Error adding place: ".$road['error'];
      bottom();
      return;
    }
    try {
	    getDatabase()->execute(" insert into places (roadid,rd_num,personid) values (:roadid,:rd_num,:personid) ",array(
	      'roadid' => $road['id'],
	      'rd_num' => $rd_num,
	      'personid' => getSession()->get('user_id')
	    ));
	    header("Location: ../home");
    } catch (Exception $e) {
      top();
      print "Error saving place: $e";
      bottom();
    }
  }

  static public function home() {
    top();
    ?>
    <h1><?php print getSession()->get('user_email'); ?></h1>
    <div class="row-fluid">

    <div class="span4">
    <h4>Your Places of Interest</h4>
    <?php 
    $rows = getDatabase()->all(" 
      select
        p.id, p.rd_num, r.rd_name, r.rd_suffix
      from places p
        join roadways r on r.OGR_FID = p.roadid
      where personid = ".getSession()->get("user_id")."
      order by
        r.rd_name, p.rd_num
    ");
    if (count($rows) == 0) {
      print "You have not added any places of interest yet.<br/>";
    }
    foreach ($rows as $r) {
      print $r['rd_num']." ".$r['rd_name']."<br/>";
      #pr($r);
      #pr($road);
    }
    ?>
    <br/>
    <a id="showAddFormBtn" class="btn" href="javascript:showAddPlaceForm()"><i class="icon-plus"></i> Add New Place</a>
    <script>
    function showAddPlaceForm() {
      $('#showAddFormBtn').css('display','none');
      $('#addForm').css('display','block');
    }
    function saveNewPlace() {
      $('rd_name').value('foo');
      return false;
    }
    </script>
    <div id="addForm" style="display: none;">
    <form class="form-inline" action="add/place" method="post">
    <input type="text" name="rd_num" class="input-small" placeholder="Number"/>
    <input type="text" name="rd_name" class="input-small" placeholder="Street Name"/>
    <select name="rd_suff">
    <?php 
    $suffixes = getDatabase()->all(" select distinct(rd_suffix) s from roadways where rd_suffix != 'NULL' order by rd_suffix ");
    foreach ($suffixes as $r) {
      ?>
      <option value="<?php print $r['s']; ?>"><?php print $r['s']; ?></option>
      <?php
    }
    ?>
    </select>
    <button class="btn" onclick="saveNewPlace()">Add</button>
    </form>
    </div>
    </div><!-- /span -->

    <div class="span8">
    <div id="map_canvas" style="width:100%; height:600px;"></div>
    <script>
        var mapOptions = { 
          center: new google.maps.LatLng(45.420833,-75.69), 
          zoom: 12, 
          mapTypeId: google.maps.MapTypeId.ROADMAP 
        };
        map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
        <?php
        foreach ($rows as $r) {
          $address = "{$r['rd_num']} {$r['rd_name']} {$r['rd_suffix']}, Ottawa, Ontario";
          ?>
	        var contentString<?php print $r['id']; ?> = 
            '<div>' + 
            '<b><?php print $address; ?></b>' + 
            '</div>';
	        var infowindow<?php print $r['id']; ?> = new google.maps.InfoWindow({ content: contentString<?php print $r['id']; ?> });
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode( { 'address': '<?php print $address; ?>'}, function(results, status) {
              if (status == google.maps.GeocoderStatus.OK) {
		  	        var myLatlng<?php print $r['id']; ?> = new google.maps.LatLng(results[0].geometry.location.mb,results[0].geometry.location.nb);
				        var marker<?php print $r['id']; ?> = new google.maps.Marker({ position: myLatlng<?php print $r['id']; ?>, map: map, title: '<?php print $address; ?>' }); 
				        google.maps.event.addListener(marker<?php print $r['id']; ?>, 'click', function() {
				          infowindow<?php print $r['id']; ?>.open(map,marker<?php print $r['id']; ?>);
                });
              }
            });
          <?php
          print "/*\n";
          pr($r);
          print "*/\n";
        }
        ?>
    </script>
    </div><!-- /span -->

    </div><!-- /row -->
    <?php
    bottom();
  }


}

?>
