<?php
/**
 * Folio Comment Deletion
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

// Null Check Session
if (isset($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $cid = escapeString($_REQUEST["cid"]);
    $profileCID = escapeString(getUserData($db, "uid", "username='".$_REQUEST["profile"]."'"));

    // Validate that Active User has Permission to Delete Comment
    $commentOwner = getCommentData($db, "commenterId", "profile", "cid='$cid'");
    $commentProfile = getCommentData($db, "uid", "profile", "cid='$cid'");
    $profileOwner = ($user == $profileCID && $commentProfile == $profileCID);

    if ($user == strval($commentOwner) || $profileOwner) {

        $delQuery = "DELETE FROM comments WHERE cid='$cid' AND type='profile'";
        if ($db->query($delQuery)) {
            echo json_encode([
                "success" => true,
                "message" => "Deleted Comment!"
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