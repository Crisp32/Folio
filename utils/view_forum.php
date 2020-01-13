<?php
/**
 * Folio Forum Grabber
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

// Null Check Query
if (isset($_REQUEST["fquery"]) && !empty($_REQUEST["fquery"]) && forumExists($db, $_REQUEST["fquery"])) {

    // Grab Forum Data
    $user = $_SESSION["user"];
    $fquery = $_REQUEST["fquery"];
    $forumId = getForumIdByName($db, $fquery);
    $forum = getForumDataById($db, $forumId);

    if (!empty($forum)) {

        // Check if Current User is a Member of the Forum
        $joinedForum = $forum->hasMember($user);

        // Check if Moderator of Forum
        $moderator = $forum->isModerator($user);

        // Check if Banned
        $banned = $forum->isBanned($user);

        // Send Response to Client
        echo json_encode([
            "success" => true,
            "forum" => [
                "joined" => $joinedForum,
                "members" => count($forum->getMembers()) - 1,
                "name" => $forum->name,
                "description" => htmlFormat($forum->description),
                "icon" => $forum->iconURL,
                "date" => $forum->date,
                "moderator" => $moderator,
                "banned" => $banned
            ]
        ]);
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => "There are no Forums with that Name"
        ]);
    }
}
else {  
    echo json_encode([
        "redirect" => true
    ]);
}

?>