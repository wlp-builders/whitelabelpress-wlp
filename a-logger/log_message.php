<?php

// Simple logging function
function log_message($message) {
    if(defined('WLP_LOGGER_FILE')) {
        $timestamp = date('Y-m-d H:i:s');
        $formatted_message = "[$timestamp] $message\n";
        file_put_contents(WLP_LOGGER_FILE, $formatted_message, FILE_APPEND);
    } else {
        //var_dump('WLP_LOGGER_FILE not defined');
    }
}

