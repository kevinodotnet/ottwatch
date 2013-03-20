<?php

class MeetingController {
 
  # trim file titles if they match the item title
  static public function trimFileTitle ($itemTitle,$fileTitle) {
    $o = $fileTitle;
    $a = explode(" ",$itemTitle);
    $b = explode(" ",$fileTitle);
    while (count($b)) {
      $a[0] = preg_replace("/[^a-zA-Z0-9]*/","",$a[0]);
      $b[0] = preg_replace("/[^a-zA-Z0-9]*/","",$b[0]);
      if ($a[0] == $b[0]) {
        array_shift($a);
        array_shift($b);
        continue;
      }
      if (preg_match("/^{$b[0]}/",$a[0])) {
        array_shift($b);
        continue;
      }
      if ($b[0] == '-') {
        array_shift($b);
        continue;
      }
      break;
    }
    $fileTitle = implode(" ",$b);
    return $fileTitle;
  }

  # for a given fileid, resolve the "cache" trick, then proxy download the real PDF
  static public function getFileCacheUrl ($fileid) {
    # get the data
    $url = 'http://app05.ottawa.ca/sirepub/view.aspx?cabinet=published_meetings&fileid=' . $fileid;
    header("Location: $url");
    return;
    $data = file_get_contents($url);
    # <script>document.location = 'cache/2/lkwtpr5l2u0ppewlizialyuu/4692203012013020316562.PDF';</script>
    $data = preg_replace("/';.*/","",$data);
    $data = preg_replace("/.*'/","",$data);
    $url = "http://app05.ottawa.ca/sirepub/$data";
    # get the real PDF and echo it back.
    header("Content-Type: application/pdf");
    print file_get_contents($url);
  }

  static public function getDocumentUrl ($meetid,$doctype) {
    return "http://app05.ottawa.ca/sirepub/agview.aspx?agviewmeetid={$meetid}&agviewdoctype={$doctype}";
  }

  static public function getItemUrl ($itemid) {
    return "http://app05.ottawa.ca/sirepub/agdocs.aspx?doctype=agenda&itemid=".$itemid;
  }

  static public function getMeetingUrl ($id) {
    global $OTT_WWW;
    $row = getDatabase()->one(" select id,category from meeting where id = :id ",array("id" => $id));
    if (!$row['id']) {
      return "";
    }
    return "$OTT_WWW/meetings/{$row['category']}/{$row['id']}";
  }

  static public function meetidForward ($meetid) {
    $m = getDatabase()->one(" select * from meeting where meetid = :meetid ",array("meetid" => $meetid));
    if (!$m['id']) {
      error404();
      return;
    }
    header("Location: ../{$m['category']}/{$m['meetid']}");
  }

#  static public function itemFiles ($category,$id,$itemid,$format) {
#    $item = getDatabase()->all(" select * from item where id = :id ",array('id' => $itemid));
#    $files = getDatabase()->all(" select * from ifile where itemid = :id order by id ",array('id' => $itemid));
#    if ($format == 'files.json') {
#      print json_encode($files);
#      return;
#    }
#    foreach ($files as $f) {
#      $url = "http://app05.ottawa.ca/sirepub/view.aspx?cabinet=published_meetings&fileid={$f['fileid']}";
#      print "<i class=\"icon-file\"></i> <a target=\"_blank\" href=\"{$url}\">".self::trimFileTitle($item['title'],$f['title'])."</a><br/>\n";
#    }
#  }

