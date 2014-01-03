<?php

class StoryController {

  public static function getPublished() {
		return getDatabase()->all(" select * from story where deleted = 0 and published = 1 order by created desc ");
	}

  public static function doList() {
    top('Stories');
    $rows = self::getPublished();
    foreach ($rows as $r) {

      $preview = strip_tags($r['body'],'<p><div>');
      $preview = preg_replace('/\n/',' ',$preview);
      $preview = preg_replace('/\n/',' ',$preview);
      $preview = preg_replace('/\s+<p/',"\n<p",$preview);
      $lines = explode("\n",$preview);
      $preview = $lines[0];
      $preview = preg_replace('/<\/p>$/','',$preview);
      $preview .= "&nbsp;&nbsp;<b><a href=\"{$r['id']}/{$r['title']}\">Read More...</a></b><p/>";

      ?>
      <div class="row-fluid">
      <div class="span6">
      <h1><a href="<?php print "{$r['id']}/{$r['title']}"; ?>"><?php print $r['title']; ?></a></h1>
      </div>
      </div>
      <div class="row-fluid">
      <div class="span5">
      <?php print $preview; ?>
      </div>
      <div class="span2">
      <center>
      <b>Updated</b><br/>
      <?php print $r['updated']; ?>
      </center>
      </div>
      </div>
      <?php
    }
    bottom();
  }

  public static function add() {

		$row = getDatabase()->one(" select * from people where id = :id ",array('id'=>getSession()->get('user_id')));
		if (!$row['author']) {
			top();
			print "ERROR: you do not have authorship rights\n";
			bottom();
			return;
		}

    $story = array();
    $story['personid'] = getSession()->get('user_id');
    $id = db_insert('story',$story);

    header("Location: edit/$id");
  }

