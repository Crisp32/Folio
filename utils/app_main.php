<?php
/**
 * Folio Main PHP File
 * @author Connell Reffo
 */

// Includes
include_once "PHPDebugger/PHPDebugger.php";
include_once "classes.php";
 
// Public Constants
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

// List of Possible Countries
$countries = [
    "Canada",
    "Costa Rica",
    "Cuba",
    "Mexico",
    "United States"
];

// Get Forum Ids By Name
function getForumIdByName($db, $forumName) {
    $query = "SELECT fid FROM forums WHERE name='$forumName'";
    $FID = $db->query($query)->fetchArray()["fid"];

    return $FID;
}

// Get all Forum Data
function getForumDataById($db, $forumId) {
    $query = "SELECT * FROM forums WHERE fid='$forumId'";
    $forumData = $db->query($query);

    if ($forumData) {
        $forumArray = $forumData->fetchArray();

        // Create Forum Instance
        $forum = new Forum($db, $forumArray["owner"], $forumArray["name"], $forumArray["iconPath"], $forumArray["description"]);
        $forum->FID = $forumId;
        $forum->date = $forumArray["date"];

        return $forum;
    }
    else {
        return false;
    }
}

// Check if a Forum Exists
function forumExists($db, $forumName) {
    $query = "SELECT name FROM forums WHERE name='$forumName'";
    $result = $db->query($query)->fetchArray()["name"];

    if (!empty($result)) {
        return true;
    }
    else {
        return false;
    }
}

// Check if User Exists
function userExists($db, $uid) {
    $query = "SELECT uid FROM users WHERE uid='$uid'";
    $result = $db->query($query)->fetchArray()["uid"];

    if (!empty($result) && $uid !== null && $uid !== "") {
        return true;
    }
    else {
        return false;
    }
}

// Generate <option> tags for Account Location input field
function fetchLocationsHtml() {
    $countries = $GLOBALS["countries"];
    $final = "<option value='' >I'd Rather not Say</option>\n";

    foreach ($countries as $country) {
        $final .= "<option value='$country' >" . $country . "</option>\n";
    }

    return $final;
}

function initPHPMailer($mail, $sendTo) {
    // SMTP Settings
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "foliowebapp@gmail.com";
    $mail->Password = "phpapp2328";
    $mail->Port = 465;
    $mail->SMTPSecure = "ssl";

    // Email Settings
    $mail->isHTML(true);
    $mail->setFrom($sendTo, "Folio");
    $mail->addAddress($sendTo);
}

// Verification Code Algorithm
function generateVerificationCode() {
    return strtoupper(substr(md5(strval(rand(0, 200))), 0, 8));
}

// Returns User Information
function getUserData(SQLite3 $db, $column, $condition) {
    $query = $db->query("SELECT $column FROM users WHERE $condition");
    $array = $query->fetchArray();

    return $array[$column];
}

// Change Users in DB
function updateUser(SQLite3 $db, $column, $value, $condition) {
    $query = $db->query("UPDATE users SET $column = '$value' WHERE $condition");
    
    return $query;
}

// Calculate Votes
function calcVotes($votingData) {
    $votes = explode(":", $votingData);
    $voteCount = 1;

    if (count($votes) > 0) {
        foreach ($votes as $vote) {
            if (strpos($vote, "+") !== false) {
                $voteCount++;
            }
            else {
                $voteCount--;
            }
        }
    }
    
    return $voteCount;
}

// Prevent SQL Injection Attack
function escapeString($str) {
    return str_replace("'", "\'", htmlspecialchars(strip_tags($str), ENT_QUOTES, "UTF-8"));
}

// Validate Location Boolean
function validLocation($country) {
    $countries = $GLOBALS["countries"];

    return in_array($country, $countries);
}

// Validate that a file exists on Seperate Server
function validURL($url) {
    if (!empty($url)) {
        $header_response = get_headers($url);

        if ($header_response) {
            if (strpos($header_response[0], "404") !== false){
                return true;
            }
            else {
                return true;
            }
        }
        else {
            return false;
        }
    }
    else {
        return true;
    }
}

// Retrieve Data from a Comment
function getCommentData(SQLite3 $db, $column, $type, $condition) {
    $finalCondition = "AND type='$type'";
    if ($type == "*") { $finalCondition = ""; }
    
    $query = $db->query("SELECT $column FROM comments WHERE $condition $finalCondition");
    $array = $query->fetchArray();
    
    return $array[$column];
}

// Set a Random Default Profile Picture to User
function randomProfileImage() {
    $list = json_decode(file_get_contents("../json/profile-images.json"), true);
    $image = $list[rand(0, count($list) - 1)];

    return $image;
}

// Validate Sessions
function validateSession($session) {
    if (isset($session) && $session !== null && $session !== "") {
        return true;
    }
    else {
        return false;
    }
}

// Remove Key From Array
function removeFromArray($array, $key) {
    unset($array[array_search(strval($key), $array)]);
}

function htmlFormat($string) {
    $str = html_entity_decode(htmlspecialchars_decode($string));
    $str = str_replace("&#039;", "'", $str);

    return $str;
}

?>