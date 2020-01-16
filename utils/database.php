<?php
/**
 * Folio Init Connection with MySQL Database
 * @author Connell Reffo
 */

function db() {
    $server = "remotemysql.com";
    $username = "CRWpW7yfGa";
    $password = "gYaLrd0qZ7";
    
    // Create Connection
    $db = new mysqli($server, $username, $password, $username);
    
    // Check Connection
    if ($db->connect_error) {
        die("Database Error: " . $conn->connect_error);
    }

    return $db;
}
