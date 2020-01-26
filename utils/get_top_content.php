<?php
/**
 * Folio Top Content Grabber
 * @author Connell Reffo
 */

include_once "app_main.php";

// Init Database
$db = db();

$LIMIT = 10;

// Handle Request
$contentType = $_REQUEST["contentType"];

if ($contentType == $CONTENT_USERS) {
    $query = $db->query("SELECT username, voteCount, profileImagePath FROM users ORDER BY voteCount DESC LIMIT $LIMIT");

    if ($query) {
        $userList = [];

        while ($user = $query->fetch_array(MYSQLI_ASSOC)) {
            array_push($userList, [
                "username" => $user["username"],
                "image" => $user["profileImagePath"],
                "votes" => intval($user["voteCount"])
            ]);
        }

        // Send List Back to Client
        echo json_encode([
            "success" => true,
            "users" => $userList
        ]);
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => $db->error
        ]);
    }
}
else if ($contentType == $CONTENT_FORUMS) {
    $query = $db->query("SELECT name, iconPath, JSON_LENGTH(members) AS memberCount FROM forums ORDER BY JSON_LENGTH(members) DESC LIMIT $LIMIT");

    if ($query) {
        $forumList = [];

        while ($forum = $query->fetch_array(MYSQLI_ASSOC)) {
            array_push($forumList, [
                "name" => $forum["name"],
                "icon" => $forum["iconPath"],
                "members" => intval($forum["memberCount"])
            ]);
        }

        // Send List Back to Client
        echo json_encode([
            "success" => true,
            "forums" => $forumList
        ]);
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => $db->error
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Content Type"
    ]);
}