<?php

/**
 * Folio Password Reset
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init Database
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {
    $oldPass = escapeString($_REQUEST["oldpass"]);
    $newPass = escapeString($_REQUEST["newpass"]);
    $confirmNewPass = escapeString($_REQUEST["newpassConf"]);

    // Get User Data
    $userInstance = new User();
    $userInstance->getUserDataByUID($_SESSION["user"]);
    $userPass = $userInstance->user["password"];

    // Validate Input
    if (strlen($oldPass) == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid Entry for Old Password Field"
        ]);
    } else if (strlen($newPass) < 6) {
        echo json_encode([
            "success" => false,
            "message" => "New Password Must be at Least 6 Characters"
        ]);
    } else if (strlen($newPass) > 20) {
        echo json_encode([
            "success" => false,
            "message" => "New Password Must be Less than 20 Characters"
        ]);
    } else if ($newPass !== $confirmNewPass) {
        echo json_encode([
            "success" => false,
            "message" => "New Passwords don't Match"
        ]);
    } else {
        if (password_verify($oldPass, $userPass)) {
            $hashedPass = password_hash($newPass, PASSWORD_BCRYPT, ["cost" => 11]);

            $uid = $userInstance->user["uid"];
            $updateQuery = $db->query("UPDATE users SET password='$hashedPass' WHERE uid=$uid");

            if ($updateQuery) {
                echo json_encode([
                    "success" => true
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => $db->error
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Old Password is Incorrect"
            ]);
        }
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Change your Password"
    ]);
}
