<?php
/**
 * Folio User Vote Handler
 * Connell Reffo 2019
 */

include "app_main.php";
session_start();

// Init DB
$db = db();

// Check if User is Logged in
if (validateSession($_SESSION["user"])) {
    $activeUser = $_SESSION["user"];
    $target = escapeString($_REQUEST["target"]);

    // Get Target User Data
    $targetUser = new User();
    $targetUser->getUserDataByName($target);

    // Check Target User
    if (!empty($targetUser->user["uid"])) {
        $upvote = $_REQUEST["upvote"];
        $downvote = $_REQUEST["downvote"];

        // Parse String Bool
        if ($upvote == "true") {
            $upvote = true;
        }
        else {
            $upvote = false;
        }

        if ($downvote == "true") {
            $downvote = true;
        }
        else {
            $downvote = false;
        }

        // Vote User
        if ($upvote && !$downvote) { // Upvote Event
            $upvoteQry = $targetUser->upvote($activeUser);
            if (!$upvoteQry["success"]) {
                echo json_encode([
                    "success" => false,
                    "message" => "Unable to Upvote User"
                ]);
            }
            else {
                echo json_encode([
                    "success" => true,
                    "votes" => $upvoteQry["count"]
                ]);
            }
        }
        else if ($downvote && !$upvote) { // Downvote Event
            $downvoteQry = $targetUser->downvote($activeUser);
            if (!$downvoteQry["success"]) {
                echo json_encode([
                    "success" => false,
                    "message" => "Unable to Downvote User"
                ]);
            }
            else {
                echo json_encode([
                    "success" => true,
                    "votes" => $downvoteQry["count"]
                ]);
            }
        }
        else { // Neutral Event
            $removeVoteQry = $targetUser->removeVote($activeUser);
            if (!$removeVoteQry["success"]) {
                echo json_encode([
                    "success" => false,
                    "message" => "Unable to Change Vote"
                ]);
            }
            else {
                echo json_encode([
                    "success" => true,
                    "votes" => $removeVoteQry["count"]
                ]);
            }
            
        }
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid Target User"
        ]);
    }
}
else {
    // Return Error
    echo json_encode([
        "success" => false,
        "message" => "You must be Logged in to Upvote/Downvote"
    ]);
}

?>