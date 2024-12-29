<?php

/**
 * Folio Notification Remover
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init Database
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];
    $nid = escapeString($_REQUEST["nid"]);

    // Check User Permissions
    if ($nid == "all") { // Delete All Notifications
        $query = $db->query("DELETE FROM notifications WHERE uid=$user");

        if ($query) {
            echo json_encode([
                "success" => true
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => $db->error
            ]);
        }
    } else { // Delete Individual Notifications
        $notification = Notification::getAssoc($nid);

        if ($notification["uid"] == $user) {
            if (Notification::delete($nid)) {
                echo json_encode([
                    "success" => true
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
                "message" => "You do not have Permission to Perform this Action"
            ]);
        }
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Delete Notifications"
    ]);
}
