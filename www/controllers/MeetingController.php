<?php

class MeetingController {
  
  static public function doList ($category) {
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
      select m.category,id,meetid,date(starttime) starttime
      from meeting m 
        join category c on c.category = m.category 
      ".
      ($category == '' ? '' : ' where c.category = :category ')
      ."
      order by starttime desc ",
      array('category' => $category));
    foreach ($rows as $r) { 
      $mtgurl = htmlspecialchars("http://app05.ottawa.ca/sirepub/mtgviewer.aspx?meetid={$r['meetid']}&doctype");
      ?>
	    <tr>
	      <td style="width: 90px; text-align: center;"><?php print $r['starttime']; ?></td>
	      <td style="width: 90px; text-align: center;"><a class="btn btn-mini" target="_blank" href="<?php print $mtgurl; ?>=AGENDA">Agenda</a></td>
	      <td><?php print meeting_category_to_title($r['category']); ?></td>
	    </tr>
      <?php
    }
    ?>
    </table>
    <?php
    bottom();
  }
  
}

?>


