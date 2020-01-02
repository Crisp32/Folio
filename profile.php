<!DOCTYPE html>

<html lang="en" >
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" type="text/css" href="/client/css/main.css">
    <title>Folio - Profile</title>
  </head>
  <body>
    <!--Javascript Sources-->
    <script>
      let profile = "<?php echo $_GET["uquery"]; ?>";
    </script>
    <script src="/client/js/profile.js" ></script>
    <?php require("partials/_included-js.php"); ?>

    <!--Render Page-->
    <?php require("partials/_loading.php"); ?>
    <?php require("partials/_top-bar.php"); ?>
    
    <div id="content" >
      
        <!--Profile Page-->
        <div class="center-container" >
            <div id="profile-name-container" >
                <img id="profile-img" src="" />
                
                <div id="profile-media-container" >
                    <h2 id="profile-name" >404 Error</h2><br />
                    <div style="display: inline-block" ><img class="icon" id="location-icon" src="/images/other/locationIcon.svg" /><div id="profile-location" >Unknown</div></div>
                    <div id="join-date" >00-00-0000</div>
                </div>

                <div>
                  <div class="votes-container" >
                      <a class="upvote vote" href="javascript:upVoteClick(true)" title="Upvote" ></a>
                      <a class="downvote vote" href="javascript:downVoteClick(true)" title="Downvote" ></a>
                      <div class="votes" >0</div>
                  </div>
                  <textarea id="bio" readonly >
                    Nothing
                  </textarea>
                </div>
            </div>
          <div>
          <br />
          <div>
            <div class="profile-section" >
              <div class="profile-section-container" >
                <h2 class="section-title" >Blogs</h2>
              </div>
            </div>
            <br />
            <div class="profile-section" >
              <div class="profile-section-container" >
                <h2 class="section-title" >Comments <div class="section-disabled" id="comments-disabled-info" >DISABLED</div></h2>
                <div class="add-comment-div" ><input class="add-comment" placeholder="Comment" /><button class="add-comment-btn" onclick="addComment('profile')" >Post</button></div>
                <div id="comments-container" >
                </div>
              </div>
            </div>
          <div>
          
        <?php require("partials/_client-msg.php"); ?>
        <?php require("partials/_footer.php"); ?>
    </div>
  </body>
</html>