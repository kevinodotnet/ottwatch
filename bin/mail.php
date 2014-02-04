<?php

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/..");
require_once('include.php');
require_once('vendor/autoload.php');

$to = 'lkajsdlkjasdflkajsdflkajsdflkasjdf@gmail.com';
$subject = 'Bounce test';
$body = 'Will it send?';

sendEmail($to,$subject,$body);

?>
