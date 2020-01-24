<?php
/**
 * Folio Forum Creator
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

$illegalChars = "'&*()^%$#@!+:-[]";

if (validateSession($_SESSION["user"])) {
    $userInstance = new User($db);
    $userInstance->getUserDataByUID($_SESSION["user"]);
    $user = $_SESSION["user"];

    // Validate Input
    $forumName = escapeString($_REQUEST["name"]);
    $forumDesc = escapeString($_REQUEST["description"]);
    $forumIcon = escapeString($_REQUEST["icon"]);

    if (strlen($forumName) > 15) {
        echo json_encode([
            "success" => false,
            "message" => "Forum Name Must be Under 15 Characters"
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
    else if (forumExists($forumName)) {
        echo json_encode([
            "success" => false,
            "message" => "A Forum with this Name Already Exists"
        ]);
    }
    else if (strpbrk($forumName, $illegalChars)) {
        echo json_encode([
            "success" => false,
            "message" => "Forum Name Cannot Contain $illegalChars"
        ]);
    }
    else {
        // Null Check Forum Icon
        if (empty($forumIcon)) {
            $forumIcon = randomProfileImage();
        }

        // Insert new Forum into DB
        $forum = new Forum($user, $forumName, $forumIcon, $forumDesc);
        $createForum = $forum->create();

        if (empty($createForum)) {
            echo json_encode([
                "success" => false,
                "message" => $db->error
            ]);
        }
        else {
            // Add Active User as Member
            $forum->FID = getForumIdByName($forumName);
            if ($forum->addMember($user)) {
                echo json_encode([
                    "success" => true,
                    "forum" => [
                        "0" => [
                            "owner" => $userInstance->user["username"],
                            "name" => $forumName,
                            "description" => $forumDesc,
                            "icon" => $forumIcon,
                            "date" => currentDate()
                        ]
                    ]
                ]);
            }
            else {
                echo json_encode([
                    "success" => false,
                    "message" => $db->error
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