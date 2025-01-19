<?php
// Reverse proxy settings
// Function to clean the HTTP_HOST, removing port and paths if present
function cleanHttpHost() {
    // Simulate the current HTTP_HOST
    $current_host = $_SERVER['HTTP_HOST'];

    // Use regex to extract only the host part (ignoring the port and paths)
    $cleaned_host = preg_split('/[:\/]/', $current_host)[0];

    $_SERVER['HTTP_HOST'] = $cleaned_host;
}
cleanHttpHost();


