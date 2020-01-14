<?php
/**
 * Folio Main Class File
 * @author Connell Reffo
 */

include_once "PHPDebugger/PHPDebugger.php";

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
                $members = $this->getMembers();

                // Return Boolean
                return in_array(strval($uid), $members);
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
            $db = $this->database;
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
            $db = $this->database;
                
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
            $userInstance = new User($db);
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
            $db = $this->database;
                
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
            $db = $this->database;
                
            // Get String of Members    
            $selectQuery = $db->query("SELECT members FROM forums WHERE fid='$forumId'");
            $members = $selectQuery->fetchArray()["members"];

            // Return Array of Members' UID
            if (!empty($members)) {
                return explode(":", $members);
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
            $db = $this->database;

            // Get String of Moderators
            $selectQuery = $db->query("SELECT mods FROM forums WHERE fid='$forumId'");
            $mods = $selectQuery->fetchArray()["mods"];

            // Return Array of Moderators' UID
            if (!empty($mods)) {
                $modsArr = explode(":", $mods);
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
                $bool = (in_array(strval($uid), $mods) || $uid == $this->ownerUID);
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
            $db = $this->database;
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
            $db = $this->database;

            // Get String of Moderators
            $selectQuery = $db->query("SELECT bans FROM forums WHERE fid='$forumId'");
            $bans = $selectQuery->fetchArray()["bans"];

            // Return Array of Banned Members' UID
            if (!empty($bans)) {
                return explode(":", $bans);
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
            return in_array(strval($uid), $bans);
        }
        else {
            return false;
        }
    }

    public function banMember($uid) {
        $forumId = $this->FID;

        // Check if Forum ID is Assigned
        if (isset($forumId)) {
            $db = $this->database;

            // Check if User is Already Banned
            if (!$this->isBanned($uid)) {
                $selectQuery = $db->query("SELECT bans FROM forums WHERE fid='$forumId'");
                $bans = $selectQuery->fetchArray()["bans"];

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
            $db = $this->database;

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
            $db = $this->database;

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
        $db = $this->database;
        $date = date("j-n-Y");
        $insertStatement = "INSERT INTO forumPosts (fid, uid, title, body, voteCount, date) VALUES ('$forumId', '$userId', '$title', '$body', '0', '$date')";

        return $db->query($insertStatement);
    }

    public function update($column, $value) {
        $db = $this->database;
        $forumId = $this->FID;
        $query = $db->query("UPDATE forums SET $column='$value' WHERE fid='$forumId'");
        
        return $query;
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

    public function getVotes() {
        $user = $this->user;
        $votes = [
            "upvotes" => [],
            "downvotes" => []
        ];

        $votesStr = substr($user["votes"], 1);
        $votesArray = explode(":", $votesStr);

        if (!empty($votesArray)) {
            // Process Each Vote
            foreach ($votesArray as $vote) {
                if (strpos($vote, "+") !== false) { // Upvote
                    $uid = str_replace("+", "", $vote);
                    array_push($votes["upvotes"], strval($uid));
                }
                else if (strpos($vote, "-") !== false) { // Downvote
                    $uid = str_replace("-", "", $vote);
                    array_push($votes["downvotes"], strval($uid));
                }
            }
        }

        // Return Final Array
        return $votes;
    }

    public function hasVoted($uid) {
        $user = $this->user;
        $votes = $this->getVotes();

        if (in_array(strval($uid), $votes["upvotes"]) || in_array(strval($uid), $votes["downvotes"])) {
            return true;
        }
        else {
            return false;
        }
    }

    public function upvotedBy($uid) {
        $user = $this->user;
        $votes = $this->getVotes();

        return in_array(strval($uid), $votes["upvotes"]);
    }

    public function downvotedBy($uid) {
        $user = $this->user;
        $votes = $this->getVotes();

        return in_array(strval($uid), $votes["downvotes"]);
    }
    
    public function getVoteCountFromArray($votesArray) {
        $votes = $votesArray;
        $count = 0;
        
        foreach ($votes["upvotes"] as $upvote) {
            if ($upvote !== null && $upvote !== "") {
                $count++;
            }
        }

        foreach ($votes["downvotes"] as $downvote) {
            if ($downvote !== null && $downvote !== "") {
                $count--;
            }
        }

        return $count;
    }

    public function getVoteCountFromDB() {
        $votes = $this->getVotes();
        return $this->getVoteCountFromArray($votes);
    }

    public function voteArrayToStr($votesArray) {
        $upvotes = $votesArray["upvotes"];
        $downvotes = $votesArray["downvotes"];
        $finalStr = "";

        // Process Upvotes
        foreach ($upvotes as $vote) {
            if ($vote !== null && $vote !== "") {
                $finalStr .= ":+$vote";
            }
        }

        // Process Downvotes
        foreach ($downvotes as $vote) {
            if ($vote !== null && $vote !== "") {
                $finalStr .= ":-$vote";
            }
        }

        return $finalStr;
    }

    public function upvote($voterId) {
        if (!$this->upvotedBy($voterId)) {
            $user = $this->user;
            $votesStr = $user["votes"];
            $count = intval($this->getVoteCountFromDB()) + 1;

            if ($this->downvotedBy($voterId)) {
                $votesStr = str_replace(":-$voterId", ":+$voterId", $votesStr);
                $count++;
            }
            else {
                $votesStr .= ":+$voterId";
            }

            // Return Array
            $dbSuccess = ($this->update("votes", $votesStr) && $this->update("voteCount", $count));
            return [
                "success" => $dbSuccess,
                "count" => $count
            ];
        }
        else {
            return false;
        }
    }

    public function downvote($voterId) {
        if (!$this->downvotedBy($voterId)) {
            $user = $this->user;
            $votesStr = $user["votes"];
            $count = intval($this->getVoteCountFromDB()) - 1;

            if ($this->upvotedBy($voterId)) {
                $votesStr = str_replace(":+$voterId", ":-$voterId", $votesStr);
                $count--;
            }
            else {
                $votesStr .= ":-$voterId";
            }

            // Return Array
            $dbSuccess = ($this->update("votes", $votesStr) && $this->update("voteCount", $count));
            return [
                "success" => $dbSuccess,
                "count" => $count
            ];
        }
        else {
            return false;
        }
    }

    public function removeVote($voterId) {
        if ($this->hasVoted($voterId)) {
            $user = $this->user;
            $votesStr = $user["votes"];
            $count = intval($this->getVoteCountFromDB());

            if ($this->upvotedBy($voterId)) {
                $votesStr = str_replace(":+$voterId", "", $votesStr);
                $count--;
            }
            else if ($this->downvotedBy($voterId)) {
                $votesStr = str_replace(":-$voterId", "", $votesStr);
                $count++;
            }

            // Return Array
            $dbSuccess = ($this->update("votes", $votesStr) && $this->update("voteCount", $count));
            return [
                "success" => $dbSuccess,
                "count" => $count
            ];
        }
        else {
            return true;
        }
    }

    public function update($column, $value) {
        $db = $this->database;
        $UID = $this->user["uid"];
        $query = $db->query("UPDATE users SET $column='$value' WHERE uid='$UID'");
        
        return $query;
    }
}

class Database extends SQLite3 {
    
}