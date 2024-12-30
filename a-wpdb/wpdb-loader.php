<?php
/*
Plugin Name: WPDB as core plugin
Description: wpdb
Version: 1.0
Author: Neil
*/


/**
 * @global wpdb $wpdb WordPress database abstraction object.
 * @since 0.71
 */
global $wpdb;
// Include the wpdb class and, if present, a db.php database drop-in.
require_wp_db();

// Set the database table prefix and the format specifiers for database table columns.
$GLOBALS['table_prefix'] = $table_prefix;
wp_set_wpdb_vars();

