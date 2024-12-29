<?php

/**
 * Folio Account Settings Save Functionality
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

$maxChars = 150;
$maxBioChars = 300;

// Init DB
$db = db();

// Null Check User Session
if (isset($_SESSION["user"])) {
    $user = $_SESSION["user"];

    $image = escapeString($_REQUEST["image"]);
    $bio = escapeString($_REQUEST["bio"]);
    $loc = escapeString($_REQUEST["location"]);
    $comments = escapeString($_REQUEST["comments"]);

    // Allow Comments
    if ($comments == "0") {
        $comments = 0;
    } else {
        $comments = 1;
    }

    // Null Check Image URL
    $defImage = false;
    if (empty($image)) {
        $image = randomProfileImage();
        $defImage = true;
    }

    // Location
    if (empty($loc) || !validLocation($loc)) {
        $loc = "Unknown";
    }

    if (strlen($image) > $maxChars) {
        echo json_encode([
            "success" => false,
            "message" => "Image URL cannot exceed $maxChars Characters"
        ]);
    } else if (strlen($bio) > $maxBioChars) {
        echo json_encode([
            "success" => false,
            "message" => "Bio cannot exceed $maxBioChars Characters"
        ]);
    } else if (strlen($bio) == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Bio must be more than 0 Characters"
        ]);
    } else if (!validURL($image) && !$defImage) {
        echo json_encode([
            "success" => false,
            "message" => "The Specified Image does not Exist"
        ]);
    } else {
        // Success Outcome
        $bio = utf8_encode($bio);
        $query = "UPDATE users SET profileImagePath='$image', profileBio='$bio', accountLocation='$loc', allowComments='$comments' WHERE uid='$user'";

        // Run Through SQLite
        if ($db->query($query)) {
            echo json_encode([
                "success" => true,
                "imgURL" => $image
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "SQLite Error"
            ]);
        }
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Session"
    ]);
}
