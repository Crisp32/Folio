<?php
/**
 * Folio Comment Grabber
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

// Fetch Comments
$type = escapeString($_REQUEST["type"]);
$profile = escapeString($_REQUEST["username"]);
$min = escapeString(intval($_REQUEST["min"]));
$max = escapeString(intval($_REQUEST["max"]));

// Check Comment Type
if ($type == $TYPE_PROFILE) {

    // Null Check User
    if (!empty($profile)) {
        $profileUID = getUserData($db, "uid", "username='$profile'");

        // Check Permissions
        if (getUserData($db, "allowComments", "uid='$profileUID'") == 1) {
            $queryComments = $db->query("SELECT * FROM comments WHERE uid='$profileUID' AND type='profile' ORDER BY cid DESC LIMIT $min, $max");
            $comments = [];
            
            while ($comment = $queryComments->fetchArray(SQLITE3_ASSOC)) {

                // Get Replies
                $unformattedReplies = $comment["usersReplied"];
                $repliesArray = explode("<|n|>", $unformattedReplies);
                $replies = [];

                foreach ($repliesArray as $reply) {
                    if (!empty($reply)) {
                        $replyPieces = explode("<|s|>", $reply);
                        $delDisplay = "";

                        // Check if Active user can Delete the Comment
                        if (strval($_SESSION["user"]) == strval($replyPieces[0]) || strval($_SESSION["user"]) == strval($profileUID)) {
                            $delDisplay = "block";
                        }
                        else {
                            $delDisplay = "none";
                        }

                        $replyJSON = [
                            "user" => getUserData($db, "username", "uid='".$replyPieces[0]."'"),
                            "content" => $replyPieces[1],
                            "date" => $replyPieces[2],
                            "rid" => $replyPieces[3],
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
                $currentUser = $_SESSION["user"];
                if (strpos($comment["usersLiked"], ":$currentUser") !== false && !empty($currentUser)) {
                    $liked = true;
                }

                // Push to Comments Array
                array_push($comments, [
                    "user" => getUserData($db, "username", "uid='".$comment["commenterId"]."'"),
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




?>