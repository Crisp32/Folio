<?php

/**
 * Folio Notifications Grabber
 * @author Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Initialize Database
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $notfications = $db->query("SELECT * FROM notifications WHERE uid=$user");

    $notifResponse = [];

    // Loop through each Notification
    while ($notif = $notfications->fetch_array(MYSQLI_ASSOC)) {
        array_push($notifResponse, [
            "nid" => $notif["nid"],
            "body" => utf8_decode($notif["message"]),
            "sub" => utf8_decode($notif["subMessage"]),
            "date" => $notif["date"]
        ]);
    }

    // Send to Client
    echo json_encode([
        "success" => true,
        "notifications" => $notifResponse
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to View your Notifications"
    ]);
}
