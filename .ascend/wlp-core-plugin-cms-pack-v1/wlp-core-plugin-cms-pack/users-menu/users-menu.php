<?php
/*
Plugin Name: Custom Users Menu
Description: Customizes the WordPress Users menu based on user capabilities using add_menu_page and add_submenu_page.
Version: 1.0
Author: Your Name
*/

// Hook into the admin_menu action to modify the users menu
add_action( 'admin_menu', 'custom_users_menu', 10 );

function custom_users_menu() {
    // Check if the current user can list users
    if ( current_user_can( 'list_users' ) ) {
        // Add the "Users" top-level menu item
        add_menu_page(
            __( 'Users' ),                  // Page title
            __( 'Users' ),                  // Menu title
            'list_users',                   // Capability required
            'users.php',                    // Menu slug
            '',                             // Function for content (not needed for top-level menu)
            'dashicons-admin-users',        // Menu icon
            70                               // Menu position
        );

        // Add submenus under the "Users" menu
        add_submenu_page(
            'users.php',                    // Parent slug
            __( 'All Users' ),              // Page title
            __( 'All Users' ),              // Menu title
            'list_users',                   // Capability required
            'users.php'                     // Menu slug
        );

        if ( current_user_can( 'create_users' ) ) {
            add_submenu_page(
                'users.php',                  // Parent slug
                _x( 'Add New', 'user' ),       // Page title
                _x( 'Add New', 'user' ),       // Menu title
                'create_users',                // Capability required
                'user-new.php'                 // Menu slug
            );
        } elseif ( is_multisite() ) {
            add_submenu_page(
                'users.php',                  // Parent slug
                _x( 'Add New', 'user' ),       // Page title
                _x( 'Add New', 'user' ),       // Menu title
                'promote_users',               // Capability required
                'user-new.php'                 // Menu slug
            );
        }

        // Add the "Profile" submenu under Users
        add_submenu_page(
            'users.php',                    // Parent slug
            __( 'Profile' ),                 // Page title
            __( 'Profile' ),                 // Menu title
            'read',                          // Capability required
            'profile.php'                    // Menu slug
        );

        // Add user taxonomies as submenus under "Users"
        $i = 20;
        foreach ( get_taxonomies( array(), 'objects' ) as $tax ) {
            if ( ! $tax->show_ui || ! $tax->show_in_menu || ! in_array( 'user', (array) $tax->object_type, true ) ) {
                continue;
            }
            add_submenu_page(
                'users.php',                       // Parent slug
                esc_attr( $tax->labels->menu_name ), // Page title
                esc_attr( $tax->labels->menu_name ), // Menu title
                $tax->cap->manage_terms,            // Capability required
                'edit-tags.php?taxonomy=' . $tax->name // Menu slug
            );
        }

    } else {
        // If the user can't list users, add the "Profile" top-level menu
        add_menu_page(
            __( 'Profile' ),                  // Page title
            __( 'Profile' ),                  // Menu title
            'read',                           // Capability required
            'profile.php',                    // Menu slug
            '',                               // Function for content (not needed for top-level menu)
            'dashicons-admin-users',          // Menu icon
            70                                 // Menu position
        );

        // Add submenus for profile-related items
        add_submenu_page(
            'profile.php',                   // Parent slug
            __( 'Profile' ),                 // Page title
            __( 'Profile' ),                 // Menu title
            'read',                          // Capability required
            'profile.php'                    // Menu slug
        );

        if ( current_user_can( 'create_users' ) ) {
            add_submenu_page(
                'profile.php',                  // Parent slug
                __( 'Add New User' ),            // Page title
                __( 'Add New User' ),            // Menu title
                'create_users',                  // Capability required
                'user-new.php'                   // Menu slug
            );
        } elseif ( is_multisite() ) {
            add_submenu_page(
                'profile.php',                  // Parent slug
                __( 'Add New User' ),            // Page title
                __( 'Add New User' ),            // Menu title
                'promote_users',                 // Capability required
                'user-new.php'                   // Menu slug
            );
        }
    }
}
?>
