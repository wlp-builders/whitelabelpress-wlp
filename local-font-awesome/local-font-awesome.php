<?php
/**
 * Plugin Name: Local Font Awesome
 * Description: A simple plugin to load Font Awesome locally in the WordPress admin area.
 * Version: 1.0
 * Author: Neil
 * License: GPL
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Enqueue Font Awesome locally in the admin area
 */
function load_local_font_awesome() {
    // Define the path to the Font Awesome CSS file
    $font_awesome_path = ABSPATH . 'wlp-core/wlp-includes/font-awesome/all.min.css';
    
    // Check if the file exists before enqueueing
    if (file_exists($font_awesome_path)) {
        // Enqueue Font Awesome stylesheet using the correct URL based on the site URL
        wp_enqueue_style('font-awesome', get_site_url() . '/wlp-core/wlp-includes/font-awesome/all.min.css', array(), '6.5.0');
    }
}

// Hook the function to the admin_enqueue_scripts action
add_action('admin_enqueue_scripts', 'load_local_font_awesome');
