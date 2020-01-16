<?php
/**
 * Folio Profile Forum Grabber
 * Connell Reffo 2020
 */

include_once "app_main.php";
session_start();

$profile = escapeString($_REQUEST["profile"]);

// Init DB
$db = db();

// Get Profile Data
$prof = new User();
$prof->getUserDataByName($profile);

if (userExists($prof->user["uid"])) {

    // Get Forums
    $forums = json_decode($prof->user["joinedForums"]);
    $profileForums = [];

    $index = 0;
    foreach ($forums as $forum) {
        $forumData = getForumDataById($forum);
        if (!empty($forum)) {
            $forumJSON = [
                "owner" => getUserData("username", "uid='".$forumData->ownerUID."'"),
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