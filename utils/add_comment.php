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

    $id = escapeString($_REQUEST["profile"]); // Can be Either Post ID or Username
    $error = [
        "success" => false,
        "message" => "Invalid Comment Type"
    ];

    // Decide on Type
    $type = escapeString($_REQUEST["type"]);
    $commentContent = escapeString($_REQUEST["content"]);

    $insertStatement;
    $updateStatement;

    $postDate = date("j-n-Y");

    if ($type == $TYPE_PROFILE) {
        
        // Get Profile Data
        $profile = new User();
        $profile->getUserDataByName($id);
        $profileId = $profile->user["uid"];

        if ($profile->user["allowComments"] == 1) {
            $error["success"] = true;

            $insertStatement = "INSERT INTO comments (uid, commenterId, type, content, postDate, usersLiked, likes, usersReplied, repliesCount) VALUES ('$profileId', '$activeUser', '$type', '$commentContent', '$postDate', '[]', '0', '[]', '0');";
            $updateStatement = "UPDATE users SET commentCount=commentCount+1 WHERE uid=$profileId;";
        }
        else {
            $error["message"] = "This User has Commenting Disabled";
        }
    }
    else if ($type == $TYPE_FORUMPOST) {

        // Get Forum Post Data
        $forumPost = new ForumPost();
        $forumPost->getDataById($id);

        // Get Forum Data
        $forum = getForumDataById($forumPost->post["fid"]);

        if ($forum->hasMember($activeUser)) {
            $error["success"] = true;

            $insertStatement = "INSERT INTO comments (uid, commenterId, type, content, postDate, usersLiked, likes, usersReplied, repliesCount) VALUES ('$id', '$activeUser', '$type', '$commentContent', '$postDate', '[]', '0', '[]', '0');";
            $updateStatement = "UPDATE forumPosts SET commentCount=commentCount+1 WHERE pid=$id;";
        }
        else {
            $error["message"] = "You Must be a Member of this Forum to Comment";
        }
    }

    // Validate Comment
    if ($error["success"]) {
        if (!empty($commentContent)) {
            if (strlen($commentContent) > $maxCommentLength) {
                echo json_encode([
                    "success" => false,
                    "message" => "Comment Must be Less than $maxCommentLength Characters"
                ]);
            }
            else {                
                $cidQuery = $db->query("SELECT cid FROM comments ORDER BY cid DESC LIMIT 1");
                if ($db->multi_query($insertStatement . $updateStatement)) {            
                    if ($cidQuery) {
                        $cid = $cidQuery->fetch_array(MYSQLI_ASSOC)["cid"] + 1;

                        // Send Successful Response
                        echo json_encode([
                            "success" => true,
                            "comment" => [
                                "0" => [
                                    "user" => $user->user["username"],
                                    "content" => $commentContent,
                                    "date" => $postDate,
                                    "replies" => null,
                                    "likes" => 0,
                                    "cid" => $cid,
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
        echo json_encode($error);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You must be logged in to Comment"
    ]);
}