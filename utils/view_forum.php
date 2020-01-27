<?php
/**
 * Folio Forum Grabber
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Null Check Query
if (isset($_REQUEST["fquery"]) && !empty($_REQUEST["fquery"]) && forumExists($_REQUEST["fquery"])) {

    // Grab Forum Data
    $user = $_SESSION["user"];
    $fquery = escapeString($_REQUEST["fquery"]);
    $forumId = getForumIdByName($fquery);
    $forum = getForumDataById($forumId);

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
                "members" => count($forum->getMembers()),
                "name" => utf8_decode($forum->name),
                "description" => htmlFormat(utf8_decode($forum->description)),
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