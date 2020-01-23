<?php
/**
 * Folio Get Banned Members
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

$user = $_SESSION["user"];

// Init DB
$db = db();

// Check if Forum Exists
$forumName = escapeString($_REQUEST["forum"]);

if (forumExists($forumName)) {

    // Get Forum Data
    $forumId = getForumIdByName($forumName);
    $forum = getForumDataById($forumId);

    // Check if Forum has Current User as Member
    if ($forum->isModerator($user)) {

        // Get List of Banned Members' UID
        $members = $forum->getBannedMembers();
        $membersJSON = [];

        // Process each Member and Push to JSON Array
        foreach ($members as $member) {
            $memberData = new User();
            $memberData->getUserDataByUID($member);

            if ($memberData->user["username"] !== null) {

                // Create Array
                $memberDataJSON = [
                    "username" => $memberData->user["username"],
                    "image" => $memberData->user["profileImagePath"]
                ];

                array_push($membersJSON, $memberDataJSON);
            }
        }

        // Send Response to Client
        echo json_encode([
            "success" => true,
            "bans" => $membersJSON
        ]);
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => "You don't have Permission to View this"
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "There are no Forums with that Name"
    ]);
}