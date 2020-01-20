<?php
/**
 * Folio Comment Deletion
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Null Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $cid = escapeString($_REQUEST["cid"]);

    $comment = $db->query("SELECT * FROM comments WHERE cid=$cid");

    if ($comment) {
        $commentData = $comment->fetch_array(MYSQLI_ASSOC);

        $commentOwner = $commentData["commenterId"];
        $commentProfile = $commentData["uid"]; // Post ID or User ID
        $commentType = $commentData["type"];

        // Validate Permissions based on Comment Type
        $canDelete = false;
        $updateQuery;

        if ($commentType == $TYPE_PROFILE) {
            $profile = escapeString(getUserData("uid", "username='".$_REQUEST["profile"]."'"));

            $canDelete = ($user == $profile && $commentProfile == $profile);
            $updateQuery = "UPDATE users SET commentCount=commentCount-1 WHERE uid=$profile;";
        }
        else if ($commentType == $TYPE_FORUMPOST) {

            // Get Forum Post Data
            $forumPost = new ForumPost();
            $forumPost->getDataById($commentProfile);
            $pid = $forumPost->post["pid"];

            // Get Forum Data
            $forum = getForumDataById($forumPost->post["fid"]);

            $canDelete = ($user == $forumPost->post["uid"] || $forum->isModerator($user));
            $updateQuery = "UPDATE forumPosts SET commentCount=commentCount-1 WHERE pid=$pid;";
        }
    
        if ($user == $commentOwner || $canDelete) {
            $delQuery = "DELETE FROM comments WHERE cid=$cid;";

            if ($db->multi_query($delQuery . $updateQuery)) {
                echo json_encode([
                    "success" => true,
                    "message" => "Deleted Comment!"
                ]);
            }
            else {
                echo json_encode([
                    "success" => false,
                    "message" => $db->error
                ]);
            }
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => "You don't have Permission to Perform this Action"
            ]);
        }
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => $db->error
        ]);
    }

}
else {
    echo json_encode([
        "success" => false,
        "message" => "You must be Logged in to Perform this Action"
    ]);
}

?>