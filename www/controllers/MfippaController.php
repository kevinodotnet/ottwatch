<?php

class MfippaController {

	const CONVERT = "/usr/bin/convert";

  public static function cleanSummary($summary) {
    $summary = preg_replace('/[éééé]/','e',$summary);
    $summary = preg_replace('/[^a-zA-Z0-9,\. ]/',' ',$summary);
    return $summary;
  }

  public static function summaryOCR($id) {
    $row = getDatabase()->one(" select * from mfippa where id = :id or tag = :id ",array('id'=>$id));
    if (!$row['id']) { 
      print "Not found\n";
      return; 
    }
    $id = $row['id'];

    # crop the thumbnail again, so we only have the summary

    $pagesdir = OttWatchConfig::FILE_DIR."/mfippa/{$row['source']}";
    $thumb = "$pagesdir/mfippa_crop_{$row['id']}.png";
    $size = getimagesize($thumb);

    # crop the summary to a new file
    $summary = "$pagesdir/mfippa_summary_{$row['id']}.png";
    $ocr = "$pagesdir/mfippa_ocr_{$row['id']}";

    $x = round($size[0]*.375);
    $x1 = round($size[0]*.468);
    $y = 0;
    $width = $size[0]-$x-$x1;
    $height = $size[1]-$y;
    $cmd = self::CONVERT . " '{$thumb}' +repage -crop {$width}x{$height}+{$x}+{$y} {$summary}";
    system($cmd);
    system(" tesseract '{$summary}' '{$ocr}' 2>/dev/null");
    $text = `cat {$ocr}* `;
    $text = preg_replace('/\r/',' ',$text);
    $text = preg_replace('/\n/',' ',$text);
    $text = preg_replace('/  /',' ',$text);
    $text = trim($text);

    db_update('mfippa',array('id'=>$id,'summary'=>$text),'id');

		print "{$row['tag']} >>> $text >>> $summary\n";
  }

  /* display a single mfippa */
  public static function showRandom() {
    $row = getDatabase()->one(" select * from mfippa where published = 1 and tag is not null order by rand() ");
    self::show($row['id']);
  }

  public static function show($id) {

    $row = getDatabase()->one(" select * from mfippa where id = :id or tag = :id ",array('id'=>$id));
    if (!LoginController::isAdmin()) {
    if (!$row['published']) {
      top();
      print "Sorry {$row['tag']} is not yet published.\n";
      return;
      bottom();
    }
    }

    $id = $row['id']; // if search by tag, fix arg back to id
    top($row['tag'].' MFIPPA - City of Ottawa');

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
      if (preg_match('/([AP])-(\d+)-(\d+)/',$prev['tag'],$matches)) {
        $row['tag'] = $matches[1].'-'.$matches[2].'-'.sprintf('%05d',$matches[3]+1);
      }
    }
    if ($row['created'] == '') {
			$row['created'] = substr($prev['created'],0,10);
		}

