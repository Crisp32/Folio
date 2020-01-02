<?php
/**
 * Folio Comment Reply Deletion
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

// Null Check Session
if (isset($_SESSION["user"])) {
    $user = $_SESSION["user"];

    $RID = $_REQUEST["rid"];
    $CID = $_REQUEST["cid"];

    // Validate User Permissions
    $commentQuery = $db->query("SELECT * FROM comments WHERE cid='$CID'");

    if ($commentQuery) {

        $comment = $commentQuery->fetchArray();
        $replies = $comment["usersReplied"];
        $reply = parseReply($replies, $RID);
        
        if ($reply["commenterId"] == $user || strval($comment["uid"]) == $user) {
            $replyStr = findReplyStr($replies, $RID);
            $final = str_replace($replyStr, "", $replies);

            // Update DB
            $updateQuery = "UPDATE comments SET usersReplied='$final' WHERE cid='$CID'";
            if ($db->query($updateQuery)) {
                echo json_encode([
                    "success" => true,
                    "message" => "Deleted Reply!"
                ]);
            }
            else {
                echo json_encode([
                    "success" => false,
                    "message" => "Error with Updating the Database"
                ]);
            }
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => "You don't have Permission to Perform this Action"
            ]);
        }
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => "SQLite Error"
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Delete Replies"
    ]);
}

function findReplyStr($replies, $RID) {
    $repliesArr = explode("<|n|>", $replies);
    $returnStr = "";
    
    foreach ($repliesArr as $reply) {
        $replyPieces = explode("<|s|>", $reply);

        // Index of Reply ID
        if ($replyPieces[3] == $RID) {
            $returnStr = "<|n|>" . implode("<|s|>", $replyPieces);
            return $returnStr;
        }
    }
}

function parseReply($replies, $RID) {
    $repliesArr = explode("<|n|>", $replies);
    $returnJSON = [];
    
    foreach ($repliesArr as $reply) {
        $replyPieces = explode("<|s|>", $reply);

        // Index of Reply ID
        if ($replyPieces[3] == $RID) {
            $returnJSON = [
                "rid" => $RID,
                "content" => $replyPieces[1],
                "commenterId" => $replyPieces[0]
            ];
            return $returnJSON;
        }
    }
}

?>