<?php
/**
 * Folio Profile Forum Grabber
 * Connell Reffo 2020
 */

include_once "app_main.php";
session_start();

$profile = $_REQUEST["profile"];

// Init DB
$db = new SQLite3("../db/folio.db");

// Get Profile Data
$prof = new User($db);
$prof->getUserDataByName($profile);

if (userExists($db, $prof->user["uid"])) {

    // Get Forums
    $forums = explode(":", $prof->user["joinedForums"]);
    $profileForums = [];

    $index = 0;
    foreach ($forums as $forum) {
        $forumData = getForumDataById($db, $forum);
        if (!empty($forum)) {
            $forumJSON = [
                "owner" => getUserData($db, "username", "uid='".$forumData->ownerUID."'"),
                "name" => $forumData->name,
                "description" => $forumData->description,
                "icon" => $forumData->iconURL,
                "date" => $forumData->date
            ];
    
            array_push($profileForums, $forumJSON);
            $index++;
        }
    }

    // Send Result to Client
    echo json_encode([
        "forums" => $profileForums
    ]);
}
else {
    echo json_encode([
        "success" => false,
        "message" => "The Requested Profile dosen't Exist"
    ]);
}

?>