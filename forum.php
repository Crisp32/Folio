<!DOCTYPE html>

<html lang="en" >
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" type="text/css" href="/client/css/main.css">
    <title>Folio - Forum</title>
  </head>
  <body>
    <!--Javascript Sources-->
    <script>
      let forum = "<?php echo $_GET["fquery"]; ?>";
    </script>
    <script src="/client/js/forum.js" ></script>
    <?php require "partials/_included-js.php"; ?>

    <!--View Members Modal-->
    <div id="members-modal" class="modal-bg" >
      <div class="modal-content" >
        <h2 class="modal-title" >Forum Members</h2>
        <div id="members-container" ></div>
        <button onclick="closeMembers()" class="close-members-modal" >Close</button>
      </div>
    </div>

    <!--Render Page-->
    <?php require "partials/_loading.php"; ?>
    <?php require "partials/_top-bar.php"; ?>
    
    <div id="content" >
        <div style="overflow: visible" class="center-container" >

            <!--Forum Info Area-->
            <div id="profile-name-container" >
                <img class="forum-img" id="profile-img" src="" />

                <div id="profile-media-container" class="forum-media" >
                    <h2 style="font-size: 30px" class="forum-name" id="profile-name" >404 Error</h2><br />
                    <div id="profile-items-container" >
                        <div class="profile-item" >Owner: <div id="forum-owner" >Unknown</div></div>
                        <div class="profile-item" id="creation-date-container" >Created: <div id="creation-date" >00-00-0000</div></div>
                    </div>
                </div>
                <div>
                    <textarea id="bio" readonly >Nothing</textarea>
                </div>
            <div>
        </div>
    </div><br />
    <button onclick="showMembers()" class="view-members" >View All Members</button>
    <?php require "partials/_client-msg.php"; ?>
  </body>
</html>