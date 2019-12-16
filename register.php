<?php
include "utils/app_main.php";

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
    <title>Folio - Register</title>
  </head>
  <body>
    <!--Javascript Sources-->
    <?php require("partials/_included-js.php"); ?>

    <!--Render Page-->
    <?php require("partials/_loading.php"); ?>
    
    <div id="content" >

      <?php require("partials/_top-bar.php"); ?>

      <div class="reg-page" >
        <div class="side-info" >
          <ul>
            <li><div class="bullet-point" >-&gt;</div> Explore others' Portfolio</li>
            <li><div class="bullet-point" >-&gt;</div> Share your Experiences</li>
            <li><div class="bullet-point" >-&gt;</div> Join Numerous Communities</li>
          </ul>
        </div> 

        <div class="form-container" >
          <div class="form-header" ><h2 class="header-text" >Register</h2></div>
          <div id="reg-form" >
            <br />
            <div class="p-header" >Personal Data
              <input type="text" class="input-field" id="email" placeholder="Email Address" />
              <select type="text" class="input-field dropdown second-input" id="location" >
                <option default value="" >Account Location</option>
                <?php echo fetchLocationsHtml(); ?>
              </select>
            </div>
            <br />
            <div class="p-header" >Username
              <input type="text" class="input-field" id="username" placeholder="Username" />
            </div>
            <br />
            <div class="p-header" >Password
              <input type="password" class="input-field" id="pass" placeholder="Password" />
              <input type="password" class="input-field second-input" id="conf-pass" placeholder="Confirm Password" />
            </div>

            <button class="standard-button inl reg-button" onclick="register()" >Send Verification Code</button>
            <br /><br />
            <a class="skip-reg" href="javascript:verifyPage()" >I Already have a Code <div class="bullet-point" >-&gt;</div></a>
          </div>
        </div>
      </div>

      <?php require("partials/_client-msg.php"); ?>

    </div>
  </body>
</html>