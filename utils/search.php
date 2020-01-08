<?php
/**
 * Folio Search Result Handler
 * Connell Reffo 2019
 */

include_once "app_main.php";

$searchTerm = escapeString($_REQUEST["term"]);

// Init DB
$db = new SQLite3("../db/folio.db");

// Generate Results
$queryUsers = $db->query("SELECT username, profileImagePath FROM users WHERE username LIKE '%$searchTerm%'");
$queryForums = $db->query("SELECT name, iconPath FROM forums WHERE name LIKE '%$searchTerm%'");

$arrayFinal = [];

// Get Users
while ($res = $queryUsers->fetchArray(SQLITE3_ASSOC)) {
    array_push($arrayFinal, [
            "name" => $res["username"],
            "profileImage" => $res["profileImagePath"],
            "type" => "user"
        ]
    );
}

// Get Forums
while ($res = $queryForums->fetchArray(SQLITE3_ASSOC)) {
    array_push($arrayFinal, [
            "name" => $res["name"],
            "profileImage" => $res["iconPath"],
            "type" => "forum"
        ]
    );
}

// Return Results to Client
echo json_encode($arrayFinal);

?>