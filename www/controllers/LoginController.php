<?php

use OAuth\OAuth2\Service\Facebook;
use OAuth\OAuth1\Service\Twitter;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

class LoginController {

  /**
    Only for kevin to use; adds the "manage pages" permission to OttWatch application
    so that later on OttWatch can post to pages offlinee.
    */
  static public function facebookManagePages() {
		$uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
		$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
		$currentUri->setQuery('');
    $storage = new Session();
    $credentials = new Credentials(
      OttWatchConfig::FACEBOOK_APP_ID,
      OttWatchConfig::FACEBOOK_APP_SECRET,
      $currentUri->getAbsoluteUri()
    );
    $serviceFactory = new \OAuth\ServiceFactory();
    $facebookService = $serviceFactory->createService('facebook', $credentials, $storage, array('manage_pages','publish_stream'));

    if (empty($_GET['code'])) {
      // send to facebook
      $url = $facebookService->getAuthorizationUri();
      header('Location: ' . $url);
      return;
    }

    // callback from facebook

    ?>
    <a href="http://dev.ottwatch.ca/ottwatch/user/login/facebook/managepages">AGAIN</a>
    <hr/>
    <?php

    $token = $facebookService->requestAccessToken($_GET['code']);
    $result = json_decode($facebookService->request(OttWatchConfig::FACEBOOK_PAGE_ID.'/?fields=access_token'), true);
    pr($result);

    getDatabase()->execute(" delete from variable where name = 'fb_page_access_token' ");
    getDatabase()->execute(" insert into variable (name,value) values ('fb_page_access_token',:token) ",array('token'=>$result['access_token']));
  }

  static public function facebook() {
		$uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
		$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
		$currentUri->setQuery('');
    $storage = new Session();
    $credentials = new Credentials(
      OttWatchConfig::FACEBOOK_APP_ID,
      OttWatchConfig::FACEBOOK_APP_SECRET,
      $currentUri->getAbsoluteUri()
    );
    $serviceFactory = new \OAuth\ServiceFactory();
    $facebookService = $serviceFactory->createService('facebook', $credentials, $storage, array());

    if (empty($_GET['code'])) {
      // send to facebook
      $url = $facebookService->getAuthorizationUri();
      header('Location: ' . $url);
      return;
    }

    // callback from facebook

    $token = $facebookService->requestAccessToken($_GET['code']);
    $result = json_decode($facebookService->request('/me'), true);

    # auto-create people if needed
    $row = getDatabase()->one(" select * from people where facebookid = :id ",array('id'=>$result['id']));
    if (!$row['id']) {
      # create them, then re-read the row
      $id = getDatabase()->execute(" insert into people (name,facebookid) values (:name,:id) ",array('name'=>$result['name'],'id'=>$result['id']));
      $row = getDatabase()->one(" select * from people where facebookid = :id ",array('id'=>$result['id']));
    }

    # they are now logged in.
    self::setLoggedIn($row['id']);
    $next = getSession()->get("login.next");
    if ($next != '') {
      header("Location: $next");
      return;
    }
    header("Location: " . OttWatchConfig::WWW . '/user/home');
  }

