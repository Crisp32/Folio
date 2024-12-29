<?php

/**
 * Folio Forum Post Vote Handler
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $pid = escapeString($_REQUEST["pid"]);

    // Get Forum Data
    $forumId = $db->query("SELECT fid FROM forumPosts WHERE pid=$pid")->fetch_array(MYSQLI_ASSOC)["fid"];
    $forumInstance = getForumDataById($forumId);

    // Check if Current User has Joined 
    if ($forumInstance->hasMember($user)) {
        $upvote = $_REQUEST["upvote"];
        $downvote = $_REQUEST["downvote"];

        // Parse String Bool
        if ($upvote == "true") {
            $upvote = true;
        } else {
            $upvote = false;
        }

        if ($downvote == "true") {
            $downvote = true;
        } else {
            $downvote = false;
        }

        // Get Forum Post Instance
        $forumPost = new ForumPost();
        $forumPost->getDataById($pid);

        if ($upvote && !$downvote) { // Add/Remove Upvote
            $upvoteQry = $forumPost->upvote($user);

            if ($upvoteQry["success"]) {
                echo json_encode([
                    "success" => true
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Unable to Upvote Post"
                ]);
            }
        } else if ($downvote && !$upvote) { // Add/Remove Downvote
            $downvoteQry = $forumPost->downvote($user);

            if ($downvoteQry["success"]) {
                echo json_encode([
                    "success" => true
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Unable to Downvote Post"
                ]);
            }
        } else { // Remove All
            $removeVoteQry = $forumPost->removeVotes($user);

            if ($removeVoteQry["success"]) {
                echo json_encode([
                    "success" => true,
                    "votes" => $removeVoteQry["count"]
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => $db->error
                ]);
            }
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "You Are not a Member of this Forum"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Vote on Forums"
    ]);
}
