<?php
/**
 * Folio Logout Functionality
 * Connell Reffo 2019
 */

session_start();

// Check if Logged in
if (isset($_SESSION["user"])) {
    echo json_encode("Logged out " . $_SESSION["user"]);
    session_unset();
}

?>