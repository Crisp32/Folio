<?php
/**
 * Folio Comment Reply Functionality
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

$maxReplyLength = 120;

$profileType = "profile";
$blogpostType = "blogpost";

// Null Check Session
if (!empty($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $commentCID = escapeString($_REQUEST["cid"]);
    $replyContent = escapeString($_REQUEST["content"]);
    $type = escapeString($_REQUEST["type"]);

    // Check if POST Request was Tampered with
    if ($type == $profileType || $type == $blogpostType) {

        // Validate Permissions
        $commentOwner = getCommentData($db, "uid", $type, "cid='$commentCID'");
        if (getUserData($db, "allowComments", "uid='$commentOwner'") == 1) {
            
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
                if ($type == $profileType) {

                    // Generate a Reply ID
                    $usersReplied = getCommentData($db, "usersReplied", "profile", "cid='$commentCID'");
                    $RID = count(explode("<|n|>", $usersReplied));
                    $date = date("j-n-Y");

                    // Insert Into DB
                    $formattedReply = formatReply($db, $replyContent, $date, $RID);
                    $finalString = $formattedReply . getCommentData($db, "usersReplied", "profile", "cid='$commentCID'");
                    
                    if ($db->query("UPDATE comments SET usersReplied='$finalString' WHERE cid='$commentCID' AND type='profile'")) {
                        
                        echo json_encode([
                            "success" => true,
                            "message" => "Posted Reply!",
                            "reply" => [
                                "0" => [
                                    "user" => getUserData($db, "username", "uid='$user'"),
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
                            "message" => "SQLite Error"
                        ]);
                    }
                }
                else {
                    // Post Comment For a Blogpost
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

function formatReply($db, $replyContent, $date, $RID) { 
    $commenter = $_SESSION["user"];
    
    // Filter Reply Content
    $replyContent = str_replace("<|n|>", "", $replyContent);
    $replyContent = str_replace("<|s|>", "", $replyContent);

    // Generate Final String
    $final = "<|n|>$commenter<|s|>$replyContent<|s|>$date<|s|>$RID";
    return $final;
}

?>