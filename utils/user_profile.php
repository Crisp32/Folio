<?php
/**
 * Folio Profile Loader
 * Connell Reffo 2019
 */

include_once "app_main.php";

// Init DB
$db = new SQLite3("../db/folio.db");

// Retrieve User Info from SQLite
if (!empty($_REQUEST["query"]) && strpos($_REQUEST["query"], " ") == false) {

    $usearch = $_REQUEST["query"];
    $uid = getUserData($db, "uid", "username='$usearch'");

    $profileImage = getUserData($db, "profileImagePath", "uid='$uid'");
    $profileName = getUserData($db, "username", "uid='$uid'");
    $profileBio = getUserData($db, "profileBio", "uid='$uid'");
    $profileLocation = getUserData($db, "accountLocation", "uid='$uid'");

    // Null Check DB Response
    if (!empty($profileName) && !empty($uid)) {
        // Null Check Image
        if (empty($profileImage)) {
            $profileImages = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/json/profile-images.json"), true);
            $profileImage = $profileImages["default"];
        }

        // Send Response to Client
        echo json_encode(array(
            "success" => true,
            "username" => $profileName,
            "image" => $profileImage,
            "bio" => $profileBio,
            "location" => $profileLocation
        ));
    }
    else {
        // Return 404 Error
        echo json_encode(array(
            "success" => false,
            "message" => "404 Error: User not Found"
        ));
    }
}
else {
    // Return 404 Error
    echo json_encode(array(
        "success" => false,
        "message" => "404 Error: User not Found"
    ));
}


?>