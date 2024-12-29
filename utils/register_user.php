<?php

/**
 * Folio Register User File
 * Connell Reffo 2019
 */

include_once "app_main.php";

// Init Composer
require_once "../vendor/autoload.php";

// Authenticate Input
$email = escapeString($_REQUEST["email"]);
$location = escapeString($_REQUEST["location"]);
$username = escapeString($_REQUEST["username"]);
$password = escapeString($_REQUEST["password"]);
$confPass = escapeString($_REQUEST["confPass"]);

$maxChars = 20;
$minPass = 6;

$illegalChars = "'&*()^%$#@!+:-[]";

// Insertion Query
if (empty($location) || !validLocation($location)) {
    $location = "Unknown";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Email"
    ));
} else if (empty($username) || $username != strip_tags($username) || filter_var($username, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Username"
    ));
} else if (strpbrk($username, $illegalChars)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Username Cannot Contain $illegalChars"
    ));
} else if (strpos($username, " ") !== false) {
    echo json_encode(array(
        "success" => false,
        "message" => "Username cannot contain Spaces"
    ));
} else if (strlen($username) > $maxChars) {
    echo json_encode(array(
        "success" => false,
        "message" => "Username is too Long (Maximum $maxChars Characters)"
    ));
} else if ($username != utf8_decode($username)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Username cannot Contain Special Characters"
    ));
} else if (empty($password) || empty($confPass) || $password != strip_tags($password) || $confPass != strip_tags($confPass)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Password(s)"
    ));
} else if ($password != $confPass) {
    echo json_encode(array(
        "success" => false,
        "message" => "Passwords don't Match"
    ));
} else if ($password != utf8_decode($password)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Password cannot Contain Special Characters"
    ));
} else if (strlen($password) > $maxChars) {
    echo json_encode(array(
        "success" => false,
        "message" => "Password is too Long\n (Maximum $maxChars Characters)"
    ));
} else if (strlen($password) < $minPass) {
    echo json_encode(array(
        "success" => false,
        "message" => "Password is too Short\n (Minimum $minPass Characters)"
    ));
} else if (!empty(getUserData("username", "username='$username'"))) {
    // Check for duplicate usernames
    echo json_encode(array(
        "success" => false,
        "message" => "An Account with that Username already exists"
    ));
} else if (!empty(getUserData("email", "email='$email'"))) {
    // Check for duplicate emails
    echo json_encode(array(
        "success" => false,
        "message" => "An Account with that Email already exists"
    ));
} else {
    $votesJSON = '{"upvotes": [], "downvotes": []}';
    $code = generateVerificationCode(); // Generate Verification Code
    $passHash = password_hash($password, PASSWORD_BCRYPT, array("cost" => 11));
    $profImg = randomProfileImage();
    $date = currentDate();
    $db = $GLOBALS["db"];
    $query = "INSERT INTO
        users (username, email, accountLocation, password, verificationCode, verified, profileBio, voteCount, date, allowComments, profileImagePath, votes, joinedForums) 
        VALUES('$username', '$email', '$location', '$passHash', '$code', '0', 'Sample Bio', '0', '$date', '1', '$profImg', '$votesJSON', '[]')
    ";

    if ($db->query($query)) {
        // Not anymore
        echo json_encode([
            "success" => true,
            "message" => "Sent Verification Code to $email"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => $db->error
        ]);
    }
}
