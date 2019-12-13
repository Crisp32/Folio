<?php

$countries = "";

function getLocationOptionsHtml() {
    $final = "";

    foreach ($countries as $country) {
        $final .= "<option value=" + $country + " >" + $country + "</option>\n";
    }

    return $final;
}

?>