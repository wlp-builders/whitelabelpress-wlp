<?php
/*
Plugin Name: Custom Appearance Menu
Description: Customizes the WordPress Appearance menu using add_menu_page and add_submenu_page.
Version: 1.0
Author: Neil
License: LGPL
 */

// uses cp/wp/other compatible methods

// Hook into the admin_menu action to modify the Appearance menu
add_action( 'admin_menu', 'custom_appearance_menu', 10 );

function custom_appearance_menu() {
    // Define the capability based on the user's permissions
    $appearance_cap = current_user_can( 'switch_themes' ) ? 'switch_themes' : 'edit_theme_options';

    // Add the Appearance menu item
    add_menu_page(
        __( 'Appearance' ),                        // Page title
        __( 'Appearance' ),                        // Menu title
        $appearance_cap,                           // Capability required
        'themes.php',                              // Menu slug
        '',                                        // Function for content (not needed for top-level menu)
        'dashicons-admin-appearance',              // Menu icon
        60                                         // Menu position
    );

    // Add the "Themes" submenu
    $count = ''; // Calculate or define the count of theme updates if needed
    

    // Add the "Customize" submenu
    $customize_url = get_site_url() . '/wp-admin/customize.php?url=' . get_site_url();
    add_submenu_page(
        'themes.php',                             // Parent slug
        __( 'Customize' ),                        // Page title
        __( 'Customize' ),                        // Menu title
        'customize',                              // Capability required
        esc_url( $customize_url ),                // Submenu slug (custom URL)
        ''                                        // No callback for custom URL
    );

    // Add the "Menus" submenu if theme supports it
    if ( current_theme_supports( 'menus' ) || current_theme_supports( 'widgets' ) ) {
        add_submenu_page(
            'themes.php',                         // Parent slug
            __( 'Menus' ),                        // Page title
            __( 'Menus' ),                        // Menu title
            'edit_theme_options',                 // Capability required
            'nav-menus.php'                       // Submenu slug
        );
    }

    // Add the "Header" submenu if theme supports custom-header and user can customize
    if ( current_theme_supports( 'custom-header' ) && current_user_can( 'customize' ) ) {
        $customize_header_url = add_query_arg(
            array( 'autofocus' => array( 'control' => 'header_image' ) ),
            $customize_url
        );
        add_submenu_page(
            'themes.php',                         // Parent slug
            __( 'Header' ),                        // Page title
            __( 'Header' ),                        // Menu title
            $appearance_cap,                       // Capability required
            esc_url( $customize_header_url ),      // Submenu slug (custom URL)
            ''                                      // No callback for custom URL
        );
    }

    // Add the "Background" submenu if theme supports custom-background and user can customize
    if ( current_theme_supports( 'custom-background' ) && current_user_can( 'customize' ) ) {
        $customize_background_url = add_query_arg(
            array( 'autofocus' => array( 'control' => 'background_image' ) ),
            $customize_url
        );
        add_submenu_page(
            'themes.php',                         // Parent slug
            __( 'Background' ),                    // Page title
            __( 'Background' ),                    // Menu title
            $appearance_cap,                       // Capability required
            esc_url( $customize_background_url ),  // Submenu slug (custom URL)
            ''                                      // No callback for custom URL
        );
    }
}
?>
