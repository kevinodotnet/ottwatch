<?php

class UserController {

  static public function addPlace () {
    if (!LoginController::isLoggedIn()) { print "ERROR: not logged in"; return; }

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

  static public function getEmailVerification($id) {
    $row = getDatabase()->one(" select md5(concat(id,lower(email),created)) md5 from people where id = :id ",array('id'=>$id));
    return $row['md5'];
  }

  static public function emailVerify($md5) {

    #$rows = getDatabase()->all(" select * from people where md5(id||email||created) = :md5 ",array('md5'=>$md5));
    #$rows = getDatabase()->all(" select md5(id||email||created) t from people ");
    $rows = getDatabase()->all(" select * from people where md5(concat(id,lower(email),created)) = :md5 ",array('md5'=>$md5));
    if (count($rows) == 0) {
      LoginController::logout();
      top();
      ?>
      <h1>Error</h1>
      Verify link did not match any users.
      <?php
      bottom();
      return;
    }
    if (count($rows) > 1) {
      LoginController::logout();
      top();
      ?>
      <h1>Error</h1>
      Verify link matched multiple users. This should not be possible!
      <?php
      bottom();
      return;
    }
    # verify link worked; mark as verified and log the user in.
    $user = $rows[0];
    getDatabase()->execute(" update people set emailverified = 1 where id = :id ",array('id'=>$user['id']));
    LoginController::setLoggedIn($user['id']);
    top();
    ?>
    <h1>Verified!</h1>
    <p>Your email address has been verified.</p>
    <p><a href="../../home">Back to user profile</a></p>

    <?php
    bottom();
  }

  static public function emailSendVerify() {
    if (!LoginController::isLoggedIn()) { print "ERROR: not logged in"; return; }
    top();

    $md5 = self::getEmailVerification(getSession()->get('user_id'));
    $url = OttWatchConfig::WWW."/user/email/verify/$md5";

    $mail = new PHPMailer;
    $mail->isSMTP();    
    $mail->Host = OttWatchConfig::SMTP_HOST;
    $mail->From = OttWatchConfig::SMTP_FROM_EMAIL;
    $mail->FromName = OttWatchConfig::SMTP_FROM_NAME;
    $mail->addAddress(getSession()->get('user_email'));
    $mail->Subject = 'Email address verification - OttWatch';
    $mail->Body = "
    To complete your email address verification with OttWatch click on the link below.<br/><br/>
    <a href=\"$url\">$url</a><br/><br/>
    Ignore this email if you did not request an account on OttWatch.ca
    ";
    $mail->AltBody = "
    To complete your email address verification with OttWatch click on the link below.

    $url

    Ignore this email if you did not request an account on OttWatch.ca
    ";
    if(!$mail->send()) {
      echo 'Mailer Error: ' . $mail->ErrorInfo;
      bottom();
      return;
    }

    # print "<a href=\"$url\">$url</a>";

    ?>
    <h1>Verification Email Sent</h1>
    A verification link has been sent to your email address. Please click on it to complete the verification.
    (and maybe check your SPAM folder if you can't find it).
    <?php
    bottom();
  }

  static public function update() {
    if (!LoginController::isLoggedIn()) { print "ERROR: not logged in"; return; }

    $name = $_POST['name'];
    $email = $_POST['email'];

    # names are just trusted.
    getDatabase()->execute(" update people set name = :name where id = :id ",array('id'=>getSession()->get('user_id'),'name'=>$name));

    # if email actually changed then mark it unverified
    try {
      getDatabase()->execute(" update people set emailverified = 0, email = :email where id = :id and email != :email ",array('id'=>getSession()->get('user_id'),'email'=>$email));
    } catch (Exception $e) {
      if (preg_match('/Duplicate entry/',$e)) {
        top();
        ?>
        <h1>Oopos...</h1>
        Looks like that email address is already taken.
        <?php
        bottom();
        return;
      } else {
        pr($e);
      }
      return;
    }

    # reload session details
    LoginController::loadLoggedIn(getSession()->get('user_id'));
    header("Location: home");
  }

  static public function home() {
    if (!LoginController::isLoggedIn()) { print "ERROR: not logged in"; return; }
    top();

    $name = getSession()->get('user_name'); if ($name == '' || $name == null) { $name = 'Your Name'; }
    $email = getSession()->get('user_email'); if ($email == '' || $email == null) { $email = 'Your Email'; }

    ?>

    <div class="row-fluid">

    <div class="span4">
    <h1>User Details</h1>

    <form class="form-horizontal" method="post" action="update">
      <div class="control-group">
      <label class="control-label">Name</label>
      <div class="controls">
	    <input type="text" id="inputName" name="name" value="<?php print getSession()->get('user_name'); ?>" placeholder="<?php print $name; ?>">
      </div>
      </div>
      <div class="control-group">
      <label class="control-label">Email</label>
      <div class="controls">
	    <input type="text" id="inputEmail" name="email" value="<?php print getSession()->get('user_email'); ?>" placeholder="<?php print $email; ?>">
      </div>
      </div>
      <div class="controls">
	    <button type="submit" class="btn">Save</button>
      </div>
    </form>

    </div><!-- /span -->

    <div class="span4">
    <h1>Activities</h1>
    <?php
    $activities = 0;

    $user = getDatabase()->one(" select * from people where id = :id ",array('id'=>getSession()->get('user_id')));
    if ($user['email'] == null || $user['email'] == '') {
      $activities ++;
      ?>
      <p>
      <i class="icon-tasks"> </i>
      Your email address is empty! That's not a big deal, but some OttWatch features
      are only available if you provide (and verify) an email address. You can use 
      the 'User Details' section to enter an email address.
      </p>
      <?php
    } elseif (!$user['emailverified']) {
      $activities ++;
      ?>
      <p>
      <i class="icon-tasks"> </i>
      Your <b><?php print $user['email']; ?></b> email address has not been verified.
      Please click on the link in the verification email that was sent to you. Check
      your spam folder if you can't find it.<br/>
      <b><a href="email/sendVerify">Request a new verification email</a></b>.
      </p>
      <?php
    }

    if ($activities == 0) {
      ?>
      <p>None!</p>
      <?php
    }
    ?>
    </div>

    <div class="span4">
    <h1>Stories</h4>
    <p><a class="btn" href="<?php print OttWatchConfig::WWW; ?>/story/add">Create New Story</a></p>
    <?php
    $rows = getDatabase()->all(" select * from story where deleted = 0 and personid = :id order by id desc ",array('id'=>getSession()->get('user_id')));
    foreach ($rows as $r) {
      print "<a href=\"".OttWatchConfig::WWW."/story/edit/{$r['id']}\">{$r['id']}: {$r['title']}</a><br/>";
    }
    ?>
    </div>

    </div><!-- /row -->



    <?php
    bottom();
    return;
    ?>

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
