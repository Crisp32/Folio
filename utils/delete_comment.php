<?php
/**
 * Folio Comment Deletion
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();
// Null Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $cid = escapeString($_REQUEST["cid"]);
    $profileCID = escapeString(getUserData("uid", "username='".$_REQUEST["profile"]."'"));

    // Validate that Active User has Permission to Delete Comment
    $commentOwner = getCommentData("commenterId", "profile", "cid='$cid'");
    $commentProfile = getCommentData("uid", "profile", "cid='$cid'");
    $profileOwner = ($user == $profileCID && $commentProfile == $profileCID);

    if ($user == $commentOwner || $profileOwner) {

        $delQuery = "DELETE FROM comments WHERE cid=$cid AND type='profile'";
        $updateQuery = "UPDATE users SET commentCount=commentCount-1 WHERE uid=$profileCID";
        if ($db->query($delQuery) && $db->query($updateQuery)) {
            echo json_encode([
                "success" => true,
                "message" => "Deleted Comment!"
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
        "message" => "You must be Logged in to Perform this Action"
    ]);
}

?>