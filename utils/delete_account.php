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
        // $newCode = generateVerificationCode();
        // $updateQuery = $db->query("UPDATE users SET verificationCode='$newCode' WHERE uid=$user");

        // if ($updateQuery) {
        //     $username = $userInstance->user["username"];

        //     // Create Mail Object
        //     $mail = new \SendGrid\Mail\Mail();

        //     $mail->setFrom($folioEmail, $folioName);
        //     $mail->setSubject("Confirm Fol.io Account Deletion");
        //     $mail->addTo($userInstance->user["email"], $username);

        //     $mail->addContent("text/html", "
        //     <body style='background-color: #252529; padding: 20px; border: 7px solid #252529; border-radius: 7px' >
        //         <h2 style='color: white; position: absolute; margin: auto' >Hello $username, your verification code is: </h2>
        //         <h1 style='color: #f53643; font-size: 40px; margin-top: 5px; position: absolute' >$newCode</h1>
        //     </body>
        //     ");

        //     // Initialize SendGrid
        //     $sendgrid = new \SendGrid($SENDGRID_API_KEY);

        //     // Send Mail
        //     try {
        //         $res = $sendgrid->send($mail);

        //         echo json_encode([
        //             "success" => true,
        //             "message" => "Sent Verification Code to your Email"
        //         ]);
        //     }
        //     catch (Exception $err) {
        //         echo json_encode([
        //             "success" => false,
        //             "message" => $err->getMessage()
        //         ]);
        //     }
        // }
        // else {
        //     echo json_encode([
        //         "success" => false,
        //         "message" => $db->error
        //     ]);
        // }
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
