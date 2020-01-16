<?php
/**
 * Folio Search Result Handler
 * Connell Reffo 2019
 */

include_once "app_main.php";

$searchTerm = escapeString($_REQUEST["term"]);
$arrayFinal = [];

// Init DB
$db = db();

if (!empty($searchTerm)) {
    $filter = $_REQUEST["filter"];
    
    // Get Users
    if ($filter == $FILTER_ALL || $filter == $FILTER_USERS) {
        $queryUsers = $db->query("SELECT username, profileImagePath FROM users WHERE username LIKE '%$searchTerm%' LIMIT 20");
        
        while ($res = $queryUsers->fetch_array(MYSQLI_ASSOC)) {
            array_push($arrayFinal, [
                    "name" => $res["username"],
                    "profileImage" => $res["profileImagePath"],
                    "type" => "user"
                ]
            );
        }
    }
    
    // Get Forums
    if ($filter == $FILTER_ALL || $filter == $FILTER_FORUMS) {
        $queryForums = $db->query("SELECT name, iconPath FROM forums WHERE name LIKE '%$searchTerm%' LIMIT 20");

        while ($res = $queryForums->fetch_array(MYSQLI_ASSOC)) {
            array_push($arrayFinal, [
                    "name" => $res["name"],
                    "profileImage" => $res["iconPath"],
                    "type" => "forum"
                ]
            );
        }
    }
}

// Return Results to Client
echo json_encode($arrayFinal);

?>