  public static function show($id,$restTitle) {
    $story = getDatabase()->one(" 
      select 
				s.id as storyid,
        s.title,s.body,
        p.name as author,
        left(s.updated,16) updated,
        s.published,s.deleted,
        p.*
      from story s
        join people p on p.id = s.personid
      where 
        s.id = :id 
      ",array('id'=>$id));

    # disallow linking to /story/X/WHATEVER_SOMEONE_TYPED by redirecting if the supplied
    # "url_title" does not match the actual story title. Also helps if story title was 
    # changed but links are already out there on social media
    # title - urlencode - removed junk - lowercase
    $titleToUrlPart = $story['title'];
    $titleToUrlPart = preg_replace('/ /','-',$titleToUrlPart);
    $titleToUrlPart = urlencode($titleToUrlPart);
    $titleToUrlPart = preg_replace('/%../','',$titleToUrlPart);
    $titleToUrlPart = strtolower($titleToUrlPart);
    $storyUrl = OttWatchConfig::WWW."/story/{$id}/{$titleToUrlPart}";
    if ($titleToUrlPart != $restTitle) {
      # new title, or someone is having fun
      header("Location: $storyUrl");
      return;
    }

    top($story['title']);

    if ($story['deleted'] == 1) {
      ?>
      <h1>Error: this story has been deleted</h1>
      <?php
      bottom();
      return;
    }
    if ($story['published'] == 0) {
			$ok = 0;
			if (LoginController::isLoggedIn()) {
		    if ($story['author'] == getSession()->get("user_id")) {
					$ok = 1;
				}
			}
			if (!$ok) {
	      ?>
	      <h1>Error: this story is not yet published</h1>
	      <?php
	      bottom();
	      return;
			}
    }

    $author = getDatabase()->one(" select * from people where id = :id ",array('id'=>$story['author']));

    ?>

    <div class="row-fluid">
    <div class="offset3 span6">
    <center><h1 id="previewtitle"><?php print "{$story['title']}\n"; ?></h1></center>
    <p style="float: right; text-align: right;">
    <b><?php print $author['name']; ?></b><br/>
    <?php print $story['updated']; ?>
    </p>
    <p>
    <div class="fb-like" 
      data-href="<?php print $storyUrl; ?>" 
      data-width="The pixel width of the plugin" 
      data-height="The pixel height of the plugin" 
      data-colorscheme="light" 
      data-layout="button_count" 
      data-action="like"
      data-show-faces="false" 
      data-send="false"></div>
		<a href="https://twitter.com/share" class="twitter-share-button" data-via="OttWatch" data-related="ottwatch" data-hashtags="ottpoli">Tweet</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
    </p>
    </div><!-- /span -->
    </div><!-- /row -->

    <div class="row-fluid">
    <div class="offset3 span6" style="border-top: 1px solid #f0f0f0; padding-right: 5px; padding-top: 20px;">
    <div class="ottwatchstorybody" ><?php print $story['body']; ?></div>
    </div><!-- /span -->
    <div class="span3" style="background: #f0f0f0; padding: 0px 5px; border-radius: 4px;">
		<center><h3>More stories...</h3></center>
		<?php
		$rows = self::getPublished();
		foreach ($rows as $r) {
			if ($r['id'] == $story['storyid']) { continue; }
			?>
			<div style="padding-bottom: 5px;">
			<h5 style="padding: 0px; margin: 0px;"><a href="/story/<?php print $r['id']; ?>"><?php print $r['title']; ?></a></h5>
			<span style="color: #808080;"><?php print $r['created']; ?></span>
			</div>
			<?php
		}
		?>
		</div>
    </div><!-- /row -->

    <div class="row-fluid">
    <div class="offset3 span6" style="border-top: 1px solid #f0f0f0; padding-right: 5px; padding-top: 20px;">
    <?php disqus(); ?>
		</div>
    </div><!-- /row -->
    <?php
    bottom();
  }

  public static function edit($id) {
    top();
    $story = getDatabase()->one(" select * from story where id = :id and personid = :personid ",array('id'=>$id,'personid'=>getSession()->get('user_id')));

    if (!$story['id']) {
      print "Story not found, or you are not the author\n";
      bottom();
      return;
    }

    if ($story['deleted']) {
      print "Story is deleted\n";
      bottom();
      return;
    }

    ?>

    <div class="row-fluid">
    <div class="span6">

    <script>
    function preview() {
      html = $('#storybody').val();
      $('#previewbody').html(html);
      return false;
    }
    </script>
		<script src="<?php print OttWatchConfig::WWW; ?>/vendor/ckeditor/ckeditor.js"></script>
		<script src="<?php print OttWatchConfig::WWW; ?>/vendor/ckeditor/adapters/jquery.js"></script>

    <!--
    Created: <?php print $story['created']; ?><br/>
    Updated: <?php print $story['updated']; ?><br/>
    -->
    <form class="form-horizontal" method="post" action="../save">
    <input type="hidden" name="id" value="<?php print $id; ?>"/>

    <p>
    <center>
    <input style="width: 98%;" type="text" name="title" value="<?php print $story['title']; ?>"/>
    </center>
    </p>
    <p>
    <center>
    <button type="submit" name="save" class="btn">Save</button>
    <?php if ($story['published']) { ?>
    <button type="submit" name="unpublish" value="1" class="btn">Unpublish</button>
    <?php } else { ?>
    <button type="submit" name="publish" value="1" class="btn">Publish</button>
    <?php } ?>
    <button type="submit" name="delete" value="1" class="btn">Delete</button>
    <button type="submit" onclick="return preview()" name="delete" value="1" class="btn">Preview</button>
    <a class="btn" href="<?php print OttWatchConfig::WWW."/story/{$story['id']}"; ?>">Full Preview</a>
    </center>
    </p>
    <textarea id="storybody" name="body" rows="80" style=""><?php print $story['body']; ?></textarea>
    <script>
	    $( '#storybody' ).ckeditor({
        extraAllowedContent: 'script; iframe pre div p blockquote {*}[*](*)',
				// allowedContent: 'script b i div p blockquote a img [class][*](*){*};',
				toolbar: [ 
					['Source','Maximize','Save'],
					['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ],
					[ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
					[ 'Image','Link','Unlink','Anchor','HorizontalRule' ],
					[ 'Format','Font','FontSize' ],
				],
				height: '450'
	    });
    </script>
    </form>

    </div>

    <div class="span6">
    <center><h1 id="previewtitle"><?php print "{$story['title']}\n"; ?></h1></center>
    <div id="previewbody"><?php print $story['body']; ?></div>
    </div>
    </div><!-- /row -->

    <?php

    bottom();
  }

  public static function save() {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $body = $_POST['body'];

    $publish = $_POST['publish'];
    $unpublish = $_POST['unpublish'];
    $delete = $_POST['delete'];

    if ($delete == '1') {
	    getDatabase()->execute(" 
	      update story set 
          published = 0,
          deleted = 1,
	        updated = CURRENT_TIMESTAMP 
	      where 
	        id = :id 
	        and personid = :personid
	      ",array('id'=>$id,'personid'=>getSession()->get('user_id')));
    }
    if ($unpublish == '1') {
	    getDatabase()->execute(" 
	      update story set 
          published = 0,
	        updated = CURRENT_TIMESTAMP 
	      where 
	        id = :id 
	        and personid = :personid
	      ",array('id'=>$id,'personid'=>getSession()->get('user_id')));
    }

    if ($publish == '1') {
	    getDatabase()->execute(" 
	      update story set 
          published = 1,
	        updated = CURRENT_TIMESTAMP 
	      where 
	        id = :id 
	        and personid = :personid
	      ",array('id'=>$id,'personid'=>getSession()->get('user_id')));
    }

    getDatabase()->execute(" 
      update story set 
        title = :title, 
        body = :body, 
        updated = CURRENT_TIMESTAMP 
      where 
        id = :id 
        and personid = :personid
      ",array('id'=>$id,'title'=>$title,'body'=>$body,'personid'=>getSession()->get('user_id')));

    header("Location: edit/$id");

  }

}

?>
