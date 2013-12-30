<?php

class OttWatchConfig {
  # const LOGGED_IN = 'logged_in';
  const DB_TYPE = "mysql";
  const DB_NAME = "ottwatch";
  const DB_HOST = "localhost";
  const DB_USER = "ottwatch";
  const DB_PASS = "CHANGEME";
  const FILE_DIR = "/Users/kevino/Sites/var_ottwatch";
  const WWW = "http://localhost/ottwatch";
  const GOOGLE_API_KEY = "GET_FROM https://code.google.com/apis/console";
  const GOOGLE_ANALYTICS = "UA-CHANGE-ME";
	# used for read-only "sign-in-with-twitter"
  const TWITTER_CONSUMER_KEY = 'change_me';
  const TWITTER_CONSUMER_SECRET = 'change_me';
  const TWITTER_ACCESS_TOKEN = 'change_me';
  const TWITTER_TOKEN_SECRET = 'change_me';
	# used for read-write, just by @OttWatch to push tweets
  const TWITTER_POST_CONSUMER_KEY = 'change_me';
  const TWITTER_POST_CONSUMER_SECRET = 'change_me';
  const TWITTER_POST_ACCESS_TOKEN = 'change_me';
  const TWITTER_POST_TOKEN_SECRET = 'change_me';
  const YOUTUBE_USER = 'CHANGEME';
  const YOUTUBE_PASS = 'CHANGEME';
  const FACEBOOK_APP_ID = 'CHANGEME';
  const FACEBOOK_APP_SECRET = 'CHANGEME';
  const FACEBOOK_PAGE_ID = 'CHANGEME';
  const SMTP_HOST = 'CHANGEME';
  const SMTP_FROM_EMAIL = 'CHANGEME';
  const SMTP_FROM_NAME = 'CHANGEME';
	const TMP = '/mnt/tmp/ottwatch_tmp';

  const DISQUS_KEY = 'CHANGEME';
  const DISQUS_SECRET = 'CHANGEME';
  const DISQUS_TOKEN = 'CHANGEME';

}

?>
