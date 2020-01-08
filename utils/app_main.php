<?php
/**
 * Folio Main PHP File
 * Connell Reffo 2019
 */

// Public Constants
$TYPE_PROFILE = "profile";
$TYPE_FORUMPOST = "forumpost";

// Classes
class Forum {
    public $ownerUID;
    public $name;
    public $iconURL;
    public $description;
    public $database;

    public $FID; // Corresponds to the FID in DB
    public $date;

    public function __construct(SQLite3 $database, $ownerUID, $name, $iconURL, $description) {
        $this->database = &$database;
        $this->ownerUID = &$ownerUID;
        $this->name = &$name;
        $this->iconURL = &$iconURL;
        $this->description = &$description;
    }

    public function create() {
        $db = $this->database;
        $ownerUID = $this->ownerUID;
        $name = $this->name;
        $iconURL = $this->iconURL;
        $description = $this->description;

        $date = date("j-n-Y");
        $insertStatement = "INSERT INTO forums(owner, name, iconPath, description, date) VALUES ('$ownerUID', '$name', '$iconURL', '$description', '$date')";

        return $db->query($insertStatement);
    }

    public function addMember($uid) {
        $FID = $this->FID;

        if ($FID !== null && $FID !== "") {

            // Get Forum Data from DB
            $db = $this->database;
            $query = "SELECT members FROM forums WHERE fid='$FID'";
            $membersQuery = $db->query($query);

            if ($membersQuery) {
                $members = $membersQuery->fetchArray()["members"];
                $members = ":$uid" . $members;

                $insertStatement = "UPDATE forums SET members='$members' WHERE fid='$FID'";

                if ($db->query($insertStatement)) {
                    // Add Newly Created Forum to User's Joined Forums List
                    $user = new User($db);
                    $user->getUserDataByUID($uid);
                    $joinedForums = ":$FID" . $user->user["joinedForums"];

                    return $user->update("joinedForums", $joinedForums);
                }
                else {
                    return false;
                }
            }
            else {
                return false;
            }
        }
        else {
            throw new Exception("Property FID is not Assigned");
        }
    }

    public function hasMember($uid) {
        $forumId = $this->FID;

        // Check if Forum ID is Assigned
        if (isset($forumId)) {
            if ($uid !== null && $uid !== "") {
                $db = $this->database;
                
                // Get String of Members
                $selectQuery = $db->query("SELECT members FROM forums WHERE fid='$forumId'");
                $members = $selectQuery->fetchArray()["members"];

                // Return Boolean
                return (strpos($members, ":$uid") !== false);
            }
            else {
                return false;
            }
        }
        else {
            throw new Exception("Property FID is not Assigned");
        }
    }

    public function getMembers() {
        $forumId = $this->FID;

        // Check if Forum ID is Assigned
        if (isset($forumId)) {
            $db = $this->database;
                
            // Get String of Members
            $selectQuery = $db->query("SELECT members FROM forums WHERE fid='$forumId'");
            $members = $selectQuery->fetchArray()["members"];

            // Return Array of Members' UID
            return explode(":", $members);
        }
        else {
            throw new Exception("Property FID is not Assigned");
        }
    }

    public function isModerator($uid) {
        $forumId = $this->FID;

        // Check if Forum ID is Assigned
        if (isset($forumId)) {
            if ($uid !== null && $uid !== "") {
                $db = $this->database;
                
                // Get String of Moderators
                $selectQuery = $db->query("SELECT mods FROM forums WHERE fid='$forumId'");
                $mods = $selectQuery->fetchArray()["mods"];

                // Return Boolean
                return (strpos($mods, ":$uid") !== false || $uid == $this->ownerUID);
            }
            else {
                return false;
            }
        }
        else {
            throw new Exception("Property FID is not Assigned");
        }
    }
}

class User {
    public $user;
    private $database;

    public function __construct(SQLite3 $database) {
        $this->database = &$database;
    }
    
    public function getUserDataByName($username) {
        $db = $this->database;

        $query = "SELECT * FROM users WHERE username='$username'";
        $userData = $db->query($query)->fetchArray();

        $this->user = $userData;
    }

    public function getUserDataByUID($UID) {
        $db = $this->database;

        $query = "SELECT * FROM users WHERE uid='$UID'";
        $userData = $db->query($query)->fetchArray();

        $this->user = $userData;
    }

    public function update($column, $value) {
        $db = $this->database;
        $UID = $this->user["uid"];
        $query = $db->query("UPDATE users SET $column = '$value' WHERE uid='$UID'");
        
        return $query;
    }
    
}

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

?>