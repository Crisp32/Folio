<?php
/**
 * Folio Account Settings Save Functionality
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

$maxChars = 150;
$maxBioChars = 300;

// Init DB
$db = new SQLite3("../db/folio.db");

// Null Check User Session
if (isset($_SESSION["user"])) {
    $user = $_SESSION["user"];

    $image = strip_tags(escapeString($_REQUEST["image"]));
    $bio = strip_tags(escapeString($_REQUEST["bio"]));
    $loc = escapeString($_REQUEST["location"]);
    $comments = escapeString($_REQUEST["comments"]);

    // Allow Comments
    if ($comments == "0") {
        $comments = 0;
    }
    else {
        $comments = 1;
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
    }
    else if (strlen($bio) > $maxBioChars) {
        echo json_encode([
            "success" => false,
            "message" => "Bio cannot exceed $maxBioChars Characters"
        ]);
    }
    else if (strlen($bio) == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Bio must be more than 0 Characters"
        ]);
    }
    else if (!validURL($image)) {
        echo json_encode([
            "success" => false,
            "message" => "The Specified Image does not Exist"
        ]);
    }
    else {
        // Success Outcome
        $query = "UPDATE users SET profileImagePath='$image', profileBio='$bio', accountLocation='$loc', allowComments='$comments' WHERE uid='$user'";
        
        // Run Through SQLite
        if ($db->query($query)) {
            echo json_encode([
                "success" => true
            ]);
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => "SQLite Error"
            ]);
        }
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Session"
    ]);
}

?>