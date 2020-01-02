<?php
/**
 * Folio Main PHP File
 * Connell Reffo 2019
 */

// List of Possible Countries
$countries = [
    "Canada",
    "Costa Rica",
    "Cuba",
    "Mexico",
    "United States"
];

// Generate <option> tags for Account Location input field
function fetchLocationsHtml() {
    $countries = $GLOBALS["countries"];
    $final = "<option value='' >I'd Rather not Say</option>\n";

    foreach ($countries as $country) {
        $final .= "<option value='$country' >" . $country . "</option>\n";
    }

    return $final;
}

function initPHPMailer($mail, $sendTo) {
    // SMTP Settings
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "foliowebapp@gmail.com";
    $mail->Password = "phpapp2328";
    $mail->Port = 465;
    $mail->SMTPSecure = "ssl";

    // Email Settings
    $mail->isHTML(true);
    $mail->setFrom($sendTo, "Folio");
    $mail->addAddress($sendTo);
}

// Verification Code Algorithm
function generateVerificationCode() {
    return strtoupper(substr(md5(strval(rand(0, 200))), 0, 8));
}

// Returns User Information
function getUserData($db, $column, $condition) {
    $query = $db->query("SELECT $column FROM users WHERE $condition");
    $array = $query->fetchArray();

    return $array[$column];
}

// Change Users in DB
function updateUser($db, $column, $value, $condition) {
    $query = $db->query("UPDATE users SET $column = '$value' WHERE $condition");
    
    return $query;
}

// Calculate Votes
function calcVotes($votingData) {
    $votes = explode(":", $votingData);
    $voteCount = 1;

    if (count($votes) > 0) {
        foreach ($votes as $vote) {
            if (strpos($vote, "+") !== false) {
                $voteCount++;
            }
            else {
                $voteCount--;
            }
        }
    }
    
    return $voteCount;
}

// Prevent SQL Injection Attack
function escapeString($str) {
    return str_replace("'", "\'", htmlspecialchars($str, ENT_QUOTES, "UTF-8"));
}

// Validate Location Boolean
function validLocation($country) {
    $countries = $GLOBALS["countries"];

    return in_array($country, $countries);
}

// Validate that a file exists on Seperate Server
function validURL($url) {
    if (!empty($url)) {
        $header_response = get_headers($url);

        if ($header_response) {
            if (strpos($header_response[0], "404") !== false){
                return true;
            }
            else {
                return true;
            }
        }
        else {
            return false;
        }
    }
    else {
        return true;
    }
}

// Retrieve Data from a Comment
function getCommentData($db, $column, $type, $condition) {
    $finalCondition = "AND type='$type'";
    if ($type == "*") { $finalCondition = ""; }
    
    $query = $db->query("SELECT $column FROM comments WHERE $condition $finalCondition");
    $array = $query->fetchArray();
    
    return $array[$column];
}

?>