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
    $profileImages = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/json/profile-images.json"), true);
    $profileImage = $profileImages["default"];
}

?>

<div class="profile-bar-container" >
    <img src="<?php echo $profileImage; ?>" class="profile-image-bar" />
    <button class="standard-button inl profile-username-btn" onclick="" ><div class="profile-username-bar" ><?php echo $username; ?></div></button>
</div>