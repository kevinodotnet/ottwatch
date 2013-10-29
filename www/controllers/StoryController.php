<?php

class StoryController {

  public static function add() {

    $story = array();
    $story['personid'] = getSession()->get('user_id');
    $id = db_insert('story',$story);

    header("Location: edit/$id");
  }

  public static function edit($id) {
    top();
    $story = getDatabase()->one(" select * from story where id = :id ",array('id'=>$id));

    if ($story['deleted']) {
      print "<h1>DELETED</h1>\n";
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
    <button type="submit" name="publish" value="1" class="btn">Publish</button>
    <button type="submit" name="unpublish" value="1" class="btn">Unpublish</button>
    <button type="submit" name="delete" value="1" class="btn">Delete</button>
    <button type="submit" onclick="return preview()" name="delete" value="1" class="btn">Preview</button>
    </center>
    </p>
    <textarea id="storybody" name="body" rows="80" style=""><?php print $story['body']; ?></textarea>
    <script>
    $( '#storybody' ).ckeditor({
     toolbar: [ 
	      ['Source','Maximize'],
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
    <h1 id="previewtitle"><?php print "{$story['title']}\n"; ?></h1>
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
