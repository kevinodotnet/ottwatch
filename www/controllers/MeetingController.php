<?php

class MeetingController {
  
  static public function doList ($category) {
    top();
    ?>

<div id="navbar-example" class="navbar navbar-static">
<div class="navbar-inner">
<div class="container" style="width: auto;">

<ul class="nav" role="navigation">
<li class="active">
</li>
<li class="dropdown">
  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Categories <b class="caret"></b></a>
  <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
    <?php
    $rows = getDatabase()->all(" select category,title from category order by title ");
    foreach ($rows as $r) { 
      ?>
      <li role="menuitem"><a href="<?php print urlencode($r['category']); ?>"><?php print $r['title']; ?></a></li>
      <?php
    }
    ?>
    <!--
    <li role="presentation" class="divider"></li>
    <li role="presentation"><a role="menuitem" tabindex="-1" href="all">All</a></li>
    -->
</ul>
</li>
</ul>

</div>
</div>
</div> 
    <?php

    bottom();
    return;
    ?>


    <table>
    <?php
    $rows = getDatabase()->all(" select * from meeting m join category c on c.category = m.category order by starttime desc ");
    foreach ($rows as $r) { 
      print <<<EOF
      <tr>
      <td>{$r['title']}</td>
      </tr>
EOF;
    }
    ?>
    </table>
    <?php
    bottom();
  }
  
}

?>


