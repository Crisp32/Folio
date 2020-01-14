<?php
/**
 * Folio Forum Poster
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

// Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $forumName = escapeString($_REQUEST["forum"]);
    $forumId = getForumIdByName($db, $forumName);
    $forum = getForumDataById($db, $forumId);

    // Check if User is in Forum
    if ($forum->hasMember($user)) {
        $title = $_REQUEST["title"];
        $body = $_REQUEST["body"];

        // Validate Input
        if (strlen($title) > 20) {
            echo json_encode([
                "success" => false,
                "message" => "Title Must not Exceed 20 Characters"
            ]);
        }
        else if (strlen($title) == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Title Must be Greater than 0 Characters"
            ]);
        }
        else if (strlen($body) > 300) {
            echo json_encode([
                "success" => false,
                "message" => "Body Must not Exceed 300 Characters"
            ]);
        }
        else if (strlen($body) == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Body Must be Greater than 0 Characters"
            ]);
        }
        else {
            // Add to Database
            if ($forum->addPost(escapeString($title), escapeString($body), $user, $forumId)) {
                echo json_encode([
                    "success" => true,
                    "post" => [
                        "title" => $title,
                        "body" => $body,
                        "posterName" => getUserData($db, "username", "uid='$user'"),
                        "date" => date("j-n-Y"),
                        "upvoted" => false,
                        "downvoted" => false
                    ]
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
            "message" => "You be a Member of this Forum to Post this"
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Post to Forums"
    ]);
}