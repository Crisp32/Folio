<?php
/**
 * Folio Forum Poster
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $forumName = escapeString($_REQUEST["forum"]);
    $forumId = getForumIdByName($forumName);
    $forum = getForumDataById($forumId);

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
            $title = utf8_encode($title);
            $body = utf8_encode($body);

            $addPostQuery = $forum->addPost(escapeString($title), escapeString($body), $user, $forumId);
            
            if ($addPostQuery["success"]) {

                // Get Rank
                $rank = "member";

                if ($forum->ownerUID == $user) {
                    $rank = "owner";
                }
                else if ($forum->isModerator($user)) {
                    $rank = "mod";
                }

                // Send Successful Response
                echo json_encode([
                    "success" => true,
                    "post" => [
                        "0" => [
                            "title" => utf8_decode($title),
                            "body" => utf8_decode($body),
                            "posterName" => getUserData("username", "uid='$user'"),
                            "date" => currentDate(),
                            "rank" => $rank,
                            "pid" => $addPostQuery["pid"],
                            "voteCount" => 0,
                            "upvoted" => false,
                            "downvoted" => false,
                            "comments" => 0,
                            "canEdit" => true
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