<?php

/**
 * Folio Login Request Handler
 * Connell Reffo 2019
 */

session_start();
include_once "app_main.php";

// Get Input
$username = escapeString($_REQUEST["username"]);
$password = escapeString($_REQUEST["password"]);

// Init DB
$db = db();

// Get User Data
$username = getUserData("username", "username='$username' OR email='$username'");

$user = new User();
$user->getUserDataByName($username);

$userPass = $user->user["password"];
$userVerified = $user->user["verified"];

// Validate Input
if (!empty($username)) {

    // Check Password
    if (!empty($password)) {

        // Validate Credentials with DB
        if (userExists($user->user["uid"])) {

            // Check if Verified
            if ($userVerified == 0) {
                echo json_encode(array(
                    "success" => false,
                    "message" => "That account is not yet Verified"
                ));
            } else {

                // Check for Matching Password
                if (password_verify($password, $userPass)) {
                    echo json_encode(array(
                        "success" => true,
                        "message" => "Successfully logged in as " . $user->user["username"]
                    ));

                    // Create Session
                    $_SESSION["user"] = $user->user["uid"];
                } else {
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Incorrect Password"
                    ));
                }
            }
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "There is no Account with the Specified Name"
            ));
        }
    } else {
        echo json_encode(array(
            "success" => false,
            "message" => "Invalid Password"
        ));
    }
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Username"
    ));
}
