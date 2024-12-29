<?php

/**
 * Folio Forum Joiner
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {

    // Get Active User
    $user = $_SESSION["user"];
    $userInstance = new User();
    $userInstance->getUserDataByUID($user);

    // Get Requested Forum
    $forum = escapeString($_REQUEST["forum"]);

    if (!empty($forum) || !forumExists($forum)) {
        $forumInstance = getForumDataById(getForumIdByName($forum));

        // Check if Banned from Forum
        if (!$forumInstance->isBanned($user)) {

            // Check if User is already in Forum
            if ($forumInstance->hasMember($user)) {
                $removeMemberQry = $forumInstance->removeMember($user);
                if ($removeMemberQry["success"]) {

                    echo json_encode([
                        "success" => true,
                        "joined" => false,
                        "reload" => $removeMemberQry["doReload"]
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => $db->error
                    ]);
                }
            } else {
                if ($forumInstance->addMember($user)) {
                    echo json_encode([
                        "success" => true,
                        "joined" => true
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => $db->error
                    ]);
                }
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "You are Banned from this Forum"
            ]);
        }
    } else {
        json_encode([
            "success" => false,
            "message" => "A Forum with that Name dosen't exist"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "You must Be Logged in to Join Forums"
    ]);
}
