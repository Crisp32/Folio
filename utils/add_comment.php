<?php
/**
 * Folio Comment Posting for Profiles and Blog Posts
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

$maxCommentLength = 120;

$maxComments = 80;
$maxReplies = 50;

// Init DB
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {
    $activeUser = $_SESSION["user"];
    $commentType = "";

    // Get Active User Data
    $user = new User();
    $user->getUserDataByUID($activeUser);

    // Check if posting Comment to Profile
    if ($_REQUEST["type"] == $TYPE_PROFILE) {
        
        $targetProfile = escapeString($_REQUEST["profile"]);
        $commentContent = escapeString($_REQUEST["content"]);
        $commentType = "profile";

        // Get Target User Data
        $targetUser = new User();
        $targetUser->getUserDataByName($targetProfile);
        $targetUserId = $targetUser->user["uid"];
        $postDate = date("j-n-Y");

        // Validate Request
        $canComment = $targetUser->user["allowComments"];
        if ($canComment == 1) {

            // Check if Max Comment Limit is Reached
            if ($targetUser->user["commentCount"] !== $maxComments) {

                if (!empty($commentContent)) {
                    if (strlen($commentContent) > $maxCommentLength) {
                        echo json_encode([
                            "success" => false,
                            "message" => "Comment Must be Less than $maxCommentLength Characters"
                        ]);
                    }
                    else {
                        $insertStatement = "INSERT INTO comments (uid, commenterId, type, content, postDate, usersLiked, likes, usersReplied, repliesCount) VALUES ('$targetUserId', '$activeUser', '$commentType', '$commentContent', '$postDate', '[]', '0', '[]', '0')";
                        $updateStatement = "UPDATE users SET commentCount=commentCount+1 WHERE uid=$targetUserId";
                        if ($db->query($insertStatement) && $db->query($updateStatement)) {
                            echo json_encode([
                                "success" => true,
                                "comment" => [
                                    "0" => [
                                        "user" => $user->user["username"],
                                        "content" => $commentContent,
                                        "date" => $postDate,
                                        "replies" => null,
                                        "likes" => 0,
                                        "cid" => getCID($db),
                                        "delDisplay" => "block"
                                    ]
                                ]
                            ]);
                        }
                        else {
                            echo json_encode([
                                "success" => false,
                                "message" => $db->error
                            ]);
                        }
                    }
                }
                else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Comment Must be Greater than 0 Characters"
                    ]);
                }
            }
            else {
                echo json_encode([
                    "success" => false,
                    "message" => "The Maximum Comment Limit on this Profile has been Reached"
                ]);
            }
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => "This User has Commenting Disabled"
            ]);
        }
    }
    else if ($_REQUEST["type"] == $TYPE_FORUMPOST) {
        $commentType = "blogpost";
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid URL: " . $_REQUEST["URL"]
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You must be logged in to Comment"
    ]);
}

function getCID($db) {
    $query = $db->query("SELECT cid FROM comments ORDER BY cid DESC LIMIT 1");
    while ($cid = $query->fetch_array(MYSQLI_ASSOC)) {
        return intval($cid["cid"]);
    }
}

?>