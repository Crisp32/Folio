<?php
/**
 * Folio Profile Loader
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

// Retrieve User Info from SQLite
if (!empty($_REQUEST["query"]) && strpos($_REQUEST["query"], " ") == false) {

    $usearch = escapeString($_REQUEST["query"]);
    $user = new User($db);
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
        
        // Null Check Image
        if (empty($profileImage)) {
            $profileImage = "https://ui-avatars.com/api/?background=c9c9c9&color=131313&size=224&bold=true&font-size=0.35&length=3&name=$profileName";
        }

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
            "bio" => $profileBio,
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