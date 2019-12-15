<?php
/**
 * Folio Login Request Handler
 * Connell Reffo 2019
 */

include "app_main.php";

// Get Input
$username = $_REQUEST["username"];
$password = $_REQUEST["password"];

// Init DB
$db = new SQLite3("../db/folio.db");

$userExists = getUserData($db, "username", "username='$username'");
$userPass = getUserData($db, "password", "username='$username'");
$userVerified = getUserData($db, "verified", "username='$username'");

$hashedPass = password_hash($password, PASSWORD_BCRYPT, array("cost" => 11));

// Validate Input
if ($userVerified != 0) {
    if (!empty($username)) {

        // Check Password
        if (!empty($password)) {

            // Validate Credentials with DB
            if (!empty($userExists)) {

                // Check for Matching Password
                if ($hashedPass == $userPass) {
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Successfully logged in as $username"
                    ));
                }
                else {
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Incorrect Password"
                    ));
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
}
else {
    echo json_encode(array(
        "success" => false,
        "message" => "$username is not yet verified"
    ));
}

?>