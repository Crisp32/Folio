<?php
/**
 * Folio Forum Members Grabber
 * Connell Reffo 2020
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

// Check if Forum Exists
$forumName = $_REQUEST["forum"];

if (forumExists($db, $forumName)) {

    // Get List of Members' UID
    $forumId = getForumIdByName($db, $forumName);
    $forum = getForumDataById($db, $forumId);

    $members = $forum->getMembers();
    $membersJSON = [];

    // Process each Member and Push to JSON Array
    foreach ($members as $member) {
        if ($member !== null && $member !== "") {
            $memberData = new User($db);
            $memberData->getUserDataByUID($member);
            $user = $_SESSION["user"];

            // Check if Member is also a Moderator
            $moderator = $forum->isModerator($member);
            $owner = ($member == $forum->ownerUID && isset($member));

            // Check Current user Permissions
            $promotable = false;
            $demotable = false;
            $removable = false;

            if (isset($user)) {

                if ($user == $forum->ownerUID && !$owner) { // Is Owner
                    $removable = true;

                    // Check if Member is Mod
                    if ($moderator) {
                        $demotable = true;
                    }
                    else {
                        $promotable = true;
                    }
                }
                else if ($forum->isModerator($user) && !$moderator) { // Is Moderator
                    $removable = true;

                    // Check if Member is Mod
                    if (!$moderator) {
                        $promotable = true;
                    }
                }
            }

            // Create Array
            $memberDataJSON = [
                "username" => $memberData->user["username"],
                "image" => $memberData->user["profileImagePath"],
                "moderator" => $moderator,
                "owner" => $owner,
                "removable" => $removable,
                "promotable" => $promotable,
                "demotable" => $demotable
            ];

            array_push($membersJSON, $memberDataJSON);
        }
    }

    // Send Response to Client
    echo json_encode([
        "success" => true,
        "members" => $membersJSON
    ]);
}
else {
    echo json_encode([
        "success" => false,
        "message" => "There are no Forums with that Name"
    ]);
}

?>