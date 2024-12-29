<?php

/**
 * Folio Init Connection with MySQL Database
 * @author Connell Reffo
 */

function db()
{
    // For Connecting to Docker
    $hostname = "mysql";
    $database = "folio_db";
    $username = "user";
    $password = "userpassword";
    $port = 3306;

    // Create Connection
    $db = new mysqli($hostname, $username, $password, $database, $port);

    // Check Connection
    if ($db->connect_error) {
        die("Database Error: " . $db->connect_error);
    }

    return $db;
}
