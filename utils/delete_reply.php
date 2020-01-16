<?php
/**
 * Folio Comment Reply Deletion
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];

    $RID = escapeString($_REQUEST["rid"]);
    $CID = escapeString($_REQUEST["cid"]);

    // Validate User Permissions
    $commentQuery = $db->query("SELECT uid, JSON_EXTRACT(usersReplied, '$[*]') AS replies FROM comments WHERE cid='$CID'");

    if ($commentQuery) {

        $comment = $commentQuery->fetch_array(MYSQLI_ASSOC);

        $repliesEncoded = $comment["replies"];
        $replyData = findReply($RID, json_decode($repliesEncoded));

        $reply = $replyData["reply"];
        $replyIndex = $replyData["index"];

        $replyAssoc = [
            "rid" => $reply[0],
            "uid" => $reply[1],
            "content" => $reply[2],
            "date" => $reply[3]
        ];
        
        if ($replyAssoc["uid"] == $user || $comment["uid"] == $user) {
            
            // Update Database
            $updateQuery = "UPDATE comments SET usersReplied=JSON_REMOVE('$repliesEncoded', '$[$replyIndex]'), repliesCount=repliesCount-1 WHERE cid='$CID'";

            if ($db->query($updateQuery)) {
                echo json_encode([
                    "success" => true,
                    "message" => "Deleted Reply!"
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
                "message" => "You don't have Permission to Perform this Action"
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
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Delete Replies"
    ]);
}

function findReply($replyId, $replies) {
    $index = 0;

    foreach ($replies as $reply) {
        if (intval($reply[0]) == intval($replyId)) {
            return [
                "index" => $index,
                "reply" => $reply
            ];
        }

        $index++;
    }

    return false;
}

?>