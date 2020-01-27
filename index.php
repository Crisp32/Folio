<!DOCTYPE html>

<html lang="en" >
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" type="text/css" href="/client/css/main.css">
    <?php require "partials/_html-head.php"; ?>
    <title>Folio - Home</title>
  </head>
  <body>
    <!--Javascript Sources-->
    <script src="/client/js/home.js" ></script>
    <script src="/client/js/forum.js" ></script>
    <?php require("partials/_included-js.php"); ?>

    <!--Confirm Forum Post Action Modal-->
    <div id="confirm-post-delete-modal" class="modal-bg small-modal" >
      <div class="modal-content" >
        <h2 class="modal-title" >Perform this Action?</h2>
        <div class="leave-forum-msg" >Are you sure that you want to Delete this Post?</div>
        <div class="leave-forum-btns" >
          <button class="standard-button close-btn" onclick="closeModal()" >Cancel</button>
          <button id="confirm-post-delete" class="standard-button save-btn" >Confirm</button>
        </div>
      </div>
    </div>

    <!--Render Page-->
    <?php require("partials/_loading.php"); ?>
    <?php require("partials/_top-bar.php"); ?>
    
    <div style="text-align: center" >
      <div id="content" >
        <div class="credit-area" ></div>
        <div class="profile-section credit-area" >
          <div class="prof-forum-section profile-section-container" >
            <h1 class="title" >fol<div style="color: darkgrey" >.</div>io</h1>

            <div class="credit-name" >&copy; Connell Reffo<br />&#8226; Developed 2019-2020</div>

            <div class="link-title" >Report Issues to this Email Address:</div>
            <a class="h-link" href="mailto:foliowebapp@gmail.com" >foliowebapp@gmail.com</a>
            <div style="margin-bottom: 23px" ></div>

            <div class="link-title" >View the Developer on GitHub:</div>
            <a class="h-link" href="https://github.com/Crisp32" target="_blank" >https://github.com/Crisp32</a>
          </div>
        </div>
        <br />

        <div class="profile-section" >
          <div class="prof-forum-section profile-section-container" >
            <h2 class="section-title" >Top Users</h2>

            <div id="top-users-container" class="home-content-container" >
              <div class="res-empty section-empty" >Nothing to See Here</div>
            </div>
          </div>
        </div>
        <br />

        <div class="profile-section" >
          <div class="prof-forum-section profile-section-container" >
            <h2 class="section-title" >Popular Forums</h2>

            <div id="popular-forums-container" class="home-content-container" >
              <div class="res-empty section-empty" >Nothing to See Here</div>
            </div>
          </div>
        </div>
        <br />
        
        <div class="profile-section" style="margin-bottom: -15px; margin-top: 45px" >
          <div class="profile-section-container" >
            <h2 class="section-title" style="margin-bottom: 10px" >Trending Posts <img class="title-img" src="/images/other/dropdown-icon.svg" ></h2>
          </div>
        </div>
        <br />
        
        <div id="forum-posts-container" class="home-forum-posts" >
          <div class="res-empty posts-empty profile-section">Loading Posts...</div>
        </div>

        <?php require("partials/_client-msg.php"); ?>
      </div>
    </div>
  </body>
</html>