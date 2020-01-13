<?php
/**
 * Folio Forum Joiner
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

// Check Session
if (validateSession($_SESSION["user"])) {

    // Get Active User
    $user = $_SESSION["user"];
    $userInstance = new User($db);
    $userInstance->getUserDataByUID($user);

    // Get Requested Forum
    $forum = $_REQUEST["forum"];

    if (!empty($forum) || !forumExists($db, $forum)) {
        $forumInstance = getForumDataById($db, getForumIdByName($db, $forum));

        // Check if Banned from Forum
        if (!$forumInstance->isBanned($user)) {
            
            // Check if User is already in Forum
            if ($forumInstance->hasMember($user)) {
                if ($forumInstance->removeMember($user)) {
                    echo json_encode([
                        "success" => true,
                        "joined" => false,
                        "reload" => (count($forumInstance->getMembers()) == 1)
                    ]);
                }
                else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Database Error"
                    ]);
                }
            }
            else {
                if ($forumInstance->addMember($user)) {
                    echo json_encode([
                        "success" => true,
                        "joined" => true
                    ]);
                }
                else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Database Error"
                    ]);
                }
            }
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => "You are Banned from this Forum"
            ]);
        }
    }
    else {
        json_encode([
            "success" => false,
            "message" => "A Forum with that Name dosen't exist"
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You must Be Logged in to Join Forums"
    ]);
}

?>