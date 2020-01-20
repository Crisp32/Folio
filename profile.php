<!DOCTYPE html>

<html lang="en" >
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" type="text/css" href="/client/css/main.css">
    <?php require "partials/_html-head.php"; ?>
    <title>Folio - Profile</title>
  </head>
  <body>
    <!--Javascript Sources-->
    <script>
      let profile = "<?php echo $_GET["uquery"]; ?>";
    </script>
    <script src="/client/js/profile.js" ></script>
    <?php require("partials/_included-js.php"); ?>

    <!--Create Forum Modal Interface-->
    <div class="create-forum-modal-bg" >
        <div class="create-forum-modal" >

        <!--Forum Image Icon Preview-->
        <img class="forum-editor-folio" src="/images/other/folioLogoSmall.svg" >

        <!--Create/Cancel Buttons-->
        <div class="forum-modal-btns" >
          <button class="standard-button close-btn" onclick="closeForumMenu()" >Close</button>
          <button class="standard-button save-btn" onclick="createForum()" >Create</button>
        </div>

        <!--Forum Settings-->
        <div class="p-header settings-option-header" >Forum Name
          <input type="text" class="forum-option input-field" id="forum-name" placeholder="Name for Your Forum" />
        </div><br />

        <div class="p-header settings-option-header" >Forum Icon
          <input type="text" class="forum-option input-field" id="forum-img-url" placeholder="Custom Forum Icon URL" />
        </div><br />

        <div class="p-header settings-option-header" >Forum Description
          <textarea style="margin-bottom: 20px" type="text" class="input-field" id="forum-desc-textarea" >Sample Text</textarea>
        </div><br />
      </div>
    </div>

    <!--Render Page-->
    <?php require("partials/_loading.php"); ?>
    <?php require("partials/_top-bar.php"); ?>
    
    <div id="content" >
      
        <!--Profile Page-->
        <div class="center-container" >
            <div id="profile-name-container" >
                <img id="profile-img" src="" />
                <button class="edit-forum-btn" onclick="openSettings()" style="display: block;" >Edit</button>
                
                <div id="profile-media-container" >
                    <h2 id="profile-name" >404 Error</h2><br />
                    <div id="profile-items-container" >
                      <div class="profile-item" >Location: <div id="profile-location" >Unknown</div></div>
                      <div class="profile-item" id="join-date-container" >Joined: <div id="join-date" >00-00-0000</div></div>
                    </div>
                </div>

                <div>
                  <div class="votes-container" >
                      <button class="upvote vote" onclick="upVoteClick(true)" ><img src="/images/other/voteIcon.svg" ></button>
                      <button class="downvote vote" onclick="downVoteClick(true)" ><img src="/images/other/voteIcon.svg" ></button>
                      <div class="votes" >0</div>
                  </div>
                  <pre id="bio" >Nothing</pre>
                </div>
            </div>
          <div>
          <br />
          <div>
            <div class="profile-section" >
              <div class="prof-forum-section profile-section-container" >
                <h2 class="section-title" >Forums<button class="new-forum new-btn" onclick="openForumMenu()" >+ Create New Forum</button></h2>
                <div style="font-size: 25px; margin-top: -25px" class="forums-empty res-empty">No Active Forums to Display</div>
                <div id="joined-forums-container" ></div>
              </div>
            </div>
            <br />
            <div id="comment-section" class="profile-section" >
              <div class="profile-section-container" >
                <h2 class="section-title" >Comments <div class="section-disabled" id="comments-disabled-info" >DISABLED</div></h2>
                <div class="add-comment-div" ><input class="add-comment" placeholder="Comment" /><button class="add-comment-btn" onclick="addComment('profile')" >Post</button></div>
                <div id="comments-container" >
                </div>
              </div>
            </div>
          <div>
        <?php require("partials/_client-msg.php"); ?>
    </div>
  </body>
</html>