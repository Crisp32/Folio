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
            $mail = new \SendGrid\Mail\Mail();

            $mail->setFrom($folioEmail, $folioName);
            $mail->setSubject("Resent Fol.io Account Verification Code");
            $mail->addTo($email, $accountUsername);

            updateUser("verificationCode", $newCode, "username='$accountUsername'");

            $mail->addContent("text/html", "
            <body style='background-color: #252529; padding: 20px; border: 7px solid #252529; border-radius: 7px' >
                <h2 style='color: white; position: absolute; margin: auto' >Hello $accountUsername, your new verification code is: </h2>
                <h1 style='color: #f53643; font-size: 40px; margin-top: 5px; position: absolute' >$newCode</h1>
            </body>
            ");
            
            // Initialize SendGrid
            $sendgrid = new \SendGrid($SENDGRID_API_KEY);
            
            // Send Mail
            try {
                $res = $sendgrid->send($mail);

                echo json_encode([
                    "success" => true,
                    "message" => "Resent Verification Code to $accountUsername's Email Address"
                ]);
            }
            catch (Exception $err) {
                echo json_encode([
                    "success" => false,
                    "message" => $err->getMessage()
                ]);
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