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

        $date = currentDate();
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
            $db = $GLOBALS["db"];
            $deleteQuery = $db->multi_query("DELETE FROM forums WHERE fid=$forumId; DELETE FROM comments WHERE type='forumpost' AND uid IN (SELECT pid FROM forumPosts WHERE fid=$forumId); DELETE FROM forumPosts WHERE fid=$forumId;");

            return $deleteQuery;
        }
        else {
            throw new Exception("Property FID is not Assigned");
        }
    }

    public function removeMember($uid) {
        $forumId = $this->FID;

        if (isset($forumId)) {
            $db = $GLOBALS["db"];

            // Get Array of Members
            $membersOrig = $this->getMembers();
            $members = $membersOrig;
            $memberIndex = array_search($uid, $members);
            unset($members[$memberIndex]);

            // Remove From Joined Forums
            $joinedForumsEncoded = $db->query("SELECT joinedForums FROM users WHERE uid='$uid'")->fetch_array(MYSQLI_ASSOC)["joinedForums"];
            $joinedForums = json_decode($joinedForumsEncoded, true);
            $forumIndex = array_search($forumId, $joinedForums);

            $removeJoinedForumQuery = "UPDATE users SET joinedForums=JSON_REMOVE('$joinedForumsEncoded', '$[$forumIndex]') WHERE uid='$uid'";

            if (count($members) == 0) {

                // Delete Forum
                return [
                    "success" => $db->query($removeJoinedForumQuery) && $this->delete(),
                    "doReload" => true
                ];
            }
            else {

                // Demote
                $oldOwner = $this->ownerUID;
                $newOwner = $oldOwner;

                if ($this->isModerator($uid)) {
                    if ($this->ownerUID == $uid) {

                        // Promote Random Mod (or Member)
                        $newOwner = selectRandomOwner($uid, $this->getModerators(), $membersOrig);
                        
                        if ($this->isModerator($newOwner)) {
                            $this->demote($newOwner); // Remove Moderator Rank
                        }

                        Notification::push($newOwner, "You are the new owner of: <strong>$this->name</strong>", "[@$oldOwner has Left]");
                    }

                    $this->demote($uid);
                }

                // Update Database
                $membersStr = $db->query("SELECT members FROM forums WHERE fid='$forumId'")->fetch_array(MYSQLI_ASSOC)["members"];

                return [
                    "success" => $db->multi_query("UPDATE forums SET owner='$newOwner', members=JSON_REMOVE('$membersStr', '$[$memberIndex]') WHERE fid='$forumId';" . $removeJoinedForumQuery),
                    "doReload" => false
                ];
            }
        }
        else {
            throw new Exception("Property FID is not Assigned");
        }
    }

    public function unban($uid) {
        $forumId = $this->FID;

        if (isset($forumId)) {
            $db = $GLOBALS["db"];    

            // Get Array of Banned Members
            $bans = $this->getBannedMembers();
            $banIndex = array_search($uid, $bans);
            
            // Update Database
            $bansEncoded = json_encode($bans);
            $unbanQuery = "UPDATE forums SET bans=JSON_REMOVE('$bansEncoded', '$[$banIndex]') WHERE fid='$forumId'";

            return $db->query($unbanQuery);
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
                return json_decode($members, true);
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
                $modsArr = json_decode($mods, true);
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
                return json_decode($bans, true);
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
            $db = $GLOBALS["db"];

            // Check if User is Already Banned
            if (!$this->isBanned($uid)) {
                $selectQuery = $db->query("SELECT bans FROM forums WHERE fid='$forumId'");
                $bans = $selectQuery->fetch_array(MYSQLI_ASSOC)["bans"];

                // Update DB
                return $db->query("UPDATE forums SET bans=JSON_ARRAY_INSERT('$bans', '$[0]', $uid) WHERE fid='$forumId'") && $this->removeMember($uid)["success"];
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
                $db = $GLOBALS["db"];

                // Get Array of Mods
                $mods = $this->getModerators();
                $modsEncoded = json_encode($mods);

                $addModQuery = "UPDATE forums SET mods=JSON_ARRAY_INSERT('$modsEncoded', '$[0]', $uid) WHERE fid='$forumId'";
                return $db->query($addModQuery);
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
                $db = $GLOBALS["db"];

                // Get Array of Mods
                $mods = $this->getModerators();
                $modIndex = array_search($uid, $mods);
                $modsEncoded = json_encode($mods);

                $addModQuery = "UPDATE forums SET mods=JSON_REMOVE('$modsEncoded', '$[$modIndex]') WHERE fid='$forumId'";
                return $db->query($addModQuery);
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
        $db = $GLOBALS["db"];
        $date = currentDate();
        $votes = json_encode([
            "upvotes" => [],
            "downvotes" => []
        ]);
        $insertStatement = "INSERT INTO forumPosts (fid, uid, title, body, voteCount, date, votes, commentCount) VALUES ('$forumId', '$userId', '$title', '$body', '0', '$date', '$votes', '0')";

        return [
            "success" => $db->query($insertStatement),
            "pid" => $db->insert_id
        ];
    }

    public function getMemberCount() {
        $db = $GLOBALS["db"];
        $forumId = $this->FID;

        $membersQuery = $db->query("SELECT members FROM forums WHERE fid='$forumId");
        $members = json_decode($membersQuery->fetch_array(MYSQLI_ASSOC)["members"], true);

        $count = count($members);
        return $count;
    }

    public function update($column, $value) {
        $db = $GLOBALS["db"];
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

    public function deleteAccount() {
        $db = $GLOBALS["db"];
        $uid = $this->user["uid"];
        $joinedForums = $db->query("SELECT joinedForums FROM users WHERE uid=$uid");
        
        if ($joinedForums) {
            $forums = json_decode($joinedForums->fetch_array(MYSQLI_ASSOC)["joinedForums"], true);

            // Loop through each Forum
            foreach ($forums as $forum) {
                $assoc = $db->query("SELECT name, members, bans, mods, owner FROM forums WHERE fid=$forum")->fetch_array(MYSQLI_ASSOC);
                
                // Member List
                $membersEncoded = $assoc["members"];
                $memberList = json_decode($membersEncoded, true);
                $memberIndex = array_search($uid, $memberList);

                // Ban List
                $bansEncoded = $assoc["bans"];
                $banList = json_decode($bansEncoded, true);
                $banIndex = array_search($uid, $banList);

                // Moderator List
                $modsEncoded = $assoc["mods"];
                $modList = json_decode($modsEncoded, true);
                $modIndex = array_search($uid, $modList);

                $owner = selectRandomOwner($uid, $modList, $memberList);
                
                if (count($memberList) > 0) {
                    $db->query("UPDATE forums SET owner='$owner', members=JSON_REMOVE('$membersEncoded', '$[$memberIndex]'), mods=JSON_REMOVE('$modsEncoded', '$[$modIndex]'), bans=JSON_REMOVE('$bansEncoded', '$[$banIndex]') WHERE fid=$forum");
                }
                else {
                    $db->query("DELETE FROM forums WHERE fid=$forum");
                }
            }

            return $db->query("DELETE FROM users WHERE uid=$uid");
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
                "success" => true
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
                "success" => true
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

class ForumPost {
    public $post;

    public function getDataById($postId) {
        $db = $GLOBALS["db"];
        $selectQuery = $db->query("SELECT * FROM forumPosts WHERE pid=$postId");

        if ($selectQuery) {
            $result = $selectQuery->fetch_array(MYSQLI_ASSOC);
            $this->post = $result;

            return true;
        }
        else {
            return false;
        }
    }

    public function getVotes() {
        $db = $GLOBALS["db"];
        $pid = $this->post["pid"];

        $selectQuery = $db->query("SELECT votes FROM forumPosts WHERE pid=$pid");

        if ($selectQuery) {
            $result = $selectQuery->fetch_array(MYSQLI_ASSOC)["votes"];
            return json_decode($result, true);
        }
        else {
            return false;
        }
    }

    public function getVoteCount() {
        $votes = $this->getVotes();
        return count($votes["upvotes"]) - count($votes["downvotes"]);
    }

    public function upvotedBy($uid) {
        $db = $GLOBALS["db"];
        $votes = $this->getVotes();

        return in_array($uid, $votes["upvotes"]);
    }

    public function downvotedBy($uid) {
        $db = $GLOBALS["db"];
        $votes = $this->getVotes();

        return in_array($uid, $votes["downvotes"]);
    }

    public function upvote($uid) {
        $votes = $this->getVotes();
        $db = $GLOBALS["db"];
        $upvoted = false;

        // Check if Already Upvoted
        if ($this->upvotedBy($uid)) {
            $voteIndex = array_search($uid, $votes["upvotes"]);
            unset($votes["upvotes"][$voteIndex]);
        }
        else {
            array_push($votes["upvotes"], intval($uid));
            $upvoted = true;
        }

        // Check if Downvoted
        if ($this->downvotedBy($uid)) {
            $voteIndex = array_search($uid, $votes["downvotes"]);
            unset($votes["downvotes"][$voteIndex]);
        }

        // Update Database
        $count = count($votes["upvotes"]) - count($votes["downvotes"]);
        $votesEncoded = json_encode($votes);

        $pid = $this->post["pid"];
        $updateQuery = $db->query("UPDATE forumPosts SET voteCount=$count, votes='$votesEncoded' WHERE pid=$pid");

        return [
            "success" => $updateQuery,
            "upvoted" => $upvoted
        ];
    }

    public function downvote($uid) {
        $votes = $this->getVotes();
        $db = $GLOBALS["db"];
        $downvoted = false;

        // Check if Already Downvoted
        if ($this->downvotedBy($uid)) {
            $voteIndex = array_search($uid, $votes["downvotes"]);
            unset($votes["downvotes"][$voteIndex]);
        }
        else {
            array_push($votes["downvotes"], intval($uid));
            $downvoted = true;
        }

        // Check if Upvoted
        if ($this->upvotedBy($uid)) {
            $voteIndex = array_search($uid, $votes["upvotes"]);
            unset($votes["upvotes"][$voteIndex]);
        }

        // Update Database
        $count = count($votes["upvotes"]) - count($votes["downvotes"]);
        $votesEncoded = json_encode($votes);

        $pid = $this->post["pid"];
        $updateQuery = $db->query("UPDATE forumPosts SET voteCount=$count, votes='$votesEncoded' WHERE pid=$pid");

        return [
            "success" => $updateQuery,
            "downvoted" => $downvoted
        ];
    }

    public function removeVotes($uid) {
        $votes = $this->getVotes();
        $db = $GLOBALS["db"];

        // Remove Upvote
        if ($this->upvotedBy($uid)) {
            $voteIndex = array_search($uid, $votes["upvotes"]);
            unset($votes["upvotes"][$voteIndex]);
        }

        // Remove Downvote
        if ($this->downvotedBy($uid)) {
            $voteIndex = array_search($uid, $votes["downvotes"]);
            unset($votes["downvotes"][$voteIndex]);
        }

        // Update Database
        $count = count($votes["upvotes"]) - count($votes["downvotes"]);
        $votesEncoded = json_encode($votes);

        $pid = $this->post["pid"];
        $updateQuery = $db->query("UPDATE forumPosts SET voteCount=$count, votes='$votesEncoded' WHERE pid=$pid");

        return [
            "success" => $updateQuery,
            "upvoted" => false,
            "downvoted" => false
        ];
    }
}

class Notification {

    public function push($uid, $body, $subMessage) {
        $maxNotifs = 60;

        if (Notification::getCount($uid) < $maxNotifs) {
            $db = $GLOBALS["db"];
            $date = currentDate();

            return $db->query("INSERT INTO notifications (uid, message, subMessage, date) VALUES ($uid, '$body', '$subMessage', '$date')");
        }
        else {
            return true;
        }
    }

    public function getCount($uid) {
        $db = $GLOBALS["db"];

        $count = $db->query("SELECT COUNT(*) AS 'count' FROM notifications WHERE uid=$uid");
        return intval($count->fetch_array(MYSQLI_ASSOC)["count"]);
    }

    public function delete($nid) {
        $db = $GLOBALS["db"];
        return $db->query("DELETE FROM notifications WHERE nid=$nid");
    }

    public function getAssoc($nid) {
        $db = $GLOBALS["db"];
        $query = $db->query("SELECT * FROM notifications WHERE nid=$nid");

        if ($query) {
            $notif = $query->fetch_array(MYSQLI_ASSOC);
            return $notif;
        }
        else {
            return false;
        }
    }
}