    $submit = $_GET['submit'];
    $tag = $_GET['tag'];
    $from = $_GET['from'];
    $closed = $_GET['closed'];
    $created = $_GET['created'];
    if ($submit == 'Save') {

      $values = array();
      $values['id'] = $id;
      $values['tag'] = $tag;
      
      # convert 'closed' from 'd-m-y' to 'yyyy-mm-dd'
      $matches = array();
      if ($created != '') {
				$values['created'] = $created;
      }
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
	    <a class="btn" href="<?php print $prev['tag']; ?>">Prev</a>
	    <a class="btn" href=".">Mfippa Home</a>
	    <a class="btn" href="random">Random</a>
	    <a class="btn" href="<?php print $next['tag']; ?>">Next</a>
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
    <input type="text" name="created" value="<?php print $row['created']; ?>" style="width: 100px;"/>
    <input type="text" name="closed" value="<?php print $row['closed']; ?>" style="width: 100px;"/><br/>
    <input class="btn" type="submit" name="submit" value="Save"/>
    </div>
    </form>
    <?php
    }
    ?>

    
    <h1><?php print $row['tag']; ?></h1>
    <div class="row-fluid">
    <div class="offset2 span2">
    <b><i>OCR Text</i></b><br/>(won't be perfect): 
    </div>
    <div class="span5">
    <?php print self::cleanSummary($row['summary']); ?>
    </div>
    </div>

    <div class="row-fluid visible-desktop" style="padding-top: 20px;">
    <div class="offset8 span1">
    <b>Received</b>
    </div>
    <div class="span1">
    <b>Due</b>
    </div>
    <div class="span1">
    <b>Closed</b>
    </div>
    </div>
    <img style="border-top: solid 1px #000000; border-bottom: 1px solid #000000;" src="<?php print $src; ?>"/><br/>

    </center>

    <div class="row-fluid" style="margin-top: 20px; padding-top: 20px;">

    <div class="span6">
    <h2>Get This Data</h2>
    <p>
    OttWatch does not have a copy of this information, but you can ask for a copy of it by <a href="http://ottawa.ca/en/city-hall/your-city-government/policies-and-administrative-structure/how-and-where-submit-request">making
    your own MFIPPA request</a> to the City of Ottawa, referencing <b><?php print $row['tag']; ?></b>.
    </p>
    <p>
    I haven't tried to do this yet, but something along the lines of "<i>Please provide a copy of the
    MFIPPA requests for <?php print $row['tag']; ?></i>" should suffice.
    </p>
    <p>
    If you do grab a copy, please let me know (kevino@kevino.net) and I'll upload it here. There's
    a good chance someone else will want it too. Drop a note in the <b>Disqus</b> comments if you
    are making the request (to avoid doubling up).
    </p>
    </div>
    <div class="span6">
    <h2>Discussion</h2>
    <?php disqus(); ?>
    </div>

    </div><!--/row-->

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
    if (!LoginController::isAdmin()) {
    if (!$row['published']) {
      return;
    }
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
    top('MFIPPA Requests');

    if (LoginController::isAdmin()) {
      print "<b>Process MFIPPA results</b>\n";
      $req = self::getOttWatchMfippa();
      foreach ($req as $r) {
        print "<a href=\"process/{$r}\">{$r}</a><br/>";
      }
      print "<hr/>";
    }

    ?>
    <div class="row-fluid">
    <div class="span4">
    <h1>MFIPPA Requests</h1>
    </div>
    <div class="span8">
    <p class="lead">
    A list of all <i>Municipal Freedom of Information and Protection of Privacy Act</i> requests 
    to the City of Ottawa for the period 2013-Jan to 2013-Jun.
    </p>
    <p>* The <b><i>summary</i></b> text is generated by OCR and will not be perfect. Click through to view the original image.</p>
    </div>
    </div>

    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <?php
    if (LoginController::isAdmin()) {
    	$rows = getDatabase()->all(" select * from mfippa order by source desc, tag desc, id desc ");
		} else {
    	$rows = getDatabase()->all(" select * from mfippa where published = 1 and tag is not null order by tag desc ");
		}
    $prevmonthyear = '';
    foreach ($rows as $r) {
      $monthyear = date('F, Y',strtotime($r['created']));
			if ($prevmonthyear != $monthyear) {
				?>
    <tr>
    <th colspan="2"><h2><?php print $monthyear; ?></h2></th>
    </tr>
				<?php
			}
			$prevmonthyear = $monthyear;
      $summary = self::cleanSummary($r['summary']);
			$href = $r['tag'];
			if ($href == '') {
				$href = $r['id'];
			}
      ?>
      <tr>
      <td><nobr><a href="<?php print $href; ?>"><?php print $href; ?></a></nobr></td>
      <td><?php print $summary; ?></td>
      </tr>
      <?php
    }
    ?>
    </table>

    <?php
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
	        $cmd = "$convert '{$pageFiles[$page]}' -scale '$perc%' {$scaleout}";
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
		pr($size);
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
    return array('A-2013-00196','A-2013-00594','A-2013-00687');
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