  static public function twitter() {

    # setup the 'return' URL to this url
		$uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
		$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
		$currentUri->setQuery('');
    $storage = new Session();
    $credentials = new Credentials(
      OttWatchConfig::TWITTER_CONSUMER_KEY,
      OttWatchConfig::TWITTER_CONSUMER_SECRET,
      $currentUri->getAbsoluteUri()
    );
    $serviceFactory = new \OAuth\ServiceFactory();
    $twitterService = $serviceFactory->createService('twitter', $credentials, $storage);

    if (!empty($_GET['denied'])) {
      # Odd. Why would you want to log in with twitter then stop? People.
      top();
      ?>
      You declined to log-in using Twitter. That's OK.
      <?php
      bottom();
      return;
    }

    if (empty($_GET['oauth_token'])) {
      # send to twitter to begin OAUTH
	    $token = $twitterService->requestRequestToken();
	    $url = $twitterService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
	    header('Location: ' . $url);
	    return;
    }

    # returning from Twitter. get details about the user
    $token = $storage->retrieveAccessToken('Twitter');
    $twitterService->requestAccessToken( $_GET['oauth_token'], $_GET['oauth_verifier'], $token->getRequestTokenSecret());
    $result = json_decode($twitterService->request('account/verify_credentials.json'));

    # auto-create people if needed
    $row = getDatabase()->one(" select * from people where lower(twitter) = lower(:screen_name) ",array('screen_name'=>$result->screen_name));
    if (!$row['id']) {
      # create them, then re-read the row
      $id = getDatabase()->execute(" insert into people (name,twitter) values (:name,:twitter) ",array('name'=>$result->name,'twitter'=>$result->screen_name));
      $row = getDatabase()->one(" select * from people where lower(twitter) = lower(:screen_name) ",array('screen_name'=>$result->screen_name));
    }

    # they are now logged in.
    self::setLoggedIn($row['id']);
    #header("Location: " . OttWatchConfig::WWW . '/user/home');

    $next = getSession()->get("login.next");
    if ($next != '') {
      header("Location: $next");
      return;
    }
    header("Location: " . OttWatchConfig::WWW . '/user/home');
    # echo 'result: <pre>' . print_r($result, true) . '</pre>';
  }

  static public function setLoggedIn($id) {
    getSession()->set("is_logged_in",$id);
    getDatabase()->execute(" update people set lastlogin = CURRENT_TIMESTAMP where id = lower(:id) ",array('id'=>$id));
    self::loadLoggedIn($id);
  }

  static public function loadLoggedIn($id) {
    $user = getDatabase()->one(" select * from people where id = lower(:id) ",array('id'=>$id));
    if (!$user['id']) {
      # should never happen
      getSession()->set("is_logged_in",'');
      return;
    }
    getSession()->set("user_id",$user['id']);
    getSession()->set("user_admin",$user['admin']);
    getSession()->set("user_name",$user['name']);
    getSession()->set("user_email",$user['email']);
    getSession()->set("user_twitter",$user['twitter']);
  }

  static public function isAdmin() {
    if (! self::isLoggedIn()) { return false; }
    return getSession()->get('user_admin') == 1;
  }

  static public function isLoggedIn() {
    $v = getSession()->get("is_logged_in");
    if ($v == '') {
      return false;
    }
    return true;
  }

  static public function displayRegister() {
    top();
    $err = $_GET['err'];
    ?>
    <div class="row-fluid">

    <div class="span6">
    <h3>Register</h3>
    <form class="form-horizontal" method="post">

    <?php if ($err != '') { ?>
    <div class="control-group">
    <div class="controls" style="color: red;">
    <?php print $err; ?>
    </div>
    </div>
    <?php } ?>

    <div class="control-group">
    <label class="control-label" for="inputEmail">Email</label>
    <div class="controls">
    <input type="text" id="inputEmail" name="email" placeholder="Email">
    </div>
    </div>

    <div class="control-group">
    <label class="control-label" for="inputPassword">Password</label>
    <div class="controls">
    <input type="password" id="inputPassword" name="password" placeholder="Password">
    </div>
    </div>

    <div class="control-group">
    <div class="controls">
    <button type="submit" class="btn">Register</button>
    </div>
    </div>

    </form>
    </div>

    <div class="span6">
    <h3>Privacy Policy</h3>
    <p>Let's keep this really simple. My name is Kevin O'Donnell and these are my intentions:</p>
    <ul>
    <li>I will keep your information confidential.</li>
    <li>I won't share your information with anyone.</li>
    <li>I won't spam you.</li>
    </ul>
    <p>That should cover it, eh?</p>
    </div>

    </div>
    <?php
    bottom();
  }

