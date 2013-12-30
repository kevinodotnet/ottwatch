<?php

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)."/../lib"));
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)."/../www"));
require_once('include.php');

$key = OttWatchConfig::DISQUS_KEY;
$secret = OttWatchConfig::DISQUS_SECRET;
$token = OttWatchConfig::DISQUS_TOKEN;

$url = "https://disqus.com/api/3.0/forums/listPosts.json?forum=ottwatch";
$url .= "&api_key=$key";

#$json = file_get_contents($url);
#file_put_contents("posts.json",$json);
$json = file_get_contents("posts.json");
$response = json_decode($json);

if ($response->code != 0) {
  print "DISQUS Api request failed\n";
  print "\n";
  print $json;
  return;
}

foreach ($response->response as $r) {
  $thread = $r->thread;
  $message = $r->message;
  $author = $r->author->name;
  $created = strtotime($r->createdAt);
  $created = substr($r->createdAt,0,10);
  print "$thread ($author) [$created] $message\n";

  $url = "https://disqus.com/api/3.0/threads/details.json";
  $url .= "?thread=$thread";
  $url .= "&api_key=$key";
  # $url .= "&api_key=$key";

  # $json = file_get_contents($url);
  # file_put_contents("thread.json",$json);
  $json = file_get_contents("thread.json");
  $thread = json_decode($json);

  if ($thread->code == 0) {
	  $link = $thread->response->link;
	  $title = $thread->response->title;
	  print "POST: $title ($link)\n";
  }

  #pr($thread);
  #pr($r);

  exit;
}

/*
<pre>stdClass Object
(
    [parent] => 
    [isFlagged] => 
    [likes] => 0
    [forum] => ottwatch
    [thread] => 2081866137
    [author] => stdClass Object
        (
            [username] => rjeschmi
            [about] => 
            [name] => Rob Schmidt
            [url] => 
            [isAnonymous] => 
            [rep] => 1.236667
            [profileUrl] => http://disqus.com/rjeschmi/
            [reputation] => 1.236667
            [location] => 
            [isPrivate] => 
            [isPrimary] => 1
            [joinedAt] => 2013-05-24T01:15:01
            [id] => 51919849
            [avatar] => stdClass Object
                (
                    [small] => stdClass Object
                        (
                            [permalink] => https://disqus.com/api/users/avatars/rjeschmi.jpg
                            [cache] => //a.disquscdn.com/uploads/users/5191/9849/avatar32.jpg?1388377120
                        )

                    [isCustom] => 
                    [permalink] => https://disqus.com/api/users/avatars/rjeschmi.jpg
                    [cache] => //a.disquscdn.com/uploads/users/5191/9849/avatar92.jpg?1388377120
                    [large] => stdClass Object
                        (
                            [permalink] => https://disqus.com/api/users/avatars/rjeschmi.jpg
                            [cache] => //a.disquscdn.com/uploads/users/5191/9849/avatar92.jpg?1388377120
                        )

                )

        )

    [media] => Array
        (
        )

    [isApproved] => 1
    [dislikes] => 0
    [raw_message] => I find this hard to follow. Were they trying to amend the minutes to include something that didn't happen at the meeting (but should have)?
    [createdAt] => 2013-12-30T15:02:49
    [id] => 1181217198
    [numReports] => 0
    [isDeleted] => 
    [isEdited] => 
    [message] => <p>I find this hard to follow. Were they trying to amend the minutes to include something that didn't happen at the meeting (but should have)?</p>
    [isSpam] => 
    [isHighlighted] => 
    [points] => 0
)
1</pre>
*/
?>
