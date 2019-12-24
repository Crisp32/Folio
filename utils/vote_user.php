<?php
/**
 * Folio User Vote Handler
 * Connell Reffo 2019
 */

include "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

$upvoted = $_REQUEST["upvote"];
$downvoted = $_REQUEST["downvote"];

$targetUser = $_REQUEST["target"];
$targetUser = getUserData($db, "uid", "username='$targetUser'");

if ($upvoted == "true") {
    $upvoted = true;
}
else {
    $upvoted = false;
}

if ($downvoted == "true") {
    $downvoted = true;
}
else {
    $downvoted = false;
}

// Check if User is Logged in
if (isset($_SESSION["user"])) {
    $activeUser = $_SESSION["user"];

    // Check Target User
    if (!empty(getUserData($db, "username", "uid='$targetUser'"))) {

        $votes = getUserData($db, "votes", "uid='$targetUser'");
        $finalVotesData = "";

        if ($upvoted && !$downvoted) { // Upvote
            if (strpos(strval($votes), strval($activeUser)) !== false) {
                $finalVotesData = str_replace(":$activeUser-", ":$activeUser+", $votes);
            }
            else {
                $finalVotesData = $votes . ":$activeUser+";
            }
        }
        else if (!$upvoted && $downvoted) { // Downvote
            if (strpos(strval($votes), strval($activeUser)) !== false) {
                $finalVotesData = str_replace(":$activeUser+", ":$activeUser-", $votes);
            }
            else {
                $finalVotesData = $votes . ":$activeUser-";
            }
        }
        else { // Neutral
            $finalVotesData = preg_replace('/:'.$activeUser.'./', "", $votes);
        }

        // Send Response
        $qry = updateUser($db, "votes", $finalVotesData, "uid='$targetUser'");
        $voteCount = calcVotes($finalVotesData);

        if ($qry) {
            // Update Vote Count in DB
            if (!updateUser($db, "voteCount", $voteCount, "uid='$targetUser'")) {
                echo json_encode([
                    "error" => "SQLite Error"
                ]);
            }
            else {
                // Success Response
                echo json_encode([
                    "votes" => $voteCount,
                ]);
            }
        }
        else {
            echo json_encode([
                "error" => "SQLite Error"
            ]);
        }
    }
    else {
        // Send Response
        echo json_encode([
            "votes" => 0
        ]);
    }
}
else {
    // Return Error
    echo json_encode([
        "error" => "You must be Logged in to Upvote/Downvote"
    ]);
}

?>