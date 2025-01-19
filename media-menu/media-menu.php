<?php
/**
 * Plugin Name: Custom Media Menu
 * Description: A custom plugin to add and customize the Media menu in WordPress.
 * Version: 1.1
 * Author: Neil
 * License: LGPL
 */

// cp/wp/other compatible
// Hook to 'admin_menu' to add custom menus
add_action( 'admin_menu', 'custom_media_menu' );

function custom_media_menu() {
    // Add the main Media menu
    add_menu_page(
        __( 'Media' ),                    // Page title
        __( 'Media' ),                    // Menu title
        'upload_files',                   // Capability required to access
        'upload.php',                     // Menu slug
        '',                               // Function (empty to use default)
        'dashicons-admin-media',          // Icon
        10                                 // Position (index is 10)
    );

    // Add the 'Library' submenu
    add_submenu_page(
        'upload.php',                     // Parent slug
        __( 'Library' ),                  // Page title
        __( 'Library' ),                  // Menu title
        'upload_files',                   // Capability required to access
        'upload.php'                      // Menu slug
    );

   

    // Add taxonomy-based submenus for attachments
    $i = 15; // Start submenu index
    foreach ( get_taxonomies_for_attachments( 'objects' ) as $tax ) {
        if ( ! $tax->show_ui || ! $tax->show_in_menu ) {
            continue;
        }

        add_submenu_page(
            'upload.php',                                         // Parent slug
            esc_attr( $tax->labels->menu_name ),                   // Page title
            esc_attr( $tax->labels->menu_name ),                   // Menu title
            $tax->cap->manage_terms,                              // Capability required to access
            'edit-tags.php?taxonomy=' . $tax->name . '&post_type=attachment' // Menu slug
        );
        $i++; // Increment submenu index
    }
}

require_once __DIR__.'/pages/improved-uploader-webp-support.php';
