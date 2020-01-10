<?php

session_start();
include_once $_SERVER["DOCUMENT_ROOT"] . "/utils/app_main.php";

// Init DB
$db = new SQLite3($_SERVER["DOCUMENT_ROOT"] . "/db/folio.db");
$sess = $_SESSION["user"];

$username = getUserData($db, "username", "uid='$sess'");
$profileImage = getUserData($db, "profileImagePath", "uid='$sess'");

// Null Check Image
if (empty($profileImage)) {
    $profileImage = "https://ui-avatars.com/api/?background=c9c9c9&color=131313&size=256&bold=true&font-size=0.35&length=3&name=" . $username;
}

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
        <a class="skip-reg" href="javascript: logout()" ><div class="bullet-point" >-&gt;</div><div class="account-option" > Logout</div></a>

        <br/><button class="close-options standard-button" onclick="toggleOptions()" >Close</button>
    </div>
</div>