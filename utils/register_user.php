<?php
/**
 * Folio Register User File
 * Connell Reffo 2019
 */


// Init PHPMailer
use PHPMailer\PHPMailer\PHPMailer;

require_once("../PHPMailer/PHPMailer.php");
require_once("../PHPMailer/SMTP.php");
require_once("../PHPMailer/Exception.php");

// Authenticate Input
$email = $_REQUEST["email"];
$location = $_REQUEST["location"];
$username = $_REQUEST["username"];
$password = $_REQUEST["password"];
$confPass = $_REQUEST["confPass"];

$maxChars = 20;
$minPass = 6;

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Email"
    ));
}
else if (empty($username)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Username"
    ));
}
else if (strlen($username) > $maxChars) {
    echo json_encode(array(
        "success" => false,
        "message" => "Username is too Long (Maximum $maxChars Characters)"
    ));
}
else if (empty($password) || empty($confPass)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Password(s)"
    ));
}
else if ($password != $confPass) {
    echo json_encode(array(
        "success" => false,
        "message" => "Passwords don't Match"
    ));
}
else if (strlen($password) > $maxChars) {
    echo json_encode(array(
        "success" => false,
        "message" => "Password is too Long\n (Maximum $maxChars Characters)"
    ));
}
else if (strlen($password) < $minPass) {
    echo json_encode(array(
        "success" => false,
        "message" => "Password is too Short\n (Minimum $minPass Characters)"
    ));
}
else {
    $mail = new PHPMailer();
    $code = "69";

    // SMTP Settings
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "chromacorn52@gmail.com";
    $mail->Password = "majestic23";
    $mail->Port = 465;
    $mail->SMTPSecure = "ssl";

    // Email Settings
    $mail->isHTML(true);
    $mail->setFrom($email, $mail->Username);
    $mail->addAddress($email);
    $mail->Subject = "Folio Verification Code";
    $mail->Body = "<h2>Hello $username, your verification code is: </h2><h1>$code</h1>";

    if ($mail->send() && $codeSent != "1") {
        echo json_encode(array(
            "success" => true,
            "verify" => true,
            "message" => "Sent Email Verifaction to " . substr($email, 0, 23)
        ));
    }
    else {
        echo json_encode(array(
            "success" => false,
            "message" => substr($mail->ErrorInfo, 0, 40) . "..."
        ));
    }
}

?>