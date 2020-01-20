<?php
/**
 * Folio Comment Reply Functionality
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

$maxReplyLength = 120;

// Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $commentCID = escapeString($_REQUEST["cid"]);
    $replyContent = escapeString($_REQUEST["content"]);

    // Validate Permissions
    $type = $db->query("SELECT type FROM comments WHERE cid=$commentCID")->fetch_array(MYSQLI_ASSOC)["type"];
    $commentOwner = getCommentData("uid", $type, "cid='$commentCID'");

    // Check if Reply Content is Valid
    if (strlen($replyContent) > $maxReplyLength) {
        echo json_encode([
            "success" => false,
            "message" => "Reply must not Exceed $maxReplyLength Characters"
        ]);
    }
    else if (strlen($replyContent) == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Reply must be Greater than 0 Characters"
        ]);
    }
    else {
        $error = [
            "success" => false,
            "message" => ""
        ];

        // Generate a Reply ID
        $usersRepliedEncoded = getCommentData("usersReplied", $type, "cid='$commentCID'");
        $usersReplied = json_decode($usersRepliedEncoded);
        $RID = count($usersReplied);
        $date = date("j-n-Y");
        
        // Post Comment For a Profile
        if ($type == $TYPE_PROFILE) {
            $allowComments = (getUserData("allowComments", "uid='$commentOwner'") == 1);

            if ($allowComments) {
                $error["success"] = true;
            }
            else {
                $error["message"] = "This User has Commenting Disabled";
            }
        }
        else if ($type == $TYPE_FORUMPOST) {
            
            // Get Forum Post Data
            $postId = getCommentData("uid", $TYPE_FORUMPOST, "cid=$commentCID");

            $forumPost = new ForumPost();
            $forumPost->getDataById($postId);

            // Get Forum Data
            $forum = getForumDataById($forumPost->post["fid"]);

            if ($forum->hasMember($user)) {
                $error["success"] = true;
            }
            else {
                $error["message"] = "You Must be a Member of this Forum to Reply to Comments";
            }
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => "Invalid Type"
            ]);
        }

        if ($error["success"]) {

            // Insert Into Database
            $addReplyQuery = $db->query("UPDATE comments SET repliesCount=repliesCount+1, usersReplied=JSON_ARRAY_INSERT('$usersRepliedEncoded', '$[0]', JSON_ARRAY($RID, $user, '$replyContent', '$date')) WHERE cid='$commentCID' AND type='$type'");
                
            if ($addReplyQuery) {
                echo json_encode([
                    "success" => true,
                    "message" => "Posted Reply!",
                    "reply" => [
                        "0" => [
                            "user" => getUserData("username", "uid='$user'"),
                            "content" => $replyContent,
                            "date" => $date,
                            "rid" => $RID,
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
            echo json_encode($error);
        }
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be logged in to Reply to Comments"
    ]);
}

?>