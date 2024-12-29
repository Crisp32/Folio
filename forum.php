<?php session_start(); ?>
<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <link rel="stylesheet" type="text/css" href="/client/css/main.css">
  <?php require "partials/_html-head.php"; ?>
  <title>Folio - Forum</title>
</head>

<body>
  <!--Javascript Sources-->
  <script>
    let forum = "<?php echo $_GET["fquery"]; ?>";
  </script>
  <script src="/client/js/forum.js"></script>
  <?php require "partials/_included-js.php"; ?>

  <!--View Members Modal-->
  <div id="members-modal" class="modal-bg">
    <div class="modal-content">
      <div id="members-load-screen" class="load-scrn">Loading Members...<br /><img src="../images/other/folioLogoWhite.svg"></div>
      <div id="view-members-content">
        <h2 class="modal-title">Forum Members</h2>
        <div id="members-container"></div><br />
        <button onclick="closeMembers()" class="close-members-modal">Close</button>
      </div>
    </div>
  </div>

  <!--Edit Forum Interface-->
  <div class="create-forum-modal-bg">
    <div class="create-forum-modal">

      <!--Forum Image Icon Preview-->
      <img class="forum-icon-sel" src="/images/avatars/01.png">

      <!--Create/Cancel Buttons-->
      <div class="forum-modal-btns">
        <button class="standard-button close-btn" onclick="closeForumSettings()">Close</button>
        <button class="standard-button save-btn" onclick="saveForum()">Save</button>
      </div>

      <!--Forum Settings-->
      <div class="p-header settings-option-header">Forum Icon
        <input type="text" class="forum-option input-field" id="forum-img-url" placeholder="Custom Forum Icon URL" />
      </div><br />

      <div class="p-header settings-option-header">Forum Description
        <textarea style="margin-bottom: 20px; width: calc(100% - 20px)" type="text" class="input-field" id="forum-desc-textarea">Sample Text</textarea>
      </div><br />

      <!--Manage Banned Members-->
      <div class="p-header settings-option-header">Banned Members
        <div class="banned-members-container">
          <div class="bans-empty res-empty" style="font-size: 25px">No Banned Members</div>
        </div>
      </div><br />
    </div>
  </div>

  <!--Confirm Leave Forum Modal-->
  <div id="leave-forum-modal" class="modal-bg">
    <div class="modal-content">
      <h2 class="modal-title">Leave Forum?</h2>
      <div class="leave-forum-msg">Are you sure you want to leave this Forum? If you are the only Member left, this Forum will Automatically be Deleted. If you are the Owner, a Random Moderator will be Selected as new Owner (or Member if no Mods)</div>
      <div class="leave-forum-btns">
        <button class="standard-button close-btn" onclick="closeLeaveForum()">Cancel</button>
        <button class="standard-button save-btn" onclick="joinForum()">Confirm</button>
      </div>
    </div>
  </div>

  <!--Confirm Member Action Modal-->
  <div id="confirm-member-action-modal" class="modal-bg small-modal">
    <div class="modal-content">
      <h2 class="modal-title">Perform this Action?</h2>
      <div class="leave-forum-msg">Sample Text</div>
      <div class="leave-forum-btns">
        <button class="standard-button close-btn" onclick="closeModal()">Cancel</button>
        <button id="confirm-member-action" class="standard-button save-btn">Confirm</button>
      </div>
    </div>
  </div>

  <!--Confirm Forum Post Action Modal-->
  <div id="confirm-post-delete-modal" class="modal-bg small-modal">
    <div class="modal-content">
      <h2 class="modal-title">Perform this Action?</h2>
      <div class="leave-forum-msg">Are you sure that you want to Delete this Post?</div>
      <div class="leave-forum-btns">
        <button class="standard-button close-btn" onclick="closeModal()">Cancel</button>
        <button id="confirm-post-delete" class="standard-button save-btn">Confirm</button>
      </div>
    </div>
  </div>

  <!--Render Page-->
  <?php require "partials/_loading.php"; ?>
  <?php require "partials/_top-bar.php"; ?>

  <div id="content">
    <div style="overflow: visible" class="center-container">

      <!--Forum Info Area-->
      <div id="profile-name-container" class="forum-name-container">
        <img class="forum-img" id="profile-img" src="" />
        <button class="edit-forum-btn" onclick="openForumSettings()">Edit</button>

        <div id="profile-media-container" class="forum-media">
          <h2 style="font-size: 30px" class="forum-name" id="profile-name">404 Error</h2><br />
          <div id="profile-items-container">
            <div class="profile-item">Members: <div id="forum-members">?</div>
            </div>
            <div class="profile-item" id="creation-date-container">Created: <div id="creation-date">00-00-0000</div>
            </div>
          </div>
        </div><br />
        <button class="join-forum-btn" onclick="joinForum()">Join Forum</button>
        <div>
          <pre id="bio">Nothing</pre>
        </div>
        <div>
        </div>
      </div><br />

      <button onclick="showMembers()" class="view-members">View All Members</button><br />

      <div id="post-forum-section" class="profile-section post-forum">
        <div class="profile-section-container">
          <h2 class="section-title">New Forum Post</h2>

          <input class="forum-post-title" placeholder="Title" />
          <textarea class="forum-post-textarea" placeholder="Body"></textarea>
          <button class="add-comment-btn post-forum-btn" onclick="addForumPost()">Post</button>
        </div>
      </div><br />

      <div id="post-forum-sorting" class="profile-section post-forum">
        <div class="profile-section-container">
          <div class="sorting-options-container">
            <h2 class="section-title">Sort Posts By</h2>

            <select type="text" class="input-field sort-options">
              <option default="" value="new">New</option>
              <option default="" value="old">Old</option>
              <option default="" value="popular">Popular</option>
            </select>
            <button class="standard-button save-btn" onclick="applySort()">Apply</button>
          </div>
        </div>
      </div><br />

      <div id="forum-posts-container">
        <div class="posts-empty res-empty">Loading Posts...</div>
      </div>

      <?php require "partials/_client-msg.php"; ?>
</body>

</html>