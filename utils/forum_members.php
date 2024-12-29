<?php

/**
 * Folio Forum Members Grabber
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Check if Forum Exists
$forumName = escapeString($_REQUEST["forum"]);

if (forumExists($forumName)) {

    // Get List of Members' UID
    $forumId = getForumIdByName($forumName);
    $forum = getForumDataById($forumId);

    $members = $forum->getMembers();
    $membersJSON = [];

    // Process each Member and Push to JSON Array
    foreach ($members as $member) {

        // Get Member Data
        $memberData = new User();
        $memberData->getUserDataByUID($member);

        if ($memberData->user["username"] !== null) {
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
                    } else {
                        $promotable = true;
                    }
                } else if ($forum->isModerator($user) && !$moderator) { // Is Moderator
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
} else {
    echo json_encode([
        "success" => false,
        "message" => "There are no Forums with that Name"
    ]);
}
