<?php

class MfippaController {

	const CONVERT = "/usr/bin/convert";

  /* display a single mfippa */
  public static function showRandom() {
    $row = getDatabase()->one(" select * from mfippa where tag is null order by id ");
    self::show($row['id']);
  }

  public static function show($id) {

    top();
    $row = getDatabase()->one(" select * from mfippa where id = :id ",array('id'=>$id));

    $row['closed'] = substr($row['closed'],0,10);

    if (!$row['id']) { 
      print "MFIPPA NOT FOUND";
      bottom();
      return;
    }
    $src = OttWatchConfig::WWW."/mfippa/$id/img";
    $next = self::getNext($row['id']);
    $prev = self::getPrev($row['id']);

    if ($row['tag'] == '') {
      $matches = array();
      if (preg_match('/A-(\d+)-(\d+)/',$prev['tag'],$matches)) {
        $row['tag'] = 'A-'.$matches[1].'-'.sprintf('%05d',$matches[2]+1);
      }
    }

    $submit = $_GET['submit'];
    $tag = $_GET['tag'];
    $from = $_GET['from'];
    $closed = $_GET['closed'];
    if ($submit == 'Save') {

      $values = array();
      $values['id'] = $id;
      $values['tag'] = $tag;
      
      # convert 'closed' from 'd-m-y' to 'yyyy-mm-dd'
      $matches = array();
      if ($closed != '') {
	      if (preg_match('/(\d+)-(\d+)-(\d+)/',$closed,$matches)) {
          $closed = $matches[3].'-'.$matches[1].'-'.$matches[2];
          $values['closed'] = $matches[3].'-'.$matches[2].'-'.$matches[1];
	      }
      }
      db_update('mfippa',$values);
      header("Location: {$next['id']}");
      return;
    }

    # http://localhost/ottwatch/mfippa/58?tag=A-2013-00001&from=public&closed=3-1-2013&submit=Save

    ?>

    <center>
    <p>
	    <a class="btn" href="<?php print $prev['id']; ?>">Prev</a>
	    <a class="btn" href="<?php print $next['id']; ?>">Next</a>
    </p>
    <?php
    if (LoginController::isAdmin()) {
    ?>
    <form>
    <div style="float: left; text-align: left; padding-top: 25px; ">
    <span style="padding-left: 130px;">
    <input style="font-size: 14pt;" type="text" name="tag" value="<?php print $row['tag']; ?>"/>
    </span>
    </div>
    <div style="text-align: right; padding-right: 120px;">
    <input type="text" name="closed" value="<?php print $row['closed']; ?>" style="width: 100px;"/><br/>
    <input class="btn" type="submit" name="submit" value="Save"/>
    </div>
    </form>
    <?php
    }
    ?>
    <img style="" src="<?php print $src; ?>"/>
    </center>

    <?php
    bottom();
  }

