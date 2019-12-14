<?php
/**
 * Folio Main PHP File
 * Connell Reffo 2019
 */

// Generate <option> tags for Account Location input field
function fetchLocationsHtml() {
    $countries = [
        "Canada",
        "Costa Rica",
        "Cuba",
        "Mexico",
        "United States"
    ];

    $final = "";

    foreach ($countries as $country) {
        $final .= "<option value='" . str_replace(" ", "-", $country) . "' >" . $country . "</option>\n";
    }

    return $final;
}

?>