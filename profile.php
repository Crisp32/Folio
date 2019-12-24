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
    
    <div id="content" >
        <?php require("partials/_top-bar.php"); ?>

        <!--Profile Page-->
        <div class="center-container" >
          <div id="profile-name-container" >
              <img id="profile-img" src="" />
              
              <div id="profile-media-container" >
                  <h2 id="profile-name" >404 Error</h2><br />
                  <div style="display: inline-block" ><img class="icon" id="location-icon" src="https://i.pinimg.com/originals/f2/57/78/f25778f30e29a96c44c4f72ef645aa63.png" /><div id="profile-location" >Unknown</div></div>
                  <div style="margin-left: -10px; transform: translateY(5px)" ><a class="upvote vote" href="javascript:upVoteClick(true)" title="Upvote" ></a><a class="downvote vote" href="javascript:downVoteClick(true)" title="Downvote" ></a></div>
                  <div class="votes" >0</div>
              </div>

              <div id="bio" >Nothing</div>
          </div>
          <br />
          <div class="profile-section" >
            <div class="profile-section-container" >
              <h2 class="section-title" >My Posts</h2>
            </div>
          </div>
          <br />
          <div class="profile-section" >
            <div class="profile-section-container" >
              <h2 class="section-title" >Blogs I Contribute To</h2>
            </div>
          </div>
          
        <?php require("partials/_client-msg.php"); ?>
        <?php require("partials/_footer.php"); ?>
    </div>
  </body>
</html>