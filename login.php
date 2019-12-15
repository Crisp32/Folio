<?php
  include "utils/app_main.php";
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
    <?php require("partials/_included-js.php"); ?>

    <!--Render Page-->
    <?php require("partials/_loading.php"); ?>
    
    <div id="content" >

      <?php require("partials/_top-bar.php"); ?>

      <div class="reg-page" >
        <div class="login-form-container" >

          <div class="form-header" ><h2 class="header-text" >Login</h2></div>
          <div id="reg-form" >
            <br />
            <div class="p-header" >Account Credentials
              <input type="text" class="input-field" id="username" placeholder="Username or Email" />
            </div>
            <br />
            <div class="p-header" >Password
              <input type="password" class="input-field" id="pass" placeholder="Password" />
            </div>

            <button class="standard-button inl reg-button" onclick="login()" >Login</button>
            <br /><br />
            <a class="skip-reg" href="/register.php" >Don't Have an Account? Register Now <div class="bullet-point" >-&gt;</div></a>
        </div>
      </div>

      <?php require("partials/_client-msg.php"); ?>

    </div>
  </body>
</html>