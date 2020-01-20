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
$forum = escapeString($_REQUEST["forum"]);
$min = escapeString(intval($_REQUEST["min"]));
$max = escapeString(intval($_REQUEST["max"]));

// Null Check Forum Name
if (!empty($forum)) {
    $forumInstance = getForumDataById(getForumIdByName($forum));
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

    // Get Forum Posts from Database
    $forumId = $forumInstance->FID;
    $postQuery = $db->query("SELECT * FROM forumPosts WHERE fid=$forumId $sort");

    if ($postQuery) {
        $posts = [];

        while ($post = $postQuery->fetch_array(MYSQLI_ASSOC)) {
            
            // Check if Voted on Post
            $upvoted = false;
            $downvoted = false;
            $votes = json_decode($post["votes"], true);

            if (in_array($user, $votes["upvotes"])) {
                $upvoted = true;
            }
            else if (in_array($user, $votes["downvotes"])) {
                $downvoted = true;
            }

            // Get Rank
            $rank = "member";

            if ($forumInstance->ownerUID == $post["uid"]) {
                $rank = "owner";
            }
            else if ($forumInstance->isModerator($post["uid"])) {
                $rank = "mod";
            }

            // Get Delete Permissions
            $canEdit = false;

            if ($post["uid"] == $user || $forumInstance->isModerator($user)) {
                $canEdit = true;
            }

            // Push to Posts Array
            array_push($posts, [
                "title" => htmlFormat($post["title"]),
                "posterName" => getUserData("username", "uid=" . $post["uid"]),
                "body" => htmlFormat($post["body"]),
                "voteCount" => intval($post["voteCount"]),
                "date" => $post["date"],
                "pid" => $post["pid"],
                "rank" => $rank,
                "upvoted" => $upvoted,
                "downvoted" => $downvoted,
                "canEdit" => $canEdit,
                "comments" => intval($post["commentCount"])
            ]);
        }

        // Send Posts to Client
        echo json_encode([
            "success" => true,
            "posts" => $posts
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
        "message" => "Invalid Forum Name"
    ]);
}