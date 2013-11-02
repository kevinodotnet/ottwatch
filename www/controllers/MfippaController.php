<?php

class MfippaController {

  /* main GUI for web */
  public static function doList() {
    top();

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

        $scaleout = "$pagesdir/page_scaled_$page_$scale.png";

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
        print "page: $page x: $x y: $y\n";
      }

      #http://localhost/ottwatch/mfippa/process/A-2013-00594?x=136&y=280

      # show the page as an image map; user is expected to click on the top-left corner of the start of
      # an MFIPPA result
      top();

      $imgW = 1000;
      $scale = $imgW/$size[0];
      $imgH = $size[1] * $scale;

      ?>
      <center>
      <canvas id="canvas" width="<?php print $imgW; ?>" height="<?php print $imgH; ?>" style="border: 1px solid #ff0000;">
      </canvas>
      <script>
	      var canvas = document.getElementById('canvas');
	      var context = canvas.getContext('2d');

	      var imageObj = new Image();
	      imageObj.onload = function() {
	        context.drawImage(imageObj,0,0);
        context.beginPath();
        context.arc(<?php print $x; ?>, <?php print $y; ?>, 5, 0, Math.PI*2, true); 
        context.closePath();
        context.fill();
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