  static public function doRegister() {

    $email = $_POST['email'];
    $password = $_POST['password'];
    $row = getDatabase()->one(" select * from people where email = lower(:email) ",array('email'=>$email));
    if ($row['id']) {
      header("Location: register?err=".urlencode("Email account already exists"));
      return;
    }

    # start a row
    $id = getDatabase()->execute(" insert into people (email) values (lower(:email)) ",array('email'=>$email));
    # salt password with the id
    $md5 = md5($id.":".$password);
    getDatabase()->execute(" update people set password = :password where id = :id ",array('id'=>$id,'password'=>$md5));

    self::setLoggedIn($id);
    header("Location: home");
    
  }

  static public function display() {
    top();
    $err = $_GET['err'];
    $next = $_GET['next'];
    getSession()->set("login.next",$_GET['next']);
    ?>
    <div class="row-fluid">

    <div class="span4 offset4" style="text-align: center;">
    <h3>Sign in with Social Media</h3>
    <a class="btn btn-primary" href="login/twitter">Sign in with Twitter</a><br/><br/>
    <a class="btn btn-primary" href="login/facebook">Sign in with Facebook</a>
    </div>

    </div>
    <div class="row-fluid">

    <div class="span4 offset4" style="margin-top: 50px;">
    <h3 style="text-align: center;">Admins Only</h3>
    <form class="form-horizontal" method="post">

    <?php if ($err != '') { ?>
    <div class="control-group">
    <div class="controls" style="color: red;">
    <?php print $err; ?>
    </div>
    </div>
    <?php } ?>

    <div class="control-group">
    <label class="control-label" for="inputEmail">Email</label>
    <div class="controls">
    <input type="text" id="inputEmail" name="email" placeholder="Email">
    </div>
    </div>

    <div class="control-group">
    <label class="control-label" for="inputPassword">Password</label>
    <div class="controls">
    <input type="password" id="inputPassword" name="password" placeholder="Password">
    </div>
    </div>

    <div class="control-group">
    <div class="controls">
    <button type="submit" class="btn">Sign in</button>
    </div>
    </div>

    </form>
    </div>

    </div>
    <?php
    bottom();
    return;
    ?>

    <div class="span4">
    <h3>Register</h3>
    <p>
    You can create an account the old-school way by providing a name and email address.
    But really, using a social media account is way easier.</p>
    <p>
    <a class="btn" href="register">Click here to register</a>.
    </p>
    </div>

    </div>
    <?php
    bottom();
  }

  static public function doLogin () {

    $email = $_POST['email'];
    $password = $_POST['password'];
    $row = getDatabase()->one(" select * from people where email = lower(:email) ",array('email'=>$email));
    if (!$row['id']) {
      header("Location: login?err=".urlencode("Account not found or wrong password"));
      return;
    }

    $mymd5 = md5($row['id'].":".$password);
    $dbmd5 = $row['password'];
    if ($mymd5 != $dbmd5) {
      header("Location: login?err=".urlencode("Account not found or wrong password"));
      return;
    }

    self::setLoggedIn($row['id']);

    $next = getSession()->get("login.next");
    if ($next != '') {
      header("Location: $next");
      return;
    }
    header("Location: home");

  }

  static public function logout() {
    $next = $_GET['next'];
    getSession()->end();
		if (!isset($next) || $next == '') {
    	$next = OttWatchConfig::WWW;
		}
    header("Location: $next");
  }

	static public function blockUnlessLoggedIn() {

		if (LoginController::isLoggedIn()) {
			// calling function shows intended content
			return true;
		}

		top();

		?>
		<center>
		<h1>You must be logged in to access this page.</h1>
		<p class="lead">
		<a href="<?php print self::getLoginUrl(); ?>">Click here to login. You will be returned to this page automatically.</a>
		</p>
		</center>
		<?php

		bottom();
		return;

		pr($_SERVER);
		pr($_POST);
		pr($_GET);

	}

	static public function getLoginUrl($url) {
		if (!isset($url) || $url == '') {
			$url = $_SERVER['REQUEST_URI'];
		}
		return $OTT_WWW . '/user/login?next='.urlencode($url);
	}

}

?>
