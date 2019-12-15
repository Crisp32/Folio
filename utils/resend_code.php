<?php

$accountUsername = $_REQUEST["uname"];

include_once "app_main.php";

// Init PHPMailer
use PHPMailer\PHPMailer\PHPMailer;

require_once("../PHPMailer/PHPMailer.php");
require_once("../PHPMailer/SMTP.php");
require_once("../PHPMailer/Exception.php");

// Init DB
$db = new SQLite3("../db/folio.db");

$email = getUserData($db, "email", "username='$accountUsername'");
$code = getUserData($db, "verificationCode", "username='$accountUsername'");
$isVerified = getUserData($db, "verified", "username='$accountUsername'");

$accountUsername = substr($accountUsername, 0, 20);
$newCode = generateVerificationCode();

if ($isVerified == 0) {
    if (!empty($accountUsername)) {
        if (!empty($email)) {
            $mail = new PHPMailer();

            // Send Email
            initPHPMailer($mail, $email);

            // Update User
            updateUser($db, "verificationCode", $newCode, "username='$accountUsername'");

            $mail->Subject = "Resent Folio Verification Code";
            $mail->Body = "
            <body style='background-color: #252529; padding: 20px; border: 7px solid #252529; border-radius: 7px' >
                <h2 style='color: white; position: absolute; margin: auto' >Hello $accountUsername, your new verification code is: </h2>
                <h1 style='color: #f53643; font-size: 40px; margin-top: 5px; position: absolute' >$newCode</h1>
            </body>
            ";

            // Send
            if ($mail->send()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Resent Code to $accountUsername's Email Address"
                ));
            }
            else {
                echo json_encode(array(
                    "success" => false,
                    "message" => substr($mail->ErrorInfo, 0, 40) . "..."
                ));
            }
        }
        else {
            echo json_encode(array(
                "success" => false,
                "message" => "There are no users called $accountUsername"
            ));
        }
    }
    else {
        echo json_encode(array(
            "success" => false,
            "message" => "Please enter Account Username"
        ));
    }
}
else {
    // Prevent User from Verifying more than once
    echo json_encode(array(
        "success" => false,
        "message" => "$accountUsername is already verified"
    ));
}

?>