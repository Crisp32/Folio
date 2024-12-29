<?php

include_once "app_main.php";
$accountUsername = escapeString($_REQUEST["uname"]);

// Init DB
$db = db();

// Init Composer
require_once "../vendor/autoload.php";

$isVerified = getUserData("verified", "username='$accountUsername'");
$newCode = generateVerificationCode();

if ($isVerified == 0) {
    if (!empty($accountUsername)) {
        $email = getUserData("email", "username='$accountUsername'");

        if (!empty($email)) {
            // Not actually anymore
            echo json_encode([
                "success" => true,
                "message" => "Resent Verification Code to $accountUsername's Email Address"
            ]);
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "There are no users called $accountUsername"
            ));
        }
    } else {
        echo json_encode(array(
            "success" => false,
            "message" => "Please enter Account Username"
        ));
    }
} else {
    // Prevent User from Verifying more than once
    echo json_encode(array(
        "success" => false,
        "message" => "$accountUsername is already verified"
    ));
}
