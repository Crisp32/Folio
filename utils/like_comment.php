<?php
/**
 * Folio Comment Liking
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

// Check Session
if (isset($_SESSION["user"])) {
    $user = $_SESSION["user"];

    $CID = escapeString($_REQUEST["cid"]);
    $commentQuery = $db->query("SELECT * FROM comments WHERE cid='$CID'");

    // Fetch Comment Data
    if ($commentQuery) {
        $commentData = $commentQuery->fetchArray();

        // Validate Permissions
        $profileId = $commentData["uid"];
        if (getUserData($db, "allowComments", "uid='$profileId'") == 1) {

            // Check if User has Already Liked Comment
            if (strpos($commentData["usersLiked"], ":$user") !== false) {

                // Has Liked
                $usersLiked = str_replace(":$user", "", $commentData["usersLiked"]);
                $likes = $commentData["likes"] - 1; // Remove Like
                $liked = false;
            }
            else {

                // Has Not Liked
                $usersLiked = ":$user" . $commentData["usersLiked"];
                $likes = $commentData["likes"] + 1; // Add Like
                $liked = true;
            }

            // Update DB
            $updateQuery = $db->query("UPDATE comments SET usersLiked='$usersLiked', likes='$likes' WHERE cid='$CID'");
            if ($updateQuery) {

                // Send Successful Response to Client
                echo json_encode([
                    "success" => true,
                    "likes" => $likes,
                    "liked" => $liked
                ]);
            }
            else {
                echo json_encode([
                    "success" => false,
                    "message" => "SQLite Error"
                ]);
            }
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => "You do not have Permission to Perform this Action"
            ]);
        }
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => "SQLite Error"
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Like Comments"
    ]);
}

?>