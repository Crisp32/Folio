<?php
/**
 * Folio Account Deletion
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Initialize Database
$db = db();

// Initialize PHPMailer
use PHPMailer\PHPMailer\PHPMailer;

require_once("../PHPMailer/PHPMailer.php");
require_once("../PHPMailer/SMTP.php");
require_once("../PHPMailer/Exception.php");

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

            // Send Email
            $mail = new PHPMailer();
    
            initPHPMailer($mail, $userInstance->user["email"]);
            $username = $userInstance->user["username"];
    
            $mail->Subject = "Folio Verification Code";
            $mail->Body = "
            <body style='background-color: #252529; padding: 20px; border: 7px solid #252529; border-radius: 7px' >
                <h2 style='color: white; position: absolute; margin: auto' >Hello $username, your verification code is: </h2>
                <h1 style='color: #f53643; font-size: 40px; margin-top: 5px; position: absolute' >$newCode</h1>
            </body>
            ";

            if ($mail->send()) {
                echo json_encode([
                    "success" => true,
                    "message" => "Sent Verification Code to you Email Address"
                ]);
            }
            else {
                echo json_encode([
                    "success" => false,
                    "message" => substr($mail->ErrorInfo, 0, 40) . "..."
                ]);
            }
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => $db->error
            ]);
        }
    }
    else {
        if ($userInstance->user["verificationCode"] == $_REQUEST["code"]) {
            $deleteQuery = $userInstance->deleteAccount();

            if ($deleteQuery) {
                session_unset();

                // Send Successful Response
                echo json_encode([
                    "success" => true,
                    "deleted" => true
                ]);
            }
            else {
                echo json_encode([
                    "success" => false,
                    "message" => $db->error
                ]);
            }
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => "Incorrect Verification Code"
            ]);
        }
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Delete your Account"
    ]);
}