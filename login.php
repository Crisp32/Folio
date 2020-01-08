<?php

// Prevent Already Logged in Users from Visiting
session_start();

if (isset($_SESSION["user"])) {
  header("Location: index.php");
}

?>

<!DOCTYPE html>

<html lang="en" >
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" type="text/css" href="/client/css/main.css">
    <title>Folio - Login</title>
  </head>
  <body>
    <!--Javascript Sources-->
    <?php require "partials/_included-js.php"; ?>

    <!--Render Page-->
    <?php require "partials/_loading.php"; ?>
    <?php require "partials/_top-bar.php"; ?>
    
    <div id="content" >
      <div style="text-align: center" >
        <div class="login-form-container" >

        <div class="form-header" ><h2 class="header-text" >Login</h2></div>
          <br />
          <div class="p-header" >Account Credentials
            <input type="text" class="input-field" id="username" placeholder="Username or Email" />
          </div>
          <br />
          <div class="p-header" >Password
            <input type="password" class="input-field" id="login-pass" placeholder="Password" />
            <button class="standard-button inl reg-button" onclick="login()" >Login</button>
          </div>
          
          <br /><br />
          <div class="register-prompt-container" >
            <a class="skip-reg" href="/register.php" >Don't Have an Account? Register Now <div class="bullet-point" >-&gt;</div></a>
          </div>
      </div>

      <?php require "partials/_client-msg.php"; ?>

    </div>

  </body>
</html>