  static public function meetingDetails ($category,$meetid,$itemid) {

    $m = getDatabase()->one(" select * from meeting where meetid = :meetid ",array("meetid" => $meetid));
    if (!$m['id']) {
      # meeting ID was not found
      self::doList($category);
      return;
    }

    $focusFrameSrc = self::getDocumentUrl($meetid,'AGENDA');
    $title = meeting_category_to_title($m['category']);
    if ($itemid != '') {
      $item = getDatabase()->one(" select * from item where itemid = :itemid ",array("itemid"=>$itemid));
      $title = $item['title'];
      $focusFrameSrc = self::getDocumentUrl($meetid,'AGENDA')."#Item$itemid";
    }

    # display list of items, and break out with the files too
    $items = getDatabase()->all(" select * from item where meetingid = :meetingid order by id ",array("meetingid"=>$m['id']));
    top($title . " on " . substr($m['starttime'],0,10));

    # any places for this meeting?
    $places = getDatabase()->all( " 
      select 
        concat(p.rd_num,' ', r.rd_name,' ',r.rd_suffix,' ',coalesce(r.rd_directi,'')) addr,
        astext(p.shape) point,
        i.itemid,
        p.rd_num,
        r.rd_name,
        r.rd_suffix,
        r.rd_directi
      from places p
        join roadways r on p.roadid = r.OGR_FID
        join item i on p.itemid = i.id
      where p.itemid in (select id from item where meetingid = :meetingid) ",array("meetingid"=>$m['id']));
    # LEFT hand navigation, items and files links
    ?>

    <script>
    function focusOn(type,id,title) {

      if (type == 'item') {
        // move agenda to an item, and refocus on agenda tab just in case user is currently browsing
        // a different tab
        $('#focusFrame').attr('src','<?php print self::getDocumentUrl($m['meetid'],'AGENDA'); ?>#Item' + id);
        $('#tablist a[href="#tabagenda"]').tab('show'); 
        return;
      }

      d = document.getElementById('tabfile'+id);
      if (d) {
        // already added, just flip to tab
        $('#tablist a[href="#tabfile'+id+'"]').tab('show'); 
      } else {
        // add new tab
        maxtitle = 30;
        if (title.length > maxtitle) {
          title = title.substring(0,maxtitle-3)+'...';
        }

        url = '<?php print OttWatchConfig::WWW; ?>/meetings/file/' + id;
        frameurl = 'http://docs.google.com/viewer?url='+escape(url)+'&embedded=true';
        $('#tablist').append('<li><a href="#tabfile'+id+'" data-toggle="tab">'+title+'</a></li>');
        tabcontent = '';
        tabcontent = tabcontent + '<div class="tab-pane active in" id="tabfile'+id+'">';
        tabcontent = tabcontent + '<iframe id="focusFrame" src="'+frameurl+'" style=" border: 0px; border-left: 1px solid #000000; width: 100%; height: 600px;"></iframe>';
        tabcontent = tabcontent + '</div>';
        $('#tabcontent').append(tabcontent);
        $('#tablist a[href="#tabfile'+id+'"]').tab('show'); 
      }

    }
    </script>

    <div class="row-fluid">

    <!-- column 1 -->
    <div class="span4">

    <?php
    print "<b>$title</b>";
    renderShareLinks("City meeting: $title","/meetings/{$category}/{$meetid}");
    print "<br/>";
    print "<small>".substr($m['starttime'],0,10)."</small>";
    ?>

    <div style="padding: 5px; 0px;">
    <?php
    ?>
    </div>

    <div id="agendanav" style="overflow:scroll; height: 620px;">
    <?php
    foreach ($items as $i) {
      if ($i['title'] == '') {
        // ODD, parser is broken in some way; meh
        continue;
      }
      #print "<pre>"; print print_r($i); print "</pre>";
      print "<b><a href=\"javascript:focusOn('item',{$i['itemid']})\">{$i['title']}</a></b><br/>\n";
      $files = getDatabase()->all(" select * from ifile where itemid = :itemid order by id ",array("itemid"=>$i['id']));
      if (count($files) > 0) {
        foreach ($files as $f) {
          $ft = self::trimFileTitle($i['title'],$f['title']);
          $fileurl = OttWatchConfig::WWW . "/meetings/file/" . $f['fileid'];
          print "<small><a target=\"_blank\" href=\"{$fileurl}\"><i class=\"icon-share-alt\"></i></a> <a href=\"javascript:focusOn('file',{$f['fileid']},'{$ft}')\"><i class=\"icon-edit\"></i> {$ft}</small></a><br/>\n";
        }
      }
      print "<br/>\n";
    }
    ?>
    </div>

    </div>

    <!-- column 2 -->
    <div class="span8" style=" border: 0px; border-left: 1px solid #000000; height: 620px;">

    <ul id="tablist" class="nav nav-tabs">
    <li><a href="#tabagenda" data-toggle="tab">Agenda</a></li>
    <li><a href="#tabmap" data-toggle="tab">Map</a></li>
    <li><a href="#tabcomments" data-toggle="tab">Comments</a></li>
    <li><a href="#tabdelegation" data-toggle="tab"><big><b>Public Delegations</b></big></a></li>
    <li><a href="#tababout" data-toggle="tab">About</a></li>
    </ul>

    <div id="tabcontent" class="tab-content">

    <div class="tab-pane active in" id="tabagenda">
    <iframe id="focusFrame" src="<?php print $focusFrameSrc; ?>" style="width: 100%; height: 600px; border: 0px;"></iframe>
    </div><!-- /tab -->

    <div class="tab-pane fade" id="tabmap">
    <div id="map_canvas" style="width:100%; height:590px;">
      <script>
        var mapOptions = { 
          center: new google.maps.LatLng(45.420833,-75.69), 
          zoom: 10, 
          mapTypeId: google.maps.MapTypeId.ROADMAP 
        };
        infowindow = new google.maps.InfoWindow({ content: '' });
        map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
        <?php
        foreach ($places as $p) {
          $title = '';
          foreach ($items as $tempi) {
            if ($tempi['itemid'] == $p['itemid']) {
              $title = $tempi['title'];
            }
          }
          # use anonymous functions so variable scope is not insane
					# [addr] => 265 CARLING AVE 
					# [point] => POINT(-75.6998273 45.4011291)
					# [itemid] => 2399
          $loc = getLatLonFromPoint($p['point']);
          # print "<b><a href=\"javascript:focusOn('item',{$i['itemid']})\">{$i['title']}</a></b><br/>\n";
          ?>
          (function(){
		        myLatlng = new google.maps.LatLng(<?php print $loc['lat']; ?>,<?php print $loc['lon']; ?>);
			      marker = new google.maps.Marker({ position: myLatlng, map: map, title: '<?php print $p['addr']; ?>' }); 
            google.maps.event.addListener(marker, 'click', (function(marker) {
              return function() {
                infowindow.setContent(
			            '<div>' + 
			            '<b><?php print $p['addr']; ?></b> ' + 
		              '<a href="javascript:focusOn(\'item\',<?php print $p['itemid']; ?>)">(Goto Agenda)</a><br/>' +
                  '<?php print $title; ?>' +
			            '</div>'
                );
                infowindow.open(map, marker);
              }
            })(marker));
          })();
          <?php
        }
        ?>
      </script>
    </div>
    </div><!-- /tab -->

    <div class="tab-pane fade" id="tabcomments">
    <div style="padding: 10px; padding-top: 0px; overflow: scroll; height: 600px;">
    <h3>This is an informal chat area</h3>
    The purpose of this page is to let residents comment and interact with eachother on the meeting's topics. 
    <b>BUT</b> it is not monitored by the City, so what you say here may not influence any decisions or votes.
    To have your opinion officially heard, use the <b>Public Delegation</b> tab to email the meeting
    coordinator, and through them, the relevant commmittee members.
    <p/>
    <?php disqus(); ?>
    </div>
    </div><!-- /tab -->

    <div class="tab-pane fade" id="tabdelegation">
    <div style="padding: 10px; padding-top: 0px; overflow: scroll; height: 600px;">
    <h3>What is a "Public Delegation"?</h3>
    <p>
    Everyone is entitled to attend committee meetings and provide a verbal statement (up to 5 minutes long)
    before Councillors vote on each agenda item. If you are not able to attend a meeting, you can also email
    your comments to councillors. Either way, your statements become part of the official record - and this
    is the single most important step to shaping the outcome of your city.
    </p>

    <?php
    $date = substr($m['starttime'],0,10);
    $title = meeting_category_to_title($m['category']);
    $subject = urlencode("Public Delegation for $title on $date");
    $mailto = "mailto:{$m['contactEmail']}?Subject=$subject";
    if ($m['contactName'] != '') {
	    ?>
	    <p>
	    This meeting's coordinator is:
	    <blockquote><b><?php print $m['contactName']; ?></b><br/>
	    <a target="_blank" href="<?php print $mailto; ?>"><?php print "{$m['contactEmail']}"; ?></a><br/>
	    <?php print $m['contactPhone']; ?></blockquote>
	    </p>
	    <?php 
    } else {
      ?>
	    <p>
	    This meeting's coordinator <b>was not autodetected - oops</b>! But you can read it yourself - it's at the top of the Agenda.
      </p>
	    <?php 
    }
    ?>
    <p>You can also email the councillors directly. Convenient links for that are on the "About" tab.</p>
    <h3>What should I say? I'm scared! I'm not an expert!</h3>
    <p>
    Here's a little secret: councillors aren't experts either, so don't let that stop you. They are also
    regular people and benefit from feedback from the public on the decisions they are about to make.
    </p>
    <p>
    Anything you choose to write or say as a "public delegation" is good. But if you want some tips, here they are.
    Don't feel limited to these points though - you have rights in this democracy - use them!
    </p>
    <ul>
    <li><b>Be respectful and concise</b>: Make your point in the first paragraph, and leave the proof till later. You have the right to say your peace, but truth be told, I don't know if councillors read these things. So shorter is better.</li>
    <li><b>Come in person if at all possible</b>: Public delegations by email are good, but showing up in person is better. Councillors may skip reading the emails, but they can't avoid listening to you for five minutes if you can make it.</li>
    <li><b>Don't pick on staff</b>: Often staff seem to do things that the public dislikes, but that's because staff only do what Council has instructed them to do in the first place. So if you have a beef, make it a beef with Councillors. (and remember, respectful and concise)</li>
    <li><b>Don't be shy</b>: 
    If you're reviewed the Lobbyist Registry you'll know that business has no shame in pushing agendas at City Hall. If you're reading this, you are the 1 in 5,000 who might take action and change Ottawa's future. 
    So don't be shy or nervous about it! "Power to the people" and all that good stuff, you know?
    </li>
    </ul>

    </div>
    </div><!-- /tab -->

    <div class="tab-pane fade" id="tababout">
    <div style="padding: 10px; padding-top: 0px; overflow: scroll; height: 600px;">
    <h3>Contact Information</h3>
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
      <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Ward</th>
      <th>Office</th>
      </tr>
    <?php
    $members = $m['members'];
    if ($members != '') {
      $members = json_decode($members);
      $rows = getDatabase()->all(" select * from electedofficials where id in (".implode(",",$members).") ");
    } else {
      $rows = array();
    }
    $emails = array();
    foreach ($rows as $r) {
      $emails[] = $r['email'];
      ?>
      <tr>
      <td><b><?php print "{$r['last']}, {$r['first']}"; ?></b></td>
      <td><?php print "{$r['email']}"; ?></td>
      <td><?php print "{$r['phone']}"; ?></td>
      <td><?php print "{$r['ward']}"; ?> (Ward <?php print "{$r['wardnum']}"; ?>)</td>
      <td><?php print "{$r['office']}"; ?></td>
      </tr>
      <?php
    }
    $mailto = "mailto:".implode(",",$emails)."?Subject={$title} on ".substr($m['starttime'],0,10);
    ?>
    </table>
    <h3>Send Your Thoughts</h3>
    <p>Want to contact the meeting members? Here's two easy ways:</p>
    <blockquote><a target="_blank" href="<?php print $mailto; ?>">Click on this convenient MAILTO link</a>.</blockquote>
    <p>Cut and paste these email addresses into your email program:</p>
    <blockquote><?php print implode("<br/>",$emails); ?></blockquote>
    </div>
    </div><!-- /tab -->

    </div><!-- /tabcontent -->

    </div>

    </div>
    <?php
    bottom();

  }

  static public function doList ($category) {
    global $OTT_WWW;
    top();
    if ($category == 'all' || $category == '') { 
      $category = ''; 
      $title = 'All Categories';
    } else {
      $title = meeting_category_to_title($category);
    }
    ?>

<div id="navbar-example" class="navbar navbar-static">
<div class="navbar-inner">
<div class="container" style="width: auto;">

<ul class="nav" role="navigation">
<a class="brand" href="#">Filter</a>
<li class="dropdown">
  <a id="drop1" href="#" class="dropdown-toggle" data-toggle="dropdown"><?php print $title; ?> <b class="caret"></b></a>
  <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
    <?php
    $rows = getDatabase()->all(" select category,title from category order by title ");
    foreach ($rows as $r) { 
      ?>
      <li role="menuitem"><a href="<?php print urlencode($r['category']); ?>"><?php print $r['title']; ?></a></li>
      <?php
    }
    ?>
    <li role="menuitem" class="divider"></li>
    <li role="menuitem"><a href="all">All Meetings</a></li>
  </ul>
</li>
<!--
<li class="dropdown">
  <a id="drop2" href="#" class="dropdown-toggle" data-toggle="dropdown">Date <b class="caret"></b></a>
  <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
    <li role="menuitem" class="divider"></li>
    <li role="menuitem"><a href="all">All Dates</a></li>
  </ul>
</li>
-->
</ul>

</div>
</div>
</div> 

    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <?php
    $rows = getDatabase()->all(" 
      select m.id,m.category,id,meetid,date(starttime) starttime
      from meeting m 
        join category c on c.category = m.category 
      ".
      ($category == '' ? '' : ' where c.category = :category ')
      ."
      order by starttime desc ",
      array('category' => $category));
    foreach ($rows as $r) { 
      $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$r['meetid']}&doctype");
      $myurl = htmlspecialchars($OTT_WWW."/meetings/{$r['category']}/{$r['meetid']}");
      ?>
	    <tr>
	      <td style="width: 90px; text-align: center;"><?php print $r['starttime']; ?></td>
	      <td style="width: 90px; text-align: center;"><a class="btn btn-mini" href="<?php print $myurl; ?>">Agenda</a></td>
	      <td>
        <?php print meeting_category_to_title($r['category']); ?>
        </td>
	      <td>
        <?php
        $count = getDatabase()->one(" select count(1) c from item where meetingid = :id ",array('id'=>$r['id']));
        print "{$count['c']} items";
        ?>
        </td>
	    </tr>
      <?php
    }
    ?>
    </table>
    <?php
    bottom();
  }

