<?php
/**
 * Folio Forum Creator
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

if (validateSession($_SESSION["user"])) {
    $userInstance = new User($db);
    $userInstance->getUserDataByUID($_SESSION["user"]);
    $user = $_SESSION["user"];

    // Validate Input
    $forumName = escapeString($_REQUEST["name"]);
    $forumDesc = escapeString($_REQUEST["description"]);
    $forumIcon = escapeString($_REQUEST["icon"]);

    if (strlen($forumName) > 20) {
        echo json_encode([
            "success" => false,
            "message" => "Forum Name Must be Under 20 Characters"
        ]);
    }
    else if (empty($forumName) || strlen($forumName) == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid Forum Name"
        ]);
    }
    if (strlen($forumDesc) > 300) {
        echo json_encode([
            "success" => false,
            "message" => "Forum Description Must be Under 300 Characters"
        ]);
    }
    else if (empty($forumDesc) || strlen($forumDesc) == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid Forum Description"
        ]);
    }
    if (strlen($forumIcon) > 150) {
        echo json_encode([
            "success" => false,
            "message" => "Forum Icon URL Must be Under 150 Characters"
        ]);
    }
    else if (!validURL($forumIcon)) {
        echo json_encode([
            "success" => false,
            "message" => "The Specified Icon dosen't Exist"
        ]);
    }
    else if (forumExists($db, $forumName)) {
        echo json_encode([
            "success" => false,
            "message" => "A Forum with this Name Already Exists"
        ]);
    }
    else {
        // Null Check Forum Icon
        if (empty($forumIcon)) {
            $forumIcon = randomProfileImage();
        }

        // Insert new Forum into DB
        $forum = new Forum($db, $user, $forumName, $forumIcon, $forumDesc);
        $createForum = $forum->create();

        if (empty($createForum)) {
            echo json_encode([
                "success" => false,
                "message" => "SQLite Error"
            ]);
        }
        else {
            // Add Active User as Member
            $forum->FID = getForumIdByName($db, $forumName);
            if ($forum->addMember($user)) {
                echo json_encode([
                    "success" => true,
                    "forum" => [
                        "0" => [
                            "owner" => $userInstance->user["username"],
                            "name" => $forumName,
                            "description" => $forumDesc,
                            "icon" => $forumIcon,
                            "date" => date("j-n-Y")
                        ]
                    ]
                ]);
            }
            else {
                echo json_encode([
                    "success" => false,
                    "message" => "SQLite Error"
                ]);
            }
            
        }
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Create Forums"
    ]);
}

?>