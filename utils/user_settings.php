<?php
/**
 * Folio Account Settings Grabber
 * Connell Reffo
 */

include_once "app_main.php";
session_start();

// Null Check Sessions
if (isset($_SESSION["user"])) {
    $uid = $_SESSION["user"];

    // Init DB
    $db = db();

    // Send User Settings back to Client
    try {

        // Get User Data
        $userInstance = new User();
        $userInstance->getUserDataByUID($uid);

        echo json_encode([
            "success" => true,
            "image" => $userInstance->user["profileImagePath"],
            "bio" => htmlFormat(utf8_decode($userInstance->user["profileBio"])),
            "location" => $userInstance->user["accountLocation"],
            "comments" => $userInstance->user["allowComments"],
            "email" => $userInstance->user["email"]
        ]);
    }
    catch (Exception $err) {
        echo json_encode([
            "success" => false,
            "message" => substr($err, 0, 23)
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Session"
    ]);
}

?>