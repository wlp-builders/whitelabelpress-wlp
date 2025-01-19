<?php
/*
Plugin Name: Custom Plugins Menu
Description: Customizes the WordPress Plugins menu using add_menu_page and add_submenu_page, with a redirect to the plugin manager.
Version: 1.1
Author: Neil
License: GPL
*/

// Hook into the admin_menu action to modify the plugins menu
add_action( 'admin_menu', 'custom_plugins_menu', 10 );

function custom_plugins_menu() {
    // Modify the "Plugins" menu
    add_menu_page(
        'Plugins',       // Page title
        __( 'Plugins' ),                        // Menu title
        'activate_plugins',                     // Capability required
        'plugins.php',                          // Menu slug
        '',                                     // Function for content (not needed for top-level menu)
        'dashicons-admin-plugins',              // Menu icon
        65                                      // Menu position
    );

    // Redirect to the plugin manager page when visiting the plugins page without a specific plugin page
    if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'plugins.php' ) !== false && ! isset( $_GET['page'] ) ) {
        redirect_to_plugin_manager();
    }
}

// Callback function that handles the redirect
function redirect_to_plugin_manager() {
    wp_redirect( admin_url( 'plugins.php?page=plugin-manager' ) ); // Redirect to /wp-admin/plugins.php?page=plugin-manager
    exit; // Always call exit after a redirect
}
?>
