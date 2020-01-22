<?php
/**
 * Folio Comment Posting for Profiles and Blog Posts
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

$maxCommentLength = 120;

$maxComments = 70;
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
    $commentCount = 0;

    $postDate = date("j-n-Y");
    $rank = "member";

    if ($type == $TYPE_PROFILE) {
        
        // Get Profile Data
        $profile = new User();
        $profile->getUserDataByName($id);
        $profileId = $profile->user["uid"];

        if ($profile->user["allowComments"] == 1) {
            $error["success"] = true;

            if ($profileId == $activeUser) {
                $rank = "owner";
            }

            $commentCount = intval($profile->user["commentCount"]);
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

            if ($forum->ownerUID == $activeUser) {
                $rank = "owner";
            }
            else if ($forum->isModerator($activeUser)) {
                $rank = "mod";
            }

            $commentCount = intval($forumPost->post["commentCount"]);
            $insertStatement = "INSERT INTO comments (uid, commenterId, type, content, postDate, usersLiked, likes, usersReplied, repliesCount) VALUES ('$id', '$activeUser', '$type', '$commentContent', '$postDate', '[]', '0', '[]', '0');";
            $updateStatement = "UPDATE forumPosts SET commentCount=commentCount+1 WHERE pid=$id;";
        }
        else {
            $error["message"] = "You Must be a Member of this Forum to Comment";
        }
    }

    // Validate Comment
    if ($error["success"]) {
        if (!($commentCount >= $maxComments)) {
            if (!empty($commentContent)) {
                if (strlen($commentContent) > $maxCommentLength) {
                    echo json_encode([
                        "success" => false,
                        "message" => "Comment Must be Less than $maxCommentLength Characters"
                    ]);
                }
                else {
                    // Push Notification
                    $username = $user->user["username"];

                    if ($type == $TYPE_FORUMPOST) {
                        $postOwner = $forumPost->post["uid"];

                        if ($activeUser != $postOwner) {
                            $postName = $forumPost->post["title"];
                            Notification::push($postOwner, "@$username commented on your post, $postName", $commentContent);
                        }
                    }
                    else if ($type == $TYPE_PROFILE) {
                        if ($profileId != $activeUser) {
                            $profileName = $profile->user["username"];
                            Notification::push($profileId, "@$username commented on your profile", $commentContent);
                        }
                    }

                    // Update Database
                    $query = $db->multi_query($insertStatement . $updateStatement);

                    if ($query) {            
                        $cid = $db->insert_id;

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
                                    "rank" => $rank,
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
                "message" => "The Maximum Amount of Comments here has been Reached"
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