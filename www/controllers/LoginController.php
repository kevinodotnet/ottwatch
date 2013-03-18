<?php

class LoginController {

  static public function setLoggedIn($id) {
    getSession()->set("is_logged_in",$id);
    $user = getDatabase()->one(" select * from people where id = lower(:id) ",array('id'=>$id));
    if (!$user['id']) {
      # should never happen
      getSession()->set("is_logged_in",'');
      return;
    }
    getSession()->set("user_id",$user['id']);
    getSession()->set("user_name",$user['name']);
    getSession()->set("user_email",$user['email']);
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
    ?>
    <div class="row-fluid">

    <div class="span6">
    <h3>Login</h3>
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

    <div class="span6">
    <h3>Register</h3>
    <p>Need to create an account?</p>
    <p>
    <a class="btn btn-primary" href="register">Click here to register</a>.
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
    header("Location: home");
  }

  static public function logout() {
    getSession()->set("is_logged_in",'');
    header("Location: " . OttWatchConfig::WWW);
  }

}

?>
