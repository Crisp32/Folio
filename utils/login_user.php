<?php
/**
 * Folio Login Request Handler
 * Connell Reffo 2019
 */

session_start();
include_once "app_main.php";

// Get Input
$username = $_REQUEST["username"];
$password = $_REQUEST["password"];

// Init DB
$db = new SQLite3("../db/folio.db");

$userExists = getUserData($db, "username", "username='$username'");
$userPass = getUserData($db, "password", "username='$username'");
$userVerified = getUserData($db, "verified", "username='$username'");

// Validate Input
if (!empty($username)) {
    // Check Password
    if (!empty($password)) {

        // Validate Credentials with DB
        if (!empty($userExists)) {

            // Check if Verified
            if ($userVerified == 0) {
                echo json_encode(array(
                    "success" => false,
                    "message" => "$username is not yet Verified"
                ));
            }
            else {

                // Check for Matching Password
                if (password_verify($password, $userPass)) {
                    echo json_encode(array(
                        "success" => true,
                        "message" => "Successfully logged in as $username"
                    ));

                    $_SESSION["user"] = getUserData($db, "uid", "username='$username'");
                }
                else {
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Incorrect Password"
                    ));
                }
            }       
        }
        else {
            echo json_encode(array(
                "success" => false,
                "message" => "There is no Account with the Specified Name"
            ));
        }
    }
    else {
        echo json_encode(array(
            "success" => false,
            "message" => "Invalid Password"
        ));
    }
}
else {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Username"
    ));
}

?>