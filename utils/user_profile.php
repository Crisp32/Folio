<?php
/**
 * Folio Profile Loader
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

// Retrieve User Info from SQLite
if (!empty($_REQUEST["query"]) && strpos($_REQUEST["query"], " ") == false) {

    $usearch = $_REQUEST["query"];
    $uid = getUserData($db, "uid", "username='$usearch'");

    $profileImage = getUserData($db, "profileImagePath", "uid='$uid'");
    $profileName = getUserData($db, "username", "uid='$uid'");
    $profileBio = getUserData($db, "profileBio", "uid='$uid'");
    $profileLocation = getUserData($db, "accountLocation", "uid='$uid'");
    $allowComments = getUserData($db, "allowComments", "uid='$uid'");
    $date = getUserData($db, "date", "uid='$uid'");
    $votes = getUserData($db, "votes", "uid='$uid'");
    $voteCount = calcVotes($votes);

    // Null Check DB Response
    if (!empty($profileName) && !empty($uid)) {
        
        // Null Check Image
        if (empty($profileImage)) {
            $profileImage = "https://ui-avatars.com/api/?background=c9c9c9&color=131313&size=224&bold=true&font-size=0.35&length=3&name=$profileName";
        }

        if (empty($date)) {
            $date = "00-00-0000";
        }

        // Check Votes
        $upvoted = false;
        $downvoted = false;

        // Get Comments
        if (getUserData($db, "allowComments", "uid='$uid'") == 1) {
            $queryComments = $db->query("SELECT cid, uid, commenterId, content, postDate, usersLiked, likes, usersReplied, replies FROM comments WHERE uid='$uid' AND type='profile' ORDER BY cid ASC");
            $comments = [];
            
            $index = 0;
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
                        if (strval($_SESSION["user"]) == strval($replyPieces[0]) || strval($_SESSION["user"]) == strval($uid)) {
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
                if (strval($_SESSION["user"]) == strval($comment["commenterId"]) || strval($_SESSION["user"]) == strval($uid)) {
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

                array_push($comments, [
                    $index => [
                        "user" => getUserData($db, "username", "uid='".$comment["commenterId"]."'"),
                        "content" => $comment["content"],
                        "date" => $comment["postDate"],
                        "likes" => $comment["likes"],
                        "liked" => $liked,
                        "replies" => $replies,
                        "cid" => $comment["cid"],
                        "delDisplay" => $delDisplayComment
                    ]
                ]);

                $index++;
            }
        }

        if (isset($_SESSION["user"])) {
            $activeUser = $_SESSION["user"];

            if (strpos($votes, ":$activeUser+") !== false) {
                $upvoted = true;
            }
            else if (strpos($votes, ":$activeUser-") !== false) {
                $downvoted = true;
            }
        }

        // Send Response to Client
        echo json_encode(array(
            "success" => true,
            "username" => $profileName,
            "image" => $profileImage,
            "bio" => $profileBio,
            "location" => $profileLocation,
            "votes" => $voteCount,
            "upvoted" => $upvoted,
            "downvoted" => $downvoted,
            "date" => $date,
            "comments" => $allowComments,
            "accountComments" => $comments
        ));
    }
    else {
        // Return 404 Error
        echo json_encode(array(
            "success" => false,
            "message" => "404 Error: User not Found"
        ));
    }
}
else {
    // Return 404 Error
    echo json_encode(array(
        "success" => false,
        "message" => "404 Error: User not Found"
    ));
}


?>