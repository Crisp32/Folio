<?php
/**
 * Folio Profile Loader
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Retrieve User Info from SQLite
if (!empty($_REQUEST["query"]) && strpos($_REQUEST["query"], " ") == false) {

    $usearch = escapeString($_REQUEST["query"]);
    $user = new User();
    $user->getUserDataByName($usearch);

    // Collect User Data
    $uid = $user->user["uid"];
    $profileImage = $user->user["profileImagePath"];
    $profileName = $user->user["username"];
    $profileBio = $user->user["profileBio"];
    $profileLocation = $user->user["accountLocation"];
    $allowComments = $user->user["allowComments"];
    $date = $user->user["date"];
    $voteCount = $user->user["voteCount"];

    // Null Check DB Response
    if (!empty($profileName) && !empty($uid)) {
        
        // Null Check Date
        if (empty($date)) {
            $date = "00-00-0000";
        }

        // Check Votes
        $upvoted = false;
        $downvoted = false;
        $isActiveUser = false;

        if (isset($_SESSION["user"])) {
            $activeUser = $_SESSION["user"];

            if ($activeUser == $uid) {
                $isActiveUser = true;
            }

            if ($user->upvotedBy($activeUser)) {
                $upvoted = true;
            }
            else if ($user->downvotedBy($activeUser)) {
                $downvoted = true;
            }
        }

        // Send Response to Client
        echo json_encode(array(
            "success" => true,
            "activeUser" => $isActiveUser,
            "username" => $profileName,
            "image" => $profileImage,
            "bio" => htmlFormat(utf8_decode($profileBio)),
            "location" => $profileLocation,
            "votes" => $voteCount,
            "upvoted" => $upvoted,
            "downvoted" => $downvoted,
            "date" => $date,
            "comments" => $allowComments
        ));
    }
    else {
        // Return 404 Error
        echo json_encode(array(
            "success" => false,
            "message" => "404 Error: User not Found"
        ));
    }
}
else {
    // Return 404 Error
    echo json_encode(array(
        "success" => false,
        "message" => "404 Error: User not Found"
    ));
}


?>