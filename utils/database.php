<?php
/**
 * Folio Init Connection with MySQL Database
 * @author Connell Reffo
 */

function db() {

    // Following was removed for privacy reasons
    $server = "";
    $username = "";
    $password = "";
    
    // Create Connection
    $db = new mysqli($server, $username, $password, $username);
    
    // Check Connection
    if ($db->connect_error) {
        die("Database Error: " . $db->connect_error);
    }

    return $db;
}
