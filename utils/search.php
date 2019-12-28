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
$arrayUsers = [];

while ($res = $queryUsers->fetchArray(SQLITE3_ASSOC)) {
    
    $profImg = $res["profileImagePath"];
    if (empty($profImg)) {
        $profImg = "https://ui-avatars.com/api/?background=c9c9c9&color=131313&size=185&bold=true&font-size=0.35&length=3&name=" . $res["username"];
    }

    array_push($arrayUsers, [
            "name" => $res["username"],
            "profileImage" => $profImg,
            "type" => "user"
        ]
    );
}

// Return Results to Client
echo json_encode($arrayUsers);

?>