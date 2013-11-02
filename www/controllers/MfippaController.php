<?php

class MfippaController {

  /* display a single mfippa */
  public static function show($id) {
    top();
    $row = getDatabase()->one(" select * from mfippa where id = :id ",array('id'=>$id));

    if (!$row['id']) { 
      print "MFIPPA NOT FOUND";
      bottom();
      return;
    }
    $src = OttWatchConfig::WWW."/mfippa/$id/img";
    print "<img src=\"$src\"/>";
    bottom();
  }

  public static function createImg($id) {
    $row = getDatabase()->one(" select * from mfippa where id = :id ",array('id'=>$id));
    if (!$row['id']) {
      return;
    }

    # find the 'next' mfippa from the same source
    $next = getDatabase()->one("
      select *
      from mfippa 
      where
        (source = :source and page = :page and y > :y)
        or (source = :source and page > :page)
      order by page, y
      ",array('source'=>$row['source'],'page'=>$row['page'],'y'=>$row['y']));
  
    # where to save the file
    $pageFiles = self::getPageFiles($row['source']);
    $pagefile = $pageFiles[$row['page']];
    $size = getimagesize($pagefile);
    $pagesdir = OttWatchConfig::FILE_DIR."/mfippa/{$row['source']}";
    $thumb = "$pagesdir/mfippa_crop_{$row['id']}.png";

    pr($row);
    pr($next);

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
        # TODO: this result might head to the next page
        # for now just use "to end of page", but it means we might be missing some of the summary
        # if it spanned across.
		    $y = round(($row['y']-10)/$scale);
		    $x = 0;
		    $height = $size[1]-$y;
		    $width = $size[0];
      }
    } else {
	    $y = round(($row['y']-10)/$scale);
	    $x = 0;
	    $height = $size[1]-$y;
	    $width = $size[0];
    }

    $convert = "/opt/local/bin/convert";
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
        $convert = "/opt/local/bin/convert";
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
        db_insert('mfippa',$values);
        header("Location: ".OttWatchConfig::WWW."/mfippa/process/$mfippa_id?page=$page");
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
      <a class="btn" href="?page=<?php print $page+1; ?>">Next</a><br/>
      <canvas id="canvas" width="<?php print $imgW; ?>" height="<?php print $imgH; ?>" style="">
      </canvas>
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
          document.location.href = '?saveA=1&x=' + x + '&y=' + y + '&page=<?php print $page; ?>';
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
    return $pages;
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
