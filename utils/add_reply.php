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
    $type = escapeString($_REQUEST["type"]);

    // Check if POST Request was Tampered with
    if ($type == $TYPE_PROFILE || $type == $TYPE_FORUMPOST) {

        // Validate Permissions
        $commentOwner = getCommentData("uid", $type, "cid='$commentCID'");
        if (getUserData("allowComments", "uid='$commentOwner'") == 1) {
            
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
                // Post Comment For a Profile
                if ($type == $TYPE_PROFILE) {

                    // Generate a Reply ID
                    $usersRepliedEncoded = getCommentData("usersReplied", "profile", "cid='$commentCID'");
                    $usersReplied = json_decode($usersRepliedEncoded);
                    $RID = count($usersReplied);
                    $date = date("j-n-Y");

                    // Insert Into Database
                    $addReplyQuery = "UPDATE comments SET repliesCount=repliesCount+1, usersReplied=JSON_ARRAY_INSERT('$usersRepliedEncoded', '$[0]', JSON_ARRAY($RID, $user, '$replyContent', '$date')) WHERE cid='$commentCID' AND type='profile'";
                    if ($db->query($addReplyQuery)) {
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
                else if ($type == $TYPE_FORUMPOST) {
                    // Post Comment For a Blogpost
                }
                else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Invalid Type"
                    ]);
                }
            }
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => "This User has Commenting Disabled"
            ]);
        }
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => "Request Error"
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be logged in to Reply to Comments"
    ]);
}

?>