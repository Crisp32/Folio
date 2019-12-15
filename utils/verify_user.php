<?php
/**
 * Folio final Account Registration step
 * Connell Reffo 2019
 */

include_once "app_main.php";

// Get User Input
$username = $_REQUEST["uname"];
$code = str_replace(" ", "", $_REQUEST["code"]);

// Init DB
$db = new SQLite3("../db/folio.db");

$userExists = getUserData($db, "username", "username='$username'");
$dbCode = getUserData($db, "verificationCode", "username='$username'");
$isVerified = getUserData($db, "verified", "username='$username'");

// Validate
if ($isVerified == 0) {
    if (!empty($username)) {

        // Check User Information
        if (!empty($userExists)) {

            // Check Verification Code
            if (!empty($code)) {

                // Validate code with SQLite and make Changes to DB
                if (strtoupper($code) == strtoupper($dbCode)) {
                    if (updateUser($db, "verified", "1", "username='$username'")) {
                        echo json_encode(array(
                            "success" => true,
                            "redirect" => true,
                            "message" => "Successfully Verified your Account"
                        ));
                    }
                    else  {
                        echo json_encode(array(
                            "success" => false,
                            "message" => "Database Error"
                        ));
                    }
                }
                else {
                    echo json_encode(array(
                        "success" => false,
                        "message" => "That Code didn't work"
                    ));
                }
            }
            else {
                echo json_encode(array(
                    "success" => false,
                    "message" => "Please Enter the Code sent to your Email"
                ));
            }
        }
        else {
            echo json_encode(array(
                "success" => false,
                "message" => "There are no users with that name"
            ));
        }
    }
    else {
        echo json_encode(array(
            "success" => false,
            "message" => "Please Enter your Account Username"
        ));
    }
}
else {
    echo json_encode(array(
        "success" => false,
        "message" => "$username is already verified"
    ));
}

?>