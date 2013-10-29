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

    Created: <?php print $story['created']; ?><br/>
    Updated: <?php print $story['updated']; ?><br/>
    <form class="form-horizontal" method="post" action="../save">
    <input type="hidden" name="id" value="<?php print $id; ?>"/>
    <button type="submit" name="save" class="btn">Save</button>
    <button type="submit" name="publish" value="1" class="btn">Publish</button>
    <button type="submit" name="unpublish" value="1" class="btn">Unpublish</button>
    <button type="submit" name="delete" value="1" class="btn">Delete</button><br/><br/>
    Title<br/>
    <input style="width: 60%;" type="text" name="title" value="<?php print $story['title']; ?>"/>
    <br/>
    <br/>
    Body<br/>
    <textarea name="body" style="width: 80%; height: 400px;"><?php print $story['body']; ?></textarea>
    </form>

    </div>

    <div class="span6">
    <?php
    print "<h1>{$story['title']}</h1>\n";
    print $story['body'];
    ?>
    </div>
    </div>

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
