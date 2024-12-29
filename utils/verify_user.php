<?php

/**
 * Folio final Account Registration step
 * Connell Reffo 2019
 */

include_once "app_main.php";

// Get User Input
$username = escapeString($_REQUEST["uname"]);
$code = escapeString($_REQUEST["code"]);

// Init DB
$db = db();

// Get User Data
$user = new User();
$user->getUserDataByName($username);

$dbCode = $user->user["verificationCode"];
$isVerified = $user->user["verified"];

// Validate
if ($isVerified == 0) {
    if (!empty($user->user["username"])) {

        // Check User Information
        if (userExists($user->user["uid"])) {

            // Check Verification Code
            if (!empty($code)) {

                // Validate code with SQLite and make Changes to DB
                if (strtoupper($code) == strtoupper($dbCode)) {
                    if ($user->update("verified", "1")) {
                        echo json_encode(array(
                            "success" => true,
                            "redirect" => true,
                            "message" => "Successfully Verified your Account"
                        ));
                    } else {
                        echo json_encode(array(
                            "success" => false,
                            "message" => $db->error
                        ));
                    }
                } else {
                    echo json_encode(array(
                        "success" => false,
                        "message" => "That Code didn't work"
                    ));
                }
            } else {
                echo json_encode(array(
                    "success" => false,
                    "message" => "Please Enter the Code sent to your Email"
                ));
            }
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "There are no users with that name"
            ));
        }
    } else {
        echo json_encode(array(
            "success" => false,
            "message" => "Please Enter your Account Username"
        ));
    }
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "$username is already verified"
    ));
}
