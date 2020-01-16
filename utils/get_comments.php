<?php
/**
 * Folio Comment Grabber
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Fetch Comments
$type = escapeString($_REQUEST["type"]);
$profile = escapeString($_REQUEST["username"]);
$min = escapeString(intval($_REQUEST["min"]));
$max = escapeString(intval($_REQUEST["max"]));

// Check Comment Type
if ($type == $TYPE_PROFILE) {

    // Null Check User
    if (!empty($profile)) {
        $profileUID = getUserData("uid", "username='$profile'");

        // Check Permissions
        if (getUserData("allowComments", "uid='$profileUID'") == 1) {
            $queryComments = $db->query("SELECT * FROM comments WHERE uid='$profileUID' AND type='profile' ORDER BY cid DESC LIMIT $min, $max");
            $comments = [];
            
            while ($comment = $queryComments->fetch_array(MYSQLI_ASSOC)) {

                // Get Replies
                $repliesObject = json_decode($comment["usersReplied"]);
                $replies = [];

                foreach ($repliesObject as $reply) {
                    if (!empty($reply)) {
                        $replyData = [
                            "rid" => $reply[0],
                            "uid" => $reply[1],
                            "content" => $reply[2],
                            "date" => $reply[3]
                        ];

                        // Check if Active user can Delete the Comment
                        if (strval($_SESSION["user"]) == strval($replyData["uid"]) || strval($_SESSION["user"]) == strval($profileUID)) {
                            $delDisplay = "block";
                        }
                        else {
                            $delDisplay = "none";
                        }

                        $replyJSON = [
                            "user" => getUserData("username", "uid='".$replyData["uid"]."'"),
                            "content" => $replyData["content"],
                            "date" => $replyData["date"],
                            "rid" => $replyData["rid"],
                            "delDisplay" => $delDisplay
                        ];

                        // Finalize Reply
                        array_push($replies, $replyJSON);
                    }
                }

                // Finalize Comment JSON
                $delDisplayComment = "";

                // Check if Active user can Delete the Comment
                if (strval($_SESSION["user"]) == strval($comment["commenterId"]) || strval($_SESSION["user"]) == strval($profileUID)) {
                    $delDisplayComment = "block";
                }
                else {
                    $delDisplayComment = "none";
                }

                // Check if Liked by Active User
                $liked = false;
                $currentUser = intval($_SESSION["user"]);
                if (in_array($currentUser, json_decode($comment["usersLiked"]))) {
                    $liked = true;
                }

                // Push to Comments Array
                array_push($comments, [
                    "user" => getUserData("username", "uid='".$comment["commenterId"]."'"),
                    "content" => $comment["content"],
                    "date" => $comment["postDate"],
                    "likes" => $comment["likes"],
                    "liked" => $liked,
                    "replies" => $replies,
                    "cid" => $comment["cid"],
                    "delDisplay" => $delDisplayComment
                ]);
            }

            // Send Comments to Client
            echo json_encode([
                "success" => true,
                "comments" => $comments
            ]);
        }
        else {
            echo json_encode([
                "success" => false
            ]);
        }
    }
    else {
        echo json_encode([
            "success" => false
        ]);
    }
}
else if ($type == $TYPE_FORUMPOST) {
    // Nothing Yet
}
else {
    echo json_encode([
        "success" => false
    ]);
}