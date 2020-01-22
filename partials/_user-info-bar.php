<?php

session_start();
include_once $_SERVER["DOCUMENT_ROOT"] . "/utils/app_main.php";

// Init DB
$db = db();
$sess = $_SESSION["user"];

// Get Active User Data
$user = new User();
$user->getUserDataByUID($sess);

$username = $user->user["username"];
$profileImage = $user->user["profileImagePath"];

?>

<div class="profile-bar-container" id="open-options" >
    <div style="float: left; margin-right: -15px" >
        <img src="<?php echo $profileImage; ?>" class="profile-image-bar" />
        <button title="<?php echo "Folio - $username"; ?>" class="standard-button inl profile-username-btn" onclick="toggleOptions()" ><div class="profile-username-bar" ><?php echo $username; ?></div></button>
    </div>

    <div class="account-options" id="acc-options" >
        <a class="skip-reg" href="/index.php" ><div class="bullet-point" >-&gt;</div><div class="account-option" > Home</div></a><br />
        <a class="skip-reg" href="/profile.php?uquery=<?php echo $username; ?>" ><div class="bullet-point" >-&gt;</div><div class="account-option" > Profile</div></a><br />
        <a class="skip-reg" href="javascript:openSettings()" ><div class="bullet-point" >-&gt;</div><div class="account-option" > Account</div></a><br />
        <a class="skip-reg" href="javascript: showNotifications()" ><div class="bullet-point" >-&gt;</div><div class="account-option" > Inbox (<div class="notif-count" ><?php echo strval(Notification::getCount($sess)); ?></div>)</div></a><br />
        <a class="skip-reg" href="javascript: logout()" ><div class="bullet-point" >-&gt;</div><div class="account-option" > Logout</div></a>

        <br/><button class="close-options standard-button" onclick="toggleOptions()" >Close</button>
    </div>
</div>