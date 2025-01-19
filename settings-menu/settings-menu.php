<?php
/*
Plugin Name: Custom Settings Menu
Description: Customizes the WordPress Settings menu using add_menu_page and add_submenu_page.
Version: 1.1
Author: Neil
License: GPL
*/

// Hook into the admin_menu action to modify the settings menu
add_action( 'admin_menu', 'custom_settings_menu', 10 );

function custom_settings_menu() {
    // Add the "Settings" top-level menu
    add_menu_page(
        __( 'Settings' ),                // Page title
        __( 'Settings' ),                // Menu title
        'manage_options',                // Capability required
        'options-general.php',           // Menu slug
        '',                              // Function for content (not needed for top-level menu)
        'dashicons-admin-settings',      // Menu icon
        80                               // Menu position
    );

    // Add submenus to the "Settings" menu
    add_submenu_page(
        'options-general.php',          // Parent slug
        _x( 'General', 'settings screen' ), // Page title
        _x( 'General', 'settings screen' ), // Menu title
        'manage_options',               // Capability required
        'options-general.php'           // Menu slug
    );

    add_submenu_page(
        'options-general.php',          // Parent slug
        __( 'Writing' ),                 // Page title
        __( 'Writing' ),                 // Menu title
        'manage_options',                // Capability required
        'options-writing.php'           // Menu slug
    );

    add_submenu_page(
        'options-general.php',          // Parent slug
        __( 'Reading' ),                 // Page title
        __( 'Reading' ),                 // Menu title
        'manage_options',                // Capability required
        'options-reading.php'           // Menu slug
    );

    add_submenu_page(
        'options-general.php',          // Parent slug
        __( 'Discussion' ),              // Page title
        __( 'Discussion' ),              // Menu title
        'manage_options',                // Capability required
        'options-discussion.php'        // Menu slug
    );

    add_submenu_page(
        'options-general.php',          // Parent slug
        __( 'Media' ),                   // Page title
        __( 'Media' ),                   // Menu title
        'manage_options',                // Capability required
        'options-media.php'             // Menu slug
    );

    add_submenu_page(
        'options-general.php',          // Parent slug
        __( 'Permalinks' ),              // Page title
        __( 'Permalinks' ),              // Menu title
        'manage_options',                // Capability required
        'options-permalink.php'         // Menu slug
    );

    add_submenu_page(
        'options-general.php',          // Parent slug
        __( 'Privacy' ),                 // Page title
        __( 'Privacy' ),                 // Menu title
        'manage_privacy_options',       // Capability required
        'options-privacy.php'           // Menu slug
    );
}
?>
