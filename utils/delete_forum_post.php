<?php
/**
 * Folio Forum Post Deleter
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init Database
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $postId = escapeString($_REQUEST["pid"]);

    // Get Forum Post Data
    $forumPost = new ForumPost();
    $forumPost->getDataById($postId);

    // Get Forum Data
    $forum = getForumDataById($forumPost->post["fid"]);

    // Check Permissions
    $isMod = $forum->isModerator($user);

    if ($forumPost->post["uid"] == $user || $isMod) {
        $deleteQuery = $db->query("DELETE FROM forumPosts WHERE pid=$postId");

        // Handle SQL Errors
        if ($deleteQuery) {
            echo json_encode([
                "success" => true
            ]);
        }
        else {
            json_encode([
                "success" => false,
                "message" => $db->error
            ]);
        }
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => "You don't have Permission to Delete this"
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Perform this Action"
    ]);
}