<?php

/**
 * Folio Logout Functionality
 * @author Connell Reffo
 */

session_start();

// Check if Logged in
if (isset($_SESSION["user"])) {
    echo json_encode("Logged out " . $_SESSION["user"]);
    session_unset();
}
