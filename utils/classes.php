<?php
/**
 * Folio Main Class File
 * @author Connell Reffo
 */

include_once "PHPDebugger/PHPDebugger.php";
include_once "database.php";

// Init DB
$db = db();

class Forum {
    public $ownerUID;
    public $name;
    public $iconURL;
    public $description;

    public $FID; // Corresponds to the FID in DB
    public $date;

    public function __construct($ownerUID, $name, $iconURL, $description) {
        $this->ownerUID = &$ownerUID;
        $this->name = &$name;
        $this->iconURL = &$iconURL;
        $this->description = &$description;
    }

    public function create() {
        $ownerUID = $this->ownerUID;
        $name = $this->name;
        $iconURL = $this->iconURL;
        $description = $this->description;
        $db = $GLOBALS["db"];

        $date = date("j-n-Y");
        $insertStatement = "INSERT INTO forums(owner, name, iconPath, description, date, members, mods, bans) VALUES ('$ownerUID', '$name', '$iconURL', '$description', '$date', '[]', '[]', '[]')";

        return $db->query($insertStatement);
    }

    public function addMember($uid) {
        $FID = $this->FID;

        if ($FID !== null && $FID !== "") {
            $db = $GLOBALS["db"];

            $forumMembers = $db->query("SELECT members FROM forums WHERE fid='$FID'")->fetch_array(MYSQLI_ASSOC)["members"];
            $joinedForums = $db->query("SELECT joinedForums FROM users WHERE uid='$uid'")->fetch_array(MYSQLI_ASSOC)["joinedForums"];

            // Modify Values in Database
            $updateForum = "UPDATE forums SET members=JSON_ARRAY_INSERT('$forumMembers', '$[0]', $uid) WHERE fid='$FID';";
            $updateUser = "UPDATE users SET joinedForums=JSON_ARRAY_INSERT('$joinedForums', '$[0]', $FID) WHERE uid='$uid';";
            $result = $db->multi_query($updateForum . $updateUser);

            return $result;
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
                
                // Get List of Members
                $members = $this->getMembers();

                // Return Boolean
                return in_array($uid, $members);
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function delete() {
        $forumId = $this->FID;

        if (isset($forumId)) {
            $deleteQuery = $db->query("DELETE FROM forums WHERE fid='$forumId'");

            return $deleteQuery;
        }
        else {
            throw new Exception("Property FID is not Assigned");
        }
    }

    public function removeMember($uid) {
        $forumId = $this->FID;

        if (isset($forumId)) {
                
            // Get Array of Members
            $members = $this->getMembers();
            unset($members[array_search(strval($uid), $members)]);
            
            // Generate new String
            $membersStr = implode(":", $members);

            // Update DB
            $this->update("members", $membersStr);

            // Demote User
            if ($this->isModerator($uid)) {
                $this->demote($uid);
            }

            // Remove Forum from User's list of Joined Forums
            $userInstance = new User();
            $userInstance->getUserDataByUID($uid);
            $joinedForums = str_replace(":$forumId", "", $userInstance->user["joinedForums"]);
            $userInstance->update("joinedForums", $joinedForums);

            // Select new Owner if Owner is Leaving
            if ($uid == $this->ownerUID) {
                $this->selectRandomOwner();
            }

            // Delete Forum if no Users are Left
            if (count($this->getMembers()) == 1) {
                $this->delete();
            }

            return true;
        }
        else {
            throw new Exception("Property FID is not Assigned");
        }
    }

    public function unban($uid) {
        $forumId = $this->FID;

        if (isset($forumId)) {
                
            // Get Array of Banned Members
            $bans = $this->getBannedMembers();
            unset($bans[array_search(strval($uid), $bans)]);
            
            // Generate new String
            $bansStr = implode(":", $bans);

            // Update DB
            return $this->update("bans", $bansStr);
        }
        else {
            throw new Exception("Property FID is not Assigned");
        }
    }

    public function getMembers() {
        $forumId = $this->FID;

        // Check if Forum ID is Assigned
        if (isset($forumId)) {
            $db = $GLOBALS["db"];
                
            // Get String of Members    
            $selectQuery = $db->query("SELECT members FROM forums WHERE fid='$forumId'");
            $members = $selectQuery->fetch_array(MYSQLI_ASSOC)["members"];

            // Return Array of Members' UID
            if (!empty($members)) {
                return json_decode($members);
            }
            else {
                return [];
            }
        }
        else {
            throw new Exception("Property FID is not Assigned");
        }
    }

    public function getModerators() {
        $forumId = $this->FID;

        // Check if Forum ID is Assigned
        if (isset($forumId)) {
            $db = $GLOBALS["db"];

            // Get String of Moderators
            $selectQuery = $db->query("SELECT mods FROM forums WHERE fid='$forumId'");
            $mods = $selectQuery->fetch_array(MYSQLI_ASSOC)["mods"];

            // Return Array of Moderators' UID
            if (!empty($mods)) {
                $modsArr = json_decode($mods);
                return $modsArr;
            }
            else {
                return [];
            }
        }
        else {
            return false;
        }
    }

    public function isModerator($uid) {
        $forumId = $this->FID;

        // Check if Forum ID is Assigned
        if (isset($forumId)) {
            if ($uid !== null && $uid !== "") {
                
                // Get String of Moderators
                $mods = $this->getModerators();

                // Return Boolean
                $bool = (in_array($uid, $mods) || $uid == $this->ownerUID);
                return $bool;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function selectRandomOwner() {
        $forumId = $this->FID;

        if (isset($forumId)) {
            $mods = $this->getModerators();
            $modsLen = count($mods);
            $newOwner;

            if ($modsLen >= 2) {
                // Select Random Moderator
                $newOwner = $mods[mt_rand(0, $modsLen - 1)];
            }
            else {
                // Select Random Member
                $members = $this->getMembers();
                $newOwner = $members[mt_rand(0, count($members) - 1)];
            }

            // Update Forum in DB
            if ($newOwner !== "" && $newOwner !== null) {
                $updateQuery = $db->query("UPDATE forums SET owner='$newOwner' WHERE fid='$forumId'");
                return $updateQuery;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function getBannedMembers() {
        $forumId = $this->FID;

        // Check if Forum ID is Assigned
        if (isset($forumId)) {
            $db = $GLOBALS["db"];

            // Get String of Moderators
            $selectQuery = $db->query("SELECT bans FROM forums WHERE fid='$forumId'");
            $bans = $selectQuery->fetch_array(MYSQLI_ASSOC)["bans"];

            // Return Array of Banned Members' UID
            if (!empty($bans)) {
                return json_decode($bans);
            }
            else {
                return [];
            }
        }
        else {
            return false;
        }
    }

    public function isBanned($uid) {
        if ($uid !== null && $uid !== "") {
            $bans = $this->getBannedMembers();
            return in_array($uid, $bans);
        }
        else {
            return false;
        }
    }

    public function banMember($uid) {
        $forumId = $this->FID;

        // Check if Forum ID is Assigned
        if (isset($forumId)) {

            // Check if User is Already Banned
            if (!$this->isBanned($uid)) {
                $selectQuery = $db->query("SELECT bans FROM forums WHERE fid='$forumId'");
                $bans = $selectQuery->fetch_array(MYSQLI_ASSOC)["bans"];

                // Update DB
                $bans .= ":$uid";
                $this->update("bans", $bans);
                $this->removeMember($uid);

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

    public function promote($uid) {
        $forumId = $this->FID;

        // Check if Forum ID is Assigned
        if (isset($forumId)) {

            // Check Current Rank
            if ($uid !== $this->ownerUID && !$this->isModerator($uid)) {

                // Get Array of Mods
                $mods = $this->getModerators();

                // Modify DB
                array_push($mods, strval($uid));
                $modsStr = implode(":", $mods);
                
                $this->update("mods", $modsStr);

                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function demote($uid) {
        $forumId = $this->FID;

        // Check if Forum ID is Assigned
        if (isset($forumId)) {

            // Check Current Rank
            if ($uid !== $this->ownerUID && $this->isModerator($uid)) {

                // Get Array of Mods
                $mods = $this->getModerators();

                // Modify DB
                unset($mods[array_search(strval($uid), $mods)]);
                $modsStr = implode(":", $mods);
                
                $this->update("mods", $modsStr);

                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function addPost($title, $body, $userId, $forumId) {
        $date = date("j-n-Y");
        $insertStatement = "INSERT INTO forumPosts (fid, uid, title, body, voteCount, date) VALUES ('$forumId', '$userId', '$title', '$body', '0', '$date')";

        return $db->query($insertStatement);
    }

    public function update($column, $value) {
        $forumId = $this->FID;
        $query = $db->query("UPDATE forums SET $column='$value' WHERE fid='$forumId'");
        
        return $query;
    }
}

class User {
    public $user;
    
    public function getUserDataByName($username) {
        $db = $GLOBALS["db"];
        $query = "SELECT * FROM users WHERE username='$username'";
        $userData = $db->query($query)->fetch_array(MYSQLI_ASSOC);

        $this->user = $userData;
    }

    public function getUserDataByUID($UID) {
        $db = $GLOBALS["db"];
        $query = "SELECT * FROM users WHERE uid='$UID'";
        $userData = $db->query($query)->fetch_array(MYSQLI_ASSOC);

        $this->user = $userData;
    }

    public function getVotes() {
        $uid = $this->user["uid"];
        $db = $GLOBALS["db"];

        $selectQuery = "SELECT votes FROM users WHERE uid='$uid'";
        $selectResult = $db->query($selectQuery);

        if ($selectResult) {
            $encodedArr = $selectResult->fetch_array(MYSQLI_ASSOC)["votes"];
            return json_decode($encodedArr, true);
        }
        else {
            return false;
        }
    }

    public function upvotedBy($uid) {
        $votes = $this->getVotes();

        return in_array($uid, $votes["upvotes"]);
    }

    public function downvotedBy($uid) {
        $votes = $this->getVotes();

        return in_array($uid, $votes["downvotes"]);
    }

    public function upvote($voterId) {
        $votes = $this->getVotes();
        $uid = $this->user["uid"];
        $db = $GLOBALS["db"];

        // Remove Downvote
        if (in_array($voterId, $votes["downvotes"])) {
            $voteIndex = array_search($voterId, $votes["downvotes"]);
            unset($votes["downvotes"][$voteIndex]);
        }

        // Add Upvote
        if (!in_array($voterId, $votes["upvotes"])) {
            array_push($votes["upvotes"], intval($voterId));
        }

        $count = count($votes["upvotes"]) - count($votes["downvotes"]);
        $encodedVotes = json_encode($votes);
        $updateQuery = "UPDATE users SET voteCount=$count, votes='$encodedVotes' WHERE uid='$uid'";

        if ($db->query($updateQuery)) {
            return [
                "success" => true,
                "count" => $count
            ];
        }
        else return [
            "success" => false
        ];
    }

    public function downvote($voterId) {
        $votes = $this->getVotes();
        $uid = $this->user["uid"];
        $db = $GLOBALS["db"];

        // Remove Upvote
        if (in_array($voterId, $votes["upvotes"])) {
            $voteIndex = array_search($voterId, $votes["upvotes"]);
            unset($votes["upvotes"][$voteIndex]);
        }

        // Add Downvote
        if (!in_array($voterId, $votes["downvotes"])) {
            array_push($votes["downvotes"], intval($voterId));
        }

        $count = count($votes["upvotes"]) - count($votes["downvotes"]);
        $encodedVotes = json_encode($votes);
        $updateQuery = "UPDATE users SET voteCount=$count, votes='$encodedVotes' WHERE uid='$uid'";

        if ($db->query($updateQuery)) {
            return [
                "success" => true,
                "count" => $count
            ];
        }
        else return [
            "success" => false
        ];
    }

    public function removeVote($voterId) {
        $votes = $this->getVotes();
        $uid = $this->user["uid"];
        $db = $GLOBALS["db"];

        // Remove Downvote
        if (in_array($voterId, $votes["downvotes"])) {
            $voteIndex = array_search($voterId, $votes["downvotes"]);
            unset($votes["downvotes"][$voteIndex]);
        }

        // Remove Upvote
        if (in_array($voterId, $votes["upvotes"])) {
            $voteIndex = array_search($voterId, $votes["upvotes"]);
            unset($votes["upvotes"][$voteIndex]);
        }

        $count = count($votes["upvotes"]) - count($votes["downvotes"]);
        $encodedVotes = json_encode($votes);
        $updateQuery = "UPDATE users SET voteCount=$count, votes='$encodedVotes' WHERE uid='$uid'";

        if ($db->query($updateQuery)) {
            return [
                "success" => true,
                "count" => $count
            ];
        }
        else return [
            "success" => false
        ];
    }

    public function update($column, $value) {
        $db = $GLOBALS["db"];
        $UID = $this->user["uid"];
        $query = $db->query("UPDATE users SET $column='$value' WHERE uid='$UID'");
        
        return $query;
    }
}