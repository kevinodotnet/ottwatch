<?php

class CampaignController {

	public static function showSubmission($id) {
    $submission = getDatabase()->one(" select * from campaign_submission where id = :id ", array('id'=> $id));
    $values = getDatabase()->all(" select * from campaign_submission_value where submission_id = :id order by id ", array('id'=> $id));
    $campaign = getDatabase()->one(" select * from campaign where id = :id order by id ", array('id'=> $submission['campaign_id']));

    top3();
    ?>
    <h1><?php print $campaign['title']; ?></h1>
    <h5><?php print $campaign['sub_title']; ?></h5>
    <?php
    # pr($submission);
    # pr($values);
    # pr($campaign);
    bottom3();
  }

	public static function submit() {
    $mode = $_POST['mode'];
    $values = array();
    $values['campaign_id'] = $_POST['campaign_id'];
    $values['status'] = $_POST['mode'];
    $submission_id = db_insert('campaign_submission',$values);
    unset($_POST['mode']);
    unset($_POST['campaign_id']);
    foreach ($_POST as $k => $v) {
      $values = array();
      $values['submission_id'] = $submission_id;
      $values['name'] = $k;
      $values['value'] = $v;
      if (preg_match('/^question_(?<id>\d+)/', $k, $matches)) {
        $values['question_id'] = $matches['id'];
      }
      $id = db_insert('campaign_submission_value',$values);
    }

    if ($mode == 'preview') {
      header("Location: /campaign/submission/$submission_id");
    }
  }

	public static function index() {
    top3("Campaigns");
    $sql = " select * from campaign order by id desc limit 10 ";
		$rows = getDatabase()->all($sql);
		foreach ($rows as $r) {
      ?>
      <li><a href="/campaign/<?php print $r['id']; ?>"><?php print $r['title']; ?></a></li>
      <?php
    }
    bottom3();
		return;
	}

	public static function show($id) {
  # >all(" select * from md5hist where url = :href order by created desc limit 1 ",array('href'=>$url));
    $campaign = getDatabase()->one(" select * from campaign where id = :id ", array('id'=>$id));
    $recipients = getDatabase()->all(" select * from campaign_recipient where campaign_id = :id ", array('id'=>$id));
    $questions = getDatabase()->all(" select * from campaign_question where campaign_id = :id ", array('id'=>$id));
    top3($campaign['title'], false, true);
    ?>
    <h1><?php print $campaign['title']; ?></h1>
    <h5><?php print $campaign['sub_title']; ?></h5>
    <?php print $campaign['preamble']; ?>
    <hr/>
    <div class="container">
    <form method="post" action="submit">
      <input type="hidden" name="mode" value="preview"/>
      <input type="hidden" name="campaign_id" value="<?php print $id; ?>"/>
      <div class="row">
        <div class="col-sm-4">
          Your Information:
        </div>
        <div class="col-sm-8">
          <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First name (required)"><br/>
          <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last name (required)"><br/>
          <input type="text" class="form-control" id="email" name="email" placeholder="Email (required)"><br/>
          <input type="text" class="form-control" id="twitter" name="twitter" placeholder="@twitter (optional)"><br/>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-4">
          This petition will be sent to:
        </div>
        <div class="col-sm-8">
          <ul>
          <?php
          foreach ($recipients as $r) {
            print "<li>{$r['name']} &lt;{$r['email']}&gt;, {$r['role']}</li>";
          }
          ?>
          </ul>
        </div>
      </div>
      <?php
      foreach ($questions as $q) {
        ?>
        <div class="row" style="padding-top: 10px;">
          <div class="col-sm-4">
            <b><?php print $q['title']; ?></b>. <?php print $q['text']; ?>
          </div>
          <div class="col-sm-8">
            <textarea class="form-control" name="question_<?php print $q['id']; ?>" rows="3"></textarea>
          </div>
        </div>
        <?php
      }
      ?>
      <div class="row" style="padding-top: 10px;">
        <div class="col-sm-4">
          Press submit to preview what your submission will look like.
        </div>
        <div class="col-sm-8">
          <button type="submit" class="btn btn-primary mb-2">Submit</button>
        </div>
      </div>
    </form>
    </div>
    <?php
    bottom3();
		return;
	}
}
