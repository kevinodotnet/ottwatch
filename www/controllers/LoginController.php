<?php

class LoginController {
  static public function display() {
    top();
    ?>
    <div class="row-fluid">
    <span class="span6">
    <h3>Login</h3>
    <form class="form-horizontal" method="post">

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
    </span>

    <span class="span6">
    <h3>Privacy Policy</h3>
    </span>
    </div>
    <?php
    bottom();
  }

  static public function process() {
    top();
    print time();
    print "<pre>";
    print $_POST['email'];
    print print_r($_POST);
    print "</pre>";
    bottom();
  }

}

?>
