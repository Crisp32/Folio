<?php
/**
 * Folio Forum Saving
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {

    // Get Requested Changes
    $icon = escapeString($_REQUEST["icon"]);
    $desc = escapeString($_REQUEST["desc"]);
    $forumName = escapeString($_REQUEST["forum"]);
    $user = $_SESSION["user"];

    // Validate Input
    if (strlen($icon) > 150) {
        echo json_encode([
            "success" => false,
            "message" => "Forum Icon URL Must be 150 Characters or Less"
        ]);
    }
    else if (strlen($desc) > 300) {
        echo json_encode([
            "success" => false,
            "message" => "Forum Description Must be 300 Characters or Less"
        ]);
    }
    else if (strlen($desc) == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Forum Description Must be Greater than 0 Characters"
        ]);
    }
    else if (!forumExists($forumName)) {
        echo json_encode([
            "success" => false,
            "message" => "Unknown Forum"
        ]);
    }
    else if (!validURL($icon)) {
        echo json_encode([
            "success" => false,
            "message" => "The Specified Icon dosen't Exist"
        ]);
    }
    else {
        // Null Check Forum Icon
        if (empty($icon)) {
            $icon = randomProfileImage();
        }

        // Get Forum Data
        $forum = getForumDataById(getForumIdByName($forumName));

        // Check User Permissions
        if ($forum->isModerator($user)) {
            $forum->update("iconPath", $icon);
            $forum->update("description", $desc);

            echo json_encode([
                "success" => true,
                "icon" => $icon
            ]);
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => "You don't have Permission to Perform this Action"
            ]);
        }
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Perform this Action"
    ]);
}

?>