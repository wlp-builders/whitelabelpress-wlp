<?php
/*
Plugin Name: DidQL Wrapper 
Description: A simple didql (communication protocol) wrapper for did_users and plugin routes
Version: 1.1
License: Spirit of Time 1.0
Author: Neil
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__.'/lib/didql.php';

// simple compatible interface CP,WP,etc
add_action('init', 'didql_execute');
