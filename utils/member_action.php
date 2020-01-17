<?php
/**
 * Folio Member Manager
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];

    // Get Requested Action
    $targetUsername = escapeString($_REQUEST["user"]);
    $targetForumName = escapeString($_REQUEST["forum"]);
    $targetAction = $_REQUEST["action"];

    // Get Forum and User Data
    $forumInstance = getForumDataById(getForumIdByName($targetForumName));
    $userInstance = new User();
    $userInstance->getUserDataByName($targetUsername);

    $targetUserId = $userInstance->user["uid"];

    // Validate Current User Permissions
    if ($forumInstance->isModerator($user) && $forumInstance->hasMember($user)) {

        // Validate Action
        switch ($targetAction) {
            case $ACTION_KICK:

                // Check Ranks
                if (!$forumInstance->isModerator($targetUserId) || $user == $forumInstance->ownerUID) {
                    $forumInstance->removeMember($targetUserId);

                    // Send Success to Client
                    echo json_encode([
                        "success" => true,
                        "message" => "Kicked $targetUsername"
                    ]);
                }
                else {
                    echo json_encode([
                        "success" => false,
                        "message" => "You can't Kick this User"
                    ]);
                }
                break;
            case $ACTION_BAN:

                // Check Ranks
                if (!$forumInstance->isModerator($targetUserId) || $user == $forumInstance->ownerUID) {
                    $forumInstance->banMember($targetUserId);

                    // Send Success to Client
                    echo json_encode([
                        "success" => true,
                        "message" => "Banned $targetUsername"
                    ]);
                }
                else {
                    echo json_encode([
                        "success" => false,
                        "message" => "You can't Ban this User"
                    ]);
                }
                break;
            case $ACTION_PROMOTE:

                // Check Ranks
                if (!$forumInstance->isModerator($targetUserId) || $user == $forumInstance->ownerUID) {
                    if ($forumInstance->promote($targetUserId)) {
                        echo json_encode([
                            "success" => true,
                            "message" => "Promoted $targetUsername"
                        ]);
                    }
                    else {
                        echo json_encode([
                            "success" => false,
                            "message" => "Unable to Promote User"
                        ]);
                    }

                }
                else {
                    echo json_encode([
                        "success" => false,
                        "message" => "You can't Promote this User"
                    ]);
                }
                break;
            case $ACTION_DEMOTE:

                // Check Ranks
                if (!$forumInstance->isModerator($targetUserId) || $user == $forumInstance->ownerUID) {
                    if ($forumInstance->demote($targetUserId)) {
                        echo json_encode([
                            "success" => true,
                            "message" => "Demoted $targetUsername"
                        ]);
                    }
                    else {
                        echo json_encode([
                            "success" => false,
                            "message" => "Unable to Demote User"
                        ]);
                    }

                }
                else {
                    echo json_encode([
                        "success" => false,
                        "message" => "You can't Demote this User"
                    ]);
                }
                break;
            case $ACTION_UNBAN:

                // Check Ranks
                if (!$forumInstance->isModerator($targetUserId) || $user == $forumInstance->ownerUID) {
                    if ($forumInstance->unban($targetUserId)) {
                        echo json_encode([
                            "success" => true,
                            "message" => "Unbanned $targetUsername"
                        ]);
                    }
                    else {
                        echo json_encode([
                            "success" => false,
                            "message" => "Unable to Unban User"
                        ]);
                    }

                }
                else {
                    echo json_encode([
                        "success" => false,
                        "message" => "You can't Unban this User"
                    ]);
                }
                break;
            default:
                echo json_encode([
                    "success" => false,
                    "message" => "Invalid Action"
                ]);
                break;
        }
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => "You don't have Permission to Perform this Action"
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Perform this Action"
    ]);
}