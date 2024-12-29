<?php

/**
 * Folio Account Deletion
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Initialize Composer
require_once "../vendor/autoload.php";

// Initialize Database
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $userInstance = new User();
    $userInstance->getUserDataByUID($user);

    $sendCode = parseBool($_REQUEST["generateCode"]);

    if ($sendCode) {
        $newCode = generateVerificationCode();
        $updateQuery = $db->query("UPDATE users SET verificationCode='$newCode' WHERE uid=$user");

        if ($updateQuery) {
            $username = $userInstance->user["username"];

            // Not actually anymore
            echo json_encode([
                "success" => true,
                "message" => "Sent Verification Code to your Email"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => $db->error
            ]);
        }
    } else {
        if ($userInstance->user["verificationCode"] == $_REQUEST["code"]) {
            $deleteQuery = $userInstance->deleteAccount();

            if ($deleteQuery) {
                session_unset();

                // Send Successful Response
                echo json_encode([
                    "success" => true,
                    "deleted" => true
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
                "message" => "Incorrect Verification Code"
            ]);
        }
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Delete your Account"
    ]);
}
