<?php
/**
 * Folio Register User File
 * Connell Reffo 2019
 */

include_once "app_main.php";

// Init PHPMailer
use PHPMailer\PHPMailer\PHPMailer;

require_once("../PHPMailer/PHPMailer.php");
require_once("../PHPMailer/SMTP.php");
require_once("../PHPMailer/Exception.php");

// Init DB
$db = new SQLite3("../db/folio.db");

// Authenticate Input
$email = $_REQUEST["email"];
$location = $_REQUEST["location"];
$username = $_REQUEST["username"];
$password = $_REQUEST["password"];
$confPass = $_REQUEST["confPass"];

$maxChars = 20;
$minPass = 6;

// Insertion Query
if (empty($location)) {
    $location = "Unknown";
}

$code = generateVerificationCode(); // Generate Verification Code
$passHash = password_hash($password, PASSWORD_BCRYPT, array("cost" => 11));
$query = "INSERT INTO
    users(username, email, accountLocation, password, verificationCode, verified) 
    VALUES('$username', '$email', '$location', '$passHash', '$code', '0')
";

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Email"
    ));
}
else if (empty($username) || $username != strip_tags($username)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Username"
    ));
}
else if (strpos($username, " ") !== false) {
    echo json_encode(array(
        "success" => false,
        "message" => "Username cannot contain Spaces"
    ));
}
else if (strlen($username) > $maxChars) {
    echo json_encode(array(
        "success" => false,
        "message" => "Username is too Long (Maximum $maxChars Characters)"
    ));
}
else if (empty($password) || empty($confPass) || $password != strip_tags($password) || $confPass != strip_tags($confPass)) {
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
    // Create Account and Send Auth info
    $mail = new PHPMailer();

    // Send Email
    initPHPMailer($mail, $email);

    $mail->Subject = "Folio Verification Code";
    $mail->Body = "
    <body style='background-color: #252529; padding: 20px; border: 7px solid #252529; border-radius: 7px' >
        <h2 style='color: white; position: absolute; margin: auto' >Hello $username, your verification code is: </h2>
        <h1 style='color: #f53643; font-size: 40px; margin-top: 5px; position: absolute' >$code</h1>
    </body>
    ";

    // Send Code
    if ($mail->send()) {

        // Create User
        if (!empty(getUserData($db, "username", "username='$username'"))) {
            // Check for duplicate usernames
            echo json_encode(array(
                "success" => false,
                "message" => "An Account with that Username already exists"
            ));
        }
        else if (!empty(getUserData($db, "email", "email='$email'"))) {
            // Check for duplicate emails
            echo json_encode(array(
                "success" => false,
                "message" => "An Account with that Email already exists"
            ));
        }
        else if (!insertUser($db, $query)) { // Execute Query
            echo json_encode(array(
                "success" => false,
                "message" => "Database Error"
            ));
        }
        else {
            // Send Successful Response
            echo json_encode(array(
                "success" => true,
                "verify" => true,
                "message" => "Sent Email Verifaction to " . substr($email, 0, 23)
            ));
        }
    }
    else {
        echo json_encode(array(
            "success" => false,
            "message" => substr($mail->ErrorInfo, 0, 40) . "..."
        ));
    }
}

function insertUser($db, $query) { 
    $result = $db->query($query);
    return $result;
}

?>