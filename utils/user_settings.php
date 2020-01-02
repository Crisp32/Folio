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
    $db = new SQLite3("../db/folio.db");

    // Send User Settings back to Client
    try {
        $profileImage = getUserData($db, "profileImagePath", "uid='$uid'");

        echo json_encode([
            "success" => true,
            "image" => $profileImage,
            "bio" => getUserData($db, "profileBio", "uid='$uid'"),
            "location" => getUserData($db, "accountLocation", "uid='$uid'"),
            "comments" => getUserData($db, "allowComments", "uid='$uid'")
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