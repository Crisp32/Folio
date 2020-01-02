<?php
/**
 * Folio Comment Posting for Profiles and Blog Posts
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

$profileURL = "/profile.php";
$blogpostURL = "/blogview.php";

$maxCommentLength = 120;

// Init DB
$db = new SQLite3("../db/folio.db");

// Null Check User Session
if (isset($_SESSION["user"])) {
    $activeUser = $_SESSION["user"];
    $commentType = "";

    // Check if posting Comment to Profile
    if ($_REQUEST["url"] == $profileURL) {

        $commentType = "profile";
        $targetProfile = escapeString($_REQUEST["profile"]);
        $commentContent = escapeString($_REQUEST["content"]);
        $targetProfileUID = getUserData($db, "uid", "username='$targetProfile'");
        $postDate = date("j-n-Y");

        // Validate Request
        $canComment = getUserData($db, "allowComments", "uid='$targetProfileUID'");
        if ($canComment == 1) {
            if (!empty($commentContent)) {
                if (strlen($commentContent) > $maxCommentLength) {
                    echo json_encode([
                        "success" => false,
                        "message" => "Comment Must be Less than $maxCommentLength Characters"
                    ]);
                }
                else {
                    $insertStatement = "INSERT INTO comments (uid, commenterId, type, content, postDate, usersLiked, likes, usersReplied, replies) VALUES ('$targetProfileUID', '$activeUser', '$commentType', '$commentContent', '$postDate', '', '0', '', '0')";
                    if ($db->query($insertStatement)) {
                        echo json_encode([
                            "success" => true,
                            "comment" => [
                                "0" => [
                                    "0" => [
                                        "user" => getUserData($db, "username", "uid='".$activeUser."'"),
                                        "content" => $commentContent,
                                        "date" => $postDate,
                                        "replies" => null,
                                        "likes" => 0,
                                        "cid" => getCID($db),
                                        "delDisplay" => "block"
                                    ]
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
                "message" => "This User has Commenting Disabled"
            ]);
        }
    }
    else if ($_REQUEST["url"] == $blogpostURL) {
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
    while ($cid = $query->fetchArray(SQLITE3_ASSOC)) {
        return intval($cid["cid"]);
    }
}

?>