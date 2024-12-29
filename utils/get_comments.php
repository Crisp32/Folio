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
$min = escapeString(intval($_REQUEST["min"]));
$max = escapeString(intval($_REQUEST["max"]));

// Check Comment Type
if ($type == $TYPE_PROFILE) {
    $profile = escapeString($_REQUEST["username"]);

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
                            "content" => utf8_decode($reply[2]),
                            "date" => $reply[3]
                        ];

                        // Check if Active user can Delete the Comment
                        if (strval($_SESSION["user"]) == strval($replyData["uid"]) || strval($_SESSION["user"]) == strval($profileUID)) {
                            $delDisplay = "block";
                        } else {
                            $delDisplay = "none";
                        }

                        // Colour Profile Owner Name
                        $rank = "member";

                        if ($replyData["uid"] == $profileUID) {
                            $rank = "owner";
                        }

                        $replyJSON = [
                            "user" => getUserData("username", "uid='" . $replyData["uid"] . "'"),
                            "content" => $replyData["content"],
                            "date" => $replyData["date"],
                            "rid" => $replyData["rid"],
                            "rank" => $rank,
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
                } else {
                    $delDisplayComment = "none";
                }

                // Check if Liked by Active User
                $liked = false;
                $currentUser = intval($_SESSION["user"]);
                if (in_array($currentUser, json_decode($comment["usersLiked"]))) {
                    $liked = true;
                }

                // Colour Profile Owner Name
                $rank = "member";

                if ($comment["commenterId"] == $profileUID) {
                    $rank = "owner";
                }

                // Push to Comments Array
                array_push($comments, [
                    "user" => getUserData("username", "uid='" . $comment["commenterId"] . "'"),
                    "content" => utf8_decode($comment["content"]),
                    "date" => $comment["postDate"],
                    "likes" => $comment["likes"],
                    "liked" => $liked,
                    "replies" => $replies,
                    "cid" => $comment["cid"],
                    "rank" => $rank,
                    "delDisplay" => $delDisplayComment
                ]);
            }

            // Send Comments to Client
            echo json_encode([
                "success" => true,
                "comments" => $comments
            ]);
        } else {
            echo json_encode([
                "success" => false
            ]);
        }
    } else {
        echo json_encode([
            "success" => false
        ]);
    }
} else if ($type == $TYPE_FORUMPOST) {
    $postId = escapeString($_REQUEST["pid"]);

    // Null Check PID
    if ($postId !== null && $postId !== "") {

        // Get Forum Post Data
        $forumPost = new ForumPost();
        $forumPost->getDataById($postId);

        // Get Forum Data
        $forumInstance = getForumDataById($forumPost->post["fid"]);

        // Get Comments
        $selectQuery = $db->query("SELECT * FROM comments WHERE uid=$postId AND type='forumpost' ORDER BY cid DESC LIMIT $min, $max");
        $comments = [];

        while ($comment = $selectQuery->fetch_array(MYSQLI_ASSOC)) {

            // Get Replies
            $repliesObject = json_decode($comment["usersReplied"]);
            $replies = [];

            foreach ($repliesObject as $reply) {
                if (!empty($reply)) {
                    $replyData = [
                        "rid" => $reply[0],
                        "uid" => $reply[1],
                        "content" => utf8_decode($reply[2]),
                        "date" => $reply[3]
                    ];

                    // Check if Active user can Delete the Comment
                    if ($forumPost->post["uid"] == $_SESSION["user"] || $forumInstance->isModerator($_SESSION["user"]) || $comment["commenterId"] == $_SESSION["user"]) {
                        $delDisplay = "block";
                    } else {
                        $delDisplay = "none";
                    }

                    // Get Member Rank
                    $rank = "member";

                    if ($forumInstance->ownerUID == $replyData["uid"]) {
                        $rank = "owner";
                    } else if ($forumInstance->isModerator($replyData["uid"])) {
                        $rank = "mod";
                    }

                    $replyJSON = [
                        "user" => getUserData("username", "uid='" . $replyData["uid"] . "'"),
                        "content" => $replyData["content"],
                        "date" => $replyData["date"],
                        "rid" => $replyData["rid"],
                        "rank" => $rank,
                        "delDisplay" => $delDisplay
                    ];

                    // Finalize Reply
                    array_push($replies, $replyJSON);
                }
            }

            // Finalize Comment JSON
            $delDisplayComment = "";

            // Check if Active user can Delete the Comment
            if ($forumPost->post["uid"] == $_SESSION["user"] || $comment["commenterId"] == $_SESSION["user"] || $forumInstance->isModerator($_SESSION["user"])) {
                $delDisplayComment = "block";
            } else {
                $delDisplayComment = "none";
            }

            // Check if Liked by Active User
            $liked = false;
            $currentUser = intval($_SESSION["user"]);
            if (in_array($currentUser, json_decode($comment["usersLiked"]))) {
                $liked = true;
            }

            // Get Member Rank
            $rank = "member";

            if ($forumInstance->ownerUID == $comment["commenterId"]) {
                $rank = "owner";
            } else if ($forumInstance->isModerator($comment["commenterId"])) {
                $rank = "mod";
            }

            // Push to Comments Array
            array_push($comments, [
                "user" => getUserData("username", "uid='" . $comment["commenterId"] . "'"),
                "content" => utf8_decode($comment["content"]),
                "date" => $comment["postDate"],
                "likes" => $comment["likes"],
                "liked" => $liked,
                "replies" => $replies,
                "cid" => $comment["cid"],
                "rank" => $rank,
                "delDisplay" => $delDisplayComment
            ]);
        }

        // Send Comments to Client
        echo json_encode([
            "success" => true,
            "comments" => $comments
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid Post ID"
        ]);
    }
} else {
    echo json_encode([
        "success" => false
    ]);
}