  /*
   */
  static public function downloadAndParseMeeting ($id) {

    $m = getDatabase()->one(" select * from meeting where id = :id ",array('id'=>$id));
    if (!$m['id']) { 
      print "downloadAndParseMeeting for $id :: NOT FOUDN\n";
      return; 
    }

    print "downloadAndParseMeeting for meeting:$id\n";

    # get agenda HTML
    $agenda = file_get_contents(self::getDocumentUrl($m['meetid'],'AGENDA')); 
    file_put_contents("agenda.html",$agenda);
    #$agenda = file_get_contents("agenda.html");

    # charset issues
    $agenda = mb_convert_encoding($agenda,"ascii");

    # XML issues
    $agenda = preg_replace("/&nbsp;/"," ",$agenda);
	  $lines = explode("\n",$agenda);

    # get members information
    $members = array();
    $raw = '';
    for ($x = 0; $x < count($lines); $x++) {
      $raw .= $lines[$x];
      if (preg_match("/DECLARATIONS/i",$lines[$x])) {
				break;
      }
    }
    $raw = preg_replace("/[^a-zA-Z-]+/"," ",$raw);
    $raw = preg_replace("/  /"," ",$raw);
    $raw = preg_replace("/  /"," ",$raw);
    $raw = preg_replace("/  /"," ",$raw);
    $raw = preg_replace("/  /"," ",$raw);
    $raw = explode(" ",$raw);
    for ($x = 0; $x < (count($raw)-1); $x++) {
      $a = $raw[$x];
      $b = $raw[$x+1];
      $row = getDatabase()->one(" select * from electedofficials where lower(last) = :last and lower(left(first,1)) = :first ",array("first"=>$a,"last"=>$b));
      if ($row['id']) {
        $members[] = $row['id'];
      }
    }
    getDatabase()->execute(" update meeting set members = :members where id = :id ",array(
      'members'=> json_encode($members),
      'id' => $id
    ));

    # get coordinator information
    for ($x = 0; $x < count($lines); $x++) {
      $matches = array();
      $lines[$x] = preg_replace("/\r/","",$lines[$x]);
      if (preg_match("/(.*), Committee Coordinator/",$lines[$x],$matches)) {
        $coordName = $matches[1];
        $coordPhone = $lines[$x+1];
        $coordEmail = $lines[$x+2];
        $coordPhone = preg_replace("/<.*/","",$coordPhone);
        $coordEmail = preg_replace("/<.*/","",$coordEmail);
        break;
      }
      if (preg_match("/(.*), Coordinator/",$lines[$x],$matches)) {
        $coordName = $matches[1];
        $coordPhone = $lines[$x+1];
        $coordEmail = $lines[$x+2];
        $coordPhone = preg_replace("/<.*/","",$coordPhone);
        $coordEmail = preg_replace("/<.*/","",$coordEmail);
        break;
      }
    }
    getDatabase()->execute(" update meeting set contactName = :name, contactEmail = :email, contactPhone = :phone where id = :id ",array(
      'name' => $coordName,
      'phone' => $coordPhone,
      'email' => $coordEmail,
      'id' => $id
    ));

    # rebuild item rows
    getDatabase()->execute(" delete from item where meetingid = :id ",array('id'=>$id));

    # scrape out item IDs, and titles.
    $add = 0;
    $spool = array();
	  foreach ($lines as $line) {
      $line = preg_replace("/\r/","",$line);
	    if (preg_match("/itemid=/",$line)) {
        $add = 1;
	      $itemid = $line;
	      $itemid = preg_replace("/.*itemid=/","",$itemid);
	      $itemid = preg_replace('/".*/',"",$itemid);
	      $items[] = $itemid;
        array_push($spool,$line);
        continue;
	    }
	    if ($add && preg_match("/a>/",$line)) {
        $add = 0;
        array_push($spool,$line);
        $snippet = "<a ".implode($spool)."\n";
        $snippet = preg_replace("/<\/a>.*/","</a>",$snippet);
        $snippet = preg_replace("/target=pubright/",'',$snippet);
        $snippet = preg_replace("/lang=[^>]*/",'',$snippet);
        $snippet = preg_replace("/\n/",' ',$snippet);
        $snippet = preg_replace("/\r/",' ',$snippet);
        $snippet = preg_replace("/<i>/","",$snippet);
        $snippet = preg_replace("/<\/i>/","",$snippet);
        $snippet = preg_replace("/  /"," ",$snippet);
        $snippet = preg_replace("/  /"," ",$snippet);
        $snippet = preg_replace("/  /"," ",$snippet);
        $snippet = preg_replace("/  /"," ",$snippet);
        $xml = simplexml_load_string($snippet);
				if (!is_object($xml)) {
					print "WARNING, bad snippet\n";
					print ">> $snippet <<\n";
					$title = '<i class="icon-warning-sign"></i> Doh! title autodection failed';
				} else {
	        $title = $xml->xpath("//span"); 
          if (count($title) == 0) {
            # fall back to <a>
	          $title = $xml->xpath("//a"); 
            $title = $title[0].'';
          } else {
            $title = $title[0].'';
          }

	        # charset problems
	        $title = preg_replace("/ \? /"," - ",$title);
	        $title = preg_replace("/\?/","'",$title);
	
	        # fix open/close brace, and spaces next to braces
	        #$title = preg_replace("/^\(/","",$title);
	        #$title = preg_replace("/\)\s*$/","",$title);
	        $title = preg_replace("/\( +/","(",$title);
	        $title = preg_replace("/ +\)/",")",$title);

				}
	  	  $dbitemid = getDatabase()->execute('insert into item (meetingid,itemid,title) values (:meetingid,:itemid,:title) ', array(
	  	    'meetingid' => $id,
	  	    'itemid' => $itemid,
	  	    'title' => $title,
	  	  ));
        $spool = array();
      }
      if ($add) {
        array_push($spool,$line);
      }
	  }
    
    # purge existing files; not needed as delete ITEM cascades
    # getDatabase()->execute(" delete from ifile where itemid in (select id from item where meetingid = :id) ",array('id'=>$id));

    # go back to the database to build up the "items" in this meeting, and then go grab
    # all the files too.
    $items = getDatabase()->all(' select * from item where meetingid = :id ', array('id' => $id));

	  foreach ($items as $item) {
      print "  item:{$item['id']} title: {$item['title']}\n";

      # look for references to addresses in the item title
      $words = explode(" ",$item['title']);
      for ($x = 0; $x < (count($words)-1); $x++) {
        $number = $words[$x];
        if (!preg_match("/\d+/",$number)) {
         continue;
        }
        # try name=X=1 and also name="(x+1) (x+1)"
        for ($y = 1; $y <= 2; $y++) {
          $name = '';
          for ($z = 0; $z < $y; $z++) {
  	        $name .= $words[$x+1+$z]." ";
          }
          $name = trim($name);
	        $roads = getDatabase()->all("
	          select *
			      from roadways 
			      where 
			        rd_name = upper(:name) 
			        and (
			          (:number % 2 = left_from % 2 and (:number between cast(left_from as unsigned) and cast(left_to as unsigned)))
			          or (:number % 2 = left_from % 2 and (:number between cast(left_to as unsigned) and cast(left_from as unsigned)))
			          or (:number % 2 = right_from % 2 and (:number between cast(right_from as unsigned) and cast(right_to as unsigned)))
			          or (:number % 2 = right_from % 2 and (:number between cast(right_to as unsigned) and cast(right_from as unsigned)))
			        )
	          ",array(
		          'number' => $number,
		          'name' => $name
	        ));
	        if (count($roads) > 0) {
            #TODO: what if match may? should probably disambituate based on suffix.
            $road = $roads[0];
	          print "[$x]: ".$words[$x]." -- ".$words[$x+1]." matched ".count($roads)." roads \n";
				    $placeid = getDatabase()->execute(" insert into places (roadid,rd_num,itemid) values (:roadid,:rd_num,:itemid) ",array(
				      'roadid' => $road['OGR_FID'],
				      'rd_num' => $number,
				      'itemid' => $item['id'],
				    ));
            $geo = getAddressLatLon($number,$name);
            if ($geo->status == 'OK') {
              $lat = $geo->results[0]->geometry->location->lat;
              $lon = $geo->results[0]->geometry->location->lng;
  				    getDatabase()->execute(" update places set shape = PointFromText('POINT($lon $lat)') where id = $placeid ");
            }
	        }
        }
      }

	    $html = file_get_contents(self::getItemUrl($item['itemid']));
		  $lines = explode("\n",$html);
	    $files = array();
		  foreach ($lines as $line) {
		    if (preg_match("/fileid=/",$line)) {
          $line = preg_replace("/\n/","",$line);
          $line = preg_replace("/\r/","",$line);

          $title = $line;
          $title = preg_replace("/.*&nbsp;/","",$title);
          $title = preg_replace("/<.*/","",$title);

		      $fileid = $line;
		      $fileid = preg_replace("/.*fileid=/","",$fileid);
		      $fileid = preg_replace('/".*/',"",$fileid);

          print "    file: $title\n";
		  	  getDatabase()->execute('insert into ifile (itemid,fileid,title,created,updated) values (:itemid,:fileid,:title,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP) ', array(
		  	    'itemid' => $item['id'],
		  	    'fileid' => $fileid,
		  	    'title' => $title,
		  	  ));
		    }
		  }
	  }

  }

  static public function downloadAndParseFile ($id) {

    # DEBUG ONLY
    # getDatabase()->execute(" update ifile set md5 = null where id = :id ",array("id"=>$id));

    $file = getDatabase()->one(" select * from ifile where id = :id ",array("id"=>$id));
    if (!$file['id']) {
      print "Fileid not found: $id\n";
      return;
    }

    # get the file data
    print "downloading file: $id\n";
    $pdf = file_get_contents(OttWatchConfig::WWW . "/meetings/file/".$file['fileid']);
    #file_put_contents("tmp.pdf",$pdf);
    #$pdf = file_get_contents("tmp.pdf");

    $md5 = md5($pdf);
    if ($md5 == $file['md5']) {
      print "md5 is the same as existing file, no need to update\n";
      return;
    }
    getDatabase()->execute(" update ifile set md5 = :md5 where id = :id ",array("md5"=>$md5,"id"=>$id));

    # save to VAR, split into individual pages
    print "  saving to disk\n";
    $saveas = OttWatchConfig::FILE_DIR."/pdf/".$file['fileid'].'.pdf';
    file_put_contents($saveas,$pdf);

    # get the text, page by page
    print "  converting to text\n";
    $txtfile = OttWatchConfig::FILE_DIR."/pdf/".$file['fileid'].'.txt';
    `pdftotext -layout -nopgbrk $saveas`;
    $txt = file_get_contents($txtfile);
    getDatabase()->execute(" update ifile set txt = :txt where id = :id ",array("txt"=>$txt,"id"=>$id));

    # now do word analysis
    print "  inserting words\n";
    getDatabase()->execute(" delete from ifileword where fileid = :id ",array("id"=>$id));
    $lines = explode("\n",$txt);
    $docoffset = 0;
    for ($x = 0; $x < count($lines); $x++) {
	    $wordlist = preg_split("/[^a-zA-Z0-9]+/",$lines[$x]);
      for ($y = 0; $y < count($wordlist); $y++) {
        if ($wordlist[$y] == '') { continue; }
        getDatabase()->execute(" insert into ifileword (fileid,word,line,offset,docoffset) values (:id,lower(:word),:line,:offset,:docoffset) ",array(
          "id" => $id,
          "word" => $wordlist[$y],
          "line" => $x,
          "offset" => $y,
          "docoffset" => $docoffset,
        ));
        $docoffset ++;
      }
    }

  }
  
}

?>
