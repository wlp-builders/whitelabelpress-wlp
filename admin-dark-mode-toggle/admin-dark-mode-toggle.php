<?php
/*
Plugin Name: Admin Dark Mode Toggle
Description: A plugin to enable dark mode in the WordPress admin area by default and edit CSS from the settings page.
Version: 1.4
Author: Neil
License: LGPL
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


// Enqueue styles
// cp/wp/other compatible
add_action('admin_head', 'admd_enqueue_styles');

function admd_enqueue_styles() {
    admd_wlp_add_admin_style(__DIR__.'/admin-style.css');
}

/**
 * Enqueue a custom admin style for the WordPress admin area.
 *
 * @param string $css_file_path The absolute path to the CSS file.
 */
function admd_wlp_add_admin_style( $css_file_path ) {
    // Ensure the CSS file exists before proceeding
    if ( ! file_exists( $css_file_path ) ) {
        return; // Stop execution if the CSS file doesn't exist
    }

    // Convert the absolute file path to a URL
    $css_file_url = str_replace( ABSPATH, home_url( '/' ), $css_file_path );

    // Get the basename of the directory for a unique handler name
    $plugin_dir_name = basename( dirname( $css_file_path ) );

    // Generate a unique handle using the plugin directory basename
    $handle = 'wlp-admin-style-' . $plugin_dir_name;

    
        wp_register_style(
            $handle, // Unique handle based on plugin directory name
            $css_file_url, // URL to the CSS file
            array(), // No dependencies
            filemtime( $css_file_path ), // Version based on the file's modification time
            'all' // Media type
        );

        // Enqueue the style
        wp_enqueue_style( $handle );
    
}