  public static function getPrev($id) {
    $row = getDatabase()->one(" select * from mfippa where id = :id ",array('id'=>$id));
    $next = getDatabase()->one("
      select *
      from mfippa 
      where
        (source = :source and page = :page and y < :y)
        or (source = :source and page < :page)
      order by page desc, y desc
      ",array('source'=>$row['source'],'page'=>$row['page'],'y'=>$row['y']));
    return $next;
  }

  public static function getNext($id) {
    $row = getDatabase()->one(" select * from mfippa where id = :id ",array('id'=>$id));
    $next = getDatabase()->one("
      select *
      from mfippa 
      where
        (source = :source and page = :page and y > :y)
        or (source = :source and page > :page)
      order by page, y
      ",array('source'=>$row['source'],'page'=>$row['page'],'y'=>$row['y']));
    return $next;
  }

  public static function createImg($id) {
    $row = getDatabase()->one(" select * from mfippa where id = :id ",array('id'=>$id));
    if (!$row['id']) {
      return;
    }

    # find the 'next' mfippa from the same source
    $next = self::getNext($row['id']);
  
    # where to save the file
    $pageFiles = self::getPageFiles($row['source']);
    $pagefile = $pageFiles[$row['page']];
    $size = getimagesize($pagefile);
    $pagesdir = OttWatchConfig::FILE_DIR."/mfippa/{$row['source']}";
    $thumb = "$pagesdir/mfippa_crop_{$row['id']}.png";

    $convert = self::CONVERT;

    # calcualte the box/extend for this id, based on its start position and the
    # start position of the next mfippa. 'SCALE' is used because database x/y
    # were based on WIDTH=1000
    $scale = 1000/$size[0];
    if ($next['id']) {
      if ($row['page'] == $next['page']) {
	      # majority case
		    $y = round(($row['y']-10)/$scale);
		    $x = 0;
		    $height = round(($next['y']-$row['y'])/$scale);
		    $width = $size[0];
      } else {
        # join end of this page with start of next page
        $thumba = "$pagesdir/mfippa_crop_{$row['id']}-a.png";
        $thumbb = "$pagesdir/mfippa_crop_{$row['id']}-b.png";

		    $x = 0;
		    $y = round(($row['y']-10)/$scale);
		    $width = $size[0];
		    $height = $size[1]-$y;
		    $cmd = "$convert '{$pagefile}' -crop {$width}x{$height}+{$x}+{$y} {$thumba}";
		    system($cmd);

        # next page
		    $x = 0;
        $y = 0;
		    $width = $size[0];
		    $height = round(($next['y']-10)/$scale);
        $pagefile = $pageFiles[$next['page']];
		    $cmd = "$convert '{$pagefile}' -crop {$width}x{$height}+{$x}+{$y} {$thumbb}";
		    system($cmd);

        # combine
        $cmd = " $convert '{$thumba}' '{$thumbb}' -append {$thumb} ";
        system($cmd);
        return;
      }
    } else {
	    $y = round(($row['y']-10)/$scale);
	    $x = 0;
	    $height = $size[1]-$y;
	    $width = $size[0];
    }

    $cmd = "$convert '{$pagefile}' -crop {$width}x{$height}+{$x}+{$y} {$thumb}";
    system($cmd);
  }

  public static function showImg($id) {
    $row = getDatabase()->one(" select * from mfippa where id = :id ",array('id'=>$id));
    if (!$row['id']) { 
      return;
    }
    $pagesdir = OttWatchConfig::FILE_DIR."/mfippa/{$row['source']}";
    $thumb = "$pagesdir/mfippa_crop_{$row['id']}.png";
    if (!file_exists($thumb)) {
      self::createImg($id);
    }
    header('Content-Type: image/png');
    print file_get_contents($thumb);
    return;


  }

  /* main GUI for web */
  public static function doList() {
    top();

    $rows = getDatabase()->all(" select * from mfippa order by source, page, y ");
    foreach ($rows as $r) {
      $src = OttWatchConfig::WWW."/mfippa/{$r['id']}/img";
      ?>
      <div style="padding-top: 10px; padding-bottom: 10px; border: solid 1px #ff0000;">
      <a href="<?php print $r['id']; ?>"><img src="<?php print $src; ?>"/></a>
      </div>
      <?php
    }

    if (LoginController::isAdmin()) {
      print "<h1>Process MFIPPA results</h1>\n";
      $req = self::getOttWatchMfippa();
      foreach ($req as $r) {
        print "<a href=\"process/{$r}\">{$r}</a><br/>";
      }
    }

    bottom();
  }

  /* admin only page for processing PDF results to public rendition */
  public static function process($mfippa_id) {

    $page = $_GET['page'];
    $png = $_GET['png'];
    $x = $_GET['x'];
    $y = $_GET['y'];
    $scale = $_GET['scale'];

    $pdffile = OttWatchConfig::FILE_DIR."/mfippa/{$mfippa_id}.pdf";
    $pagesdir = OttWatchConfig::FILE_DIR."/mfippa/{$mfippa_id}";
    $pageFiles = self::getPageFiles($mfippa_id);
    $size = getimagesize($pageFiles[$page]);

    if (! file_exists($pdffile)) {
      top();
      print "PDF file not found.\n";
      bottom();
      return;
    }
    if (! file_exists($pagesdir)) {
      top();
      print "PDF to PAGES directory not found\n";
      bottom();
      return;
    }

    if (preg_match('/\d+/',$page)) {

      if ($png == 1) {
        $convert = self::CONVERT;
	      if (preg_match('/^\d+$/',$x)) {
		      if (preg_match('/^\d+$/',$y)) {
            # dump a crop of the image

            $width = $size[0];
            $height = 500;
            $cropout = "$pagesdir/crop-out.png";

            system("$convert '{$pageFiles[$page]}' -crop {$width}x{$height}+0+0 {$cropout}");
            header('Content-Type: image/png');
            print file_get_contents($cropout);
            unlink($cropout);
            return;
		      }
	      }

        if ($scale == '') {
          # simple dump
	        $data = file_get_contents($pageFiles[$page]);
	        header('Content-Type: image/png');
	        print $data;
	        return;
        }

        #$scaleout = "$pagesdir/page_scaled_{$page}_{$scale}.png";
        $scaleout = "$pagesdir/page_scaled_{$page}.png";

        if (!file_exists($scaleout)) {
	        $perc = $scale * 100;
	        $cmd = "$convert '{$pageFiles[$page]}' -scale $perc% {$scaleout}";
	        system($cmd);
        }

        header('Content-Type: image/png');
        print file_get_contents($scaleout);
        return;

      }

      if ($_GET['saveA'] == 1) {
        $values = array();
        $values['source'] = $mfippa_id;
        $values['x'] = $x;
        $values['y'] = $y;
        $values['page'] = $page;
        $id = db_insert('mfippa',$values);
        $prev = self::getPrev($id);
        if ($prev['id'] != $id) {
          # don't do on first
          self::createImg($prev['id']);
        }
        #header("Location: ".OttWatchConfig::WWW."/mfippa/process/$mfippa_id?page=$page");
        return;
      }

      #http://localhost/ottwatch/mfippa/process/A-2013-00594?x=136&y=280

      # show the page as an image map; user is expected to click on the top-left corner of the start of
      # an MFIPPA result

      $imgW = 1000;
      $scale = $imgW/$size[0];
      $imgH = $size[1] * $scale;

      $dots = getDatabase()->all(" select * from mfippa where source = :source and page = :page ",array('source'=>$mfippa_id,'page'=>$page));

      top();
      ?>
      <center>
      <div style="font-size: 18pt; padding-right: 330px; float: right;"><?php print $page+1; ?></div>
      <a class="btn" href="?page=<?php print $page+1; ?>">Next</a><br/>
      <canvas id="canvas" width="<?php print $imgW; ?>" height="<?php print $imgH; ?>" style="">
      </canvas><br/>
      <a class="btn" href="?page=<?php print $page+1; ?>">Next</a><br/>
      <script>
	      var canvas = document.getElementById('canvas');
	      var context = canvas.getContext('2d');

	      var imageObj = new Image();
	      imageObj.onload = function() {
	        context.drawImage(imageObj,0,0);

          <?php
          foreach ($dots as $d) {
            ?>
		        context.beginPath();
		        context.arc(<?php print $d['x']; ?>, <?php print $d['y']; ?>, 5, 0, Math.PI*2, true); 
		        context.closePath();
		        context.fill();
            <?php
          }
          ?>


	      };
	      imageObj.src = '<?php print "?scale=$scale&png=1&page=$page"; ?>';

        canvas.addEventListener('click', function(event) { 
          c = document.getElementById('canvas');
          x = event.pageX - c.offsetLeft;
          y = event.pageY - c.offsetTop;

		        context.beginPath();
            context.fillStyle = '#f00';
            context.strokeStyle = '#f00';
		        context.arc(x, y, 5, 0, Math.PI*2, true); 
		        context.closePath();
		        context.fill();

          url = '?saveA=1&x=' + x + '&y=' + y + '&page=<?php print $page; ?>';
          $.get( url );
        }, false);

      </script>
      </center>
      <?php
      bottom();
      return;
    }

    top();
    print "<h1>$mfippa_id</h1>\n";
    print "Choose a page to process: ";
    foreach (array_keys($pageFiles) as $page) {
      print "<a href=\"?page={$page}\">{$page}</a> ";
    }

    bottom();
  }

  public static function getOttWatchMfippa() {
    # TODO: use opendir() to read PDF files, or move to database.
    # low volume function though so for now lazy and just changing
    # this code as MFIPPA-on-MFIPPA are processed added manually.
    return array('A-2013-00594');
  }


  public static function getPageFiles($mfippa_id) {
    $pages = array();
    $pagesdir = OttWatchConfig::FILE_DIR."/mfippa/{$mfippa_id}";
    $d = opendir($pagesdir);
    while (($file = readdir($d)) !== false) {
      if (preg_match('/^\./',$file)) { continue; }
      if (!preg_match('/^page-\d+\.png/',$file)) { continue; }
      $pages[] = "$pagesdir/$file";
    }
    closedir($d);
		asort($pages);
		$sorted = array();
		foreach ($pages as $p) {
			$sorted[] = $p;
		}
    return $sorted;
  }

  public static function pdfToPages($mfippa_id) {

    $pdffile = OttWatchConfig::FILE_DIR."/mfippa/{$mfippa_id}.pdf";
    $pagesdir = OttWatchConfig::FILE_DIR."/mfippa/{$mfippa_id}";

    if (!file_exists($pdffile)) {
      print "ERROR: pdf file not found: $pdffile\n";
      return;
    }

    # delete any calculated data for this PDF, then start fresh directory
    if (file_exists($pagesdir)) {
      $pageFiles = self::getPageFiles($mfippa_id);
      foreach ($pageFiles as $file) {
        unlink($file);
      }
      rmdir($pagesdir);
    } 
    mkdir($pagesdir);

    # create PNG pages
    $cmd = " pdftoppm ";
    $cmd .= " -png ";
    $cmd .= " '$pdffile' ";
    $cmd .= " '$pagesdir/page' ";
    system($cmd);

  }

}


?>
