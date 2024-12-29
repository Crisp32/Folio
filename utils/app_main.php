<?php

/**
 * Folio Main PHP File
 * @author Connell Reffo
 */

// Includes
include_once "database.php";
include_once "PHPDebugger/PHPDebugger.php";
include_once "classes.php";

// Init DB
$db = db();

// Global Constants
$TYPE_PROFILE = "profile";
$TYPE_FORUMPOST = "forumpost";

$ACTION_KICK = "kick";
$ACTION_BAN = "ban";
$ACTION_PROMOTE = "promote";
$ACTION_DEMOTE = "demote";
$ACTION_UNBAN = "unban";

$FILTER_USERS = "users";
$FILTER_FORUMS = "forums";
$FILTER_ALL = "all";

$SORT_NEW = "new";
$SORT_OLD = "old";
$SORT_POPULAR = "popular";

$CONTENT_USERS = "users";
$CONTENT_FORUMS = "forums";

// Email Variables
$folioEmail = "foliowebapp@gmail.com";
$folioName = "Fol.io";

// List of Possible Countries
$countries = [
    "Argentina",
    "Australia",
    "Bangladesh",
    "Brazil",
    "Canada",
    "Columbia",
    "Costa Rica",
    "Cuba",
    "China",
    "Chile",
    "Egypt",
    "Finland",
    "France",
    "Germany",
    "India",
    "Indonesia",
    "Iran",
    "Italy",
    "Japan",
    "Mexico",
    "Netherlands",
    "Nigeria",
    "Pakistan",
    "Poland",
    "Philippines",
    "Russia",
    "South Africa",
    "South Korea",
    "Sweden",
    "Switzerland",
    "Taiwan",
    "Thailand",
    "Turkey",
    "United States",
    "United Kingdom"
];

// Get Forum IDs By Name
function getForumIdByName($forumName)
{
    $db = $GLOBALS["db"];
    $query = "SELECT fid FROM forums WHERE name='$forumName'";
    $FID = $db->query($query)->fetch_array(MYSQLI_ASSOC)["fid"];

    return $FID;
}

// Get all Forum Data
function getForumDataById($forumId)
{
    $db = $GLOBALS["db"];

    $query = "SELECT * FROM forums WHERE fid=$forumId";
    $forumData = $db->query($query);

    if ($forumData) {
        $forumArray = $forumData->fetch_array(MYSQLI_ASSOC);

        // Create Forum Instance
        $forum = new Forum($forumArray["owner"], $forumArray["name"], $forumArray["iconPath"], $forumArray["description"]);
        $forum->FID = $forumId;
        $forum->date = $forumArray["date"];

        return $forum;
    } else {
        return false;
    }
}

// Check if a Forum Exists
function forumExists($forumName)
{
    $db = $GLOBALS["db"];
    $query = "SELECT name FROM forums WHERE name='$forumName'";
    $result = $db->query($query)->fetch_array(MYSQLI_ASSOC)["name"];

    if (!empty($result)) {
        return true;
    } else {
        return false;
    }
}

// Check if User Exists
function userExists($uid)
{
    $db = $GLOBALS["db"];
    $query = "SELECT uid FROM users WHERE uid='$uid'";
    $result = $db->query($query)->fetch_array(MYSQLI_ASSOC)["uid"];

    if (!empty($result) && $uid !== null && $uid !== "") {
        return true;
    } else {
        return false;
    }
}

// Generate <option> tags for Account Location input field
function fetchLocationsHtml()
{
    $countries = $GLOBALS["countries"];
    $final = "<option value='' >I'd Rather not Say</option>\n";

    foreach ($countries as $country) {
        $final .= "<option value='$country' >" . $country . "</option>\n";
    }

    return $final;
}

// Verification Code Algorithm
function generateVerificationCode()
{
    // Removed SendGrid so this is going to be hardcoded for demonstration purposess
    return 69420;
}

// Returns User Information
function getUserData($column, $condition)
{
    $db = $GLOBALS["db"];
    $query = $db->query("SELECT $column FROM users WHERE $condition");

    if ($query) {
        $row = $query->fetch_array(MYSQLI_ASSOC);
        return $row[$column];
    } else {
        return false;
    }
}

// Change Users in DB
function updateUser($column, $value, $condition)
{
    $db = $GLOBALS["db"];
    $query = $db->query("UPDATE users SET $column = '$value' WHERE $condition");

    return $query;
}

// Calculate Votes
function calcVotes($votingData)
{
    $votes = explode(":", $votingData);
    $voteCount = 1;

    if (count($votes) > 0) {
        foreach ($votes as $vote) {
            if (strpos($vote, "+") !== false) {
                $voteCount++;
            } else {
                $voteCount--;
            }
        }
    }

    return $voteCount;
}

// Prevent SQL Injection Attack
function escapeString($str)
{
    return str_replace("'", "\'", htmlspecialchars(strip_tags($str), ENT_QUOTES, "UTF-8"));
}

// Validate Location Boolean
function validLocation($country)
{
    $countries = $GLOBALS["countries"];

    return in_array($country, $countries);
}

// Validate that a file exists on Seperate Server
function validURL($url)
{
    if (!empty($url)) {
        $header_response = get_headers($url);

        if ($header_response) {
            if (strpos($header_response[0], "404") !== false) {
                return true;
            } else {
                return true;
            }
        } else {
            return false;
        }
    } else {
        return true;
    }
}

// Retrieve Data from a Comment
function getCommentData($column, $type, $condition)
{
    $db = $GLOBALS["db"];

    $finalCondition = "AND type='$type'";
    if ($type == "*") {
        $finalCondition = "";
    }

    $query = $db->query("SELECT $column FROM comments WHERE $condition $finalCondition");
    $array = $query->fetch_array(MYSQLI_ASSOC);

    return $array[$column];
}

// Set a Random Default Profile Picture to User
function randomProfileImage()
{
    $list = json_decode(file_get_contents("../json/profile-images.json"), true);
    $image = $list[rand(0, count($list) - 1)];

    return $image;
}

// Validate Sessions
function validateSession($session)
{
    if (isset($session) && $session !== null && $session !== "") {
        return true;
    } else {
        return false;
    }
}

function htmlFormat($string)
{
    $str = html_entity_decode(htmlspecialchars_decode($string));
    $str = str_replace("&#039;", "'", $str);

    return $str;
}

function parseBool($str)
{
    if (strtolower($str) == "true") {
        return true;
    } else {
        return false;
    }
}

function currentDate()
{
    return date("Y-m-d");
}

// Random Owner Selection
function selectRandomOwner($excludeUID, $mods, $members)
{
    $modsLen = count($mods);
    $newOwner = null;

    if ($modsLen >= 2) {
        // Select Random Moderator
        $modList = $mods;
        $modIndex = array_search($excludeUID, $modList);

        if ($modIndex !== null && $modIndex !== "") {
            unset($modList[$modIndex]);
        }

        $newOwner = $modList[mt_rand(0, count($modList))];
    } else {
        // Select Random Member
        $memberList = $members;
        $memberIndex = array_search($excludeUID, $memberList);

        if ($memberIndex == null || $memberIndex == "") {
            unset($memberList[$memberIndex]);
        }

        $newOwner = $memberList[mt_rand(0, count($memberList))];
    }

    // Return new Owner UID
    return $newOwner;
}
