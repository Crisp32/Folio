<?php

/**
 * Folio Forum Post Grabber
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Request Params
$min = escapeString(intval($_REQUEST["min"]));
$max = escapeString(intval($_REQUEST["max"]));

if (isset($_REQUEST["forum"])) {
    $forum = escapeString($_REQUEST["forum"]);
}

// Null Check Forum Name
if (isset($forum) || isset($_REQUEST["username"]) || isset($_REQUEST["suggested"])) {
    $user = $_SESSION["user"];

    // Select Sorting Method
    $sortMethod = $_REQUEST["sort"];
    $sort;

    switch ($sortMethod) {
        case $SORT_OLD:
            $sort = "ORDER BY pid ASC LIMIT $min, $max";
            break;
        case $SORT_POPULAR:
            $sort = "ORDER BY voteCount DESC LIMIT $min, $max";
            break;
        default:
            $sort = "ORDER BY pid DESC LIMIT $min, $max";
            break;
    }

    if (isset($_REQUEST["username"])) { // For Displaying User Posts on Profile
        $username = $_REQUEST["username"];
        $uid = getUserData("uid", "username='$username'");

        $condition = "uid=$uid";
    } else if (isset($_REQUEST["suggested"])) {
        $suggested = parseBool($_REQUEST["suggested"]);

        if ($suggested) {
            $condition = "voteCount>0 AND commentCount>0";
        }
    } else { // For Normal Forum Viewing
        $forumInstance = getForumDataById(getForumIdByName($forum));
        $forumId = $forumInstance->FID;

        $condition = "fid=$forumId";
    }

    // Get Forum Posts from Database
    $postQuery = $db->query("SELECT * FROM forumPosts WHERE $condition $sort");

    if ($postQuery) {
        $posts = [];

        while ($post = $postQuery->fetch_array(MYSQLI_ASSOC)) {

            if (isset($_REQUEST["username"]) || isset($suggested)) {
                $forumInstance = getForumDataById($post["fid"]);
                $forumId = $forumInstance->FID;
            }

            // Check if Voted on Post
            $upvoted = false;
            $downvoted = false;
            $votes = json_decode($post["votes"], true);

            if (in_array($user, $votes["upvotes"])) {
                $upvoted = true;
            } else if (in_array($user, $votes["downvotes"])) {
                $downvoted = true;
            }

            // Get Rank
            $rank = "member";

            if ($forumInstance->ownerUID == $post["uid"]) {
                $rank = "owner";
            } else if ($forumInstance->isModerator($post["uid"])) {
                $rank = "mod";
            }

            // Get Delete Permissions
            $canEdit = false;

            if ($post["uid"] == $user || $forumInstance->isModerator($user)) {
                $canEdit = true;
            }

            // Push to Posts Array
            array_push($posts, [
                "title" => htmlFormat(utf8_decode($post["title"])),
                "body" => htmlFormat(utf8_decode($post["body"])),
                "posterName" => getUserData("username", "uid=" . $post["uid"]),
                "voteCount" => intval($post["voteCount"]),
                "date" => $post["date"],
                "pid" => $post["pid"],
                "rank" => $rank,
                "upvoted" => $upvoted,
                "downvoted" => $downvoted,
                "canEdit" => $canEdit,
                "comments" => intval($post["commentCount"]),
                "forumName" => $forumInstance->name
            ]);
        }

        // Send Posts to Client
        echo json_encode([
            "success" => true,
            "posts" => $posts
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => $db->error
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Forum Name"
    ]);
}
