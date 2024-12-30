<?php
/*
Plugin Name: Custom Post Type Menu
Description: Customizes the admin menu for custom post types and their taxonomies using add_menu_page and add_submenu_page.
Version: 1.0
Author: Your Name
*/

// Hook into the admin_menu action to modify the admin menu
add_action( 'admin_menu', 'custom_post_type_menu', 10 );

function custom_post_type_menu() {
    // Get all public post types
    $types = (array) get_post_types(
        array(
            'show_ui'      => true,
            '_builtin'     => false,
            'show_in_menu' => true,
        )
    );
    
    $builtin = array( 'post', 'page' );
    foreach ( array_merge( $builtin, $types ) as $ptype ) {
        $ptype_obj = get_post_type_object( $ptype );
        
        // Skip post types that shouldn't show in the menu
        if ( true !== $ptype_obj->show_in_menu ) {
            continue;
        }

        // Default menu icon
        $menu_icon = 'dashicons-admin-post';
        if ( is_string( $ptype_obj->menu_icon ) ) {
            // Special handling for data:image/svg+xml and Dashicons.
            if ( str_starts_with( $ptype_obj->menu_icon, 'data:image/svg+xml;base64,' ) || str_starts_with( $ptype_obj->menu_icon, 'dashicons-' ) ) {
                $menu_icon = $ptype_obj->menu_icon;
            } else {
                $menu_icon = esc_url( $ptype_obj->menu_icon );
            }
        } elseif ( in_array( $ptype, $builtin, true ) ) {
            $menu_icon = 'dashicons-admin-' . $ptype;
        }

        // Menu and submenu labels and capabilities
        $menu_title = esc_attr( $ptype_obj->labels->menu_name );
        $capability = $ptype_obj->cap->edit_posts;

        // Define the menu file paths
        if ( 'post' === $ptype ) {
            $ptype_file = 'edit.php';
            $post_new_file = 'post-new.php';
            $edit_tags_file = 'edit-tags.php?taxonomy=%s';
        } else {
            $ptype_file = "edit.php?post_type=$ptype";
            $post_new_file = "post-new.php?post_type=$ptype";
            $edit_tags_file = "edit-tags.php?taxonomy=%s&amp;post_type=$ptype";
        }

        // Add the custom post type as a top-level menu item
        add_menu_page(
            $menu_title,              // Page title
            $menu_title,              // Menu title
            $capability,              // Capability required
            $ptype_file,              // Menu slug
            '',                       // Function for content (not needed for top-level menu)
            $menu_icon,               // Menu icon
            $ptype_obj->menu_position // Menu position
        );

        // Add the "All Items" submenu
        add_submenu_page(
            $ptype_file,             // Parent slug
            $ptype_obj->labels->all_items, // Page title
            $ptype_obj->labels->all_items, // Menu title
            $capability,             // Capability required
            $ptype_file              // Menu slug
        );

        // Add the "Add New" submenu
        add_submenu_page(
            $ptype_file,             // Parent slug
            $ptype_obj->labels->add_new, // Page title
            $ptype_obj->labels->add_new, // Menu title
            $ptype_obj->cap->create_posts, // Capability required
            $post_new_file           // Menu slug
        );

        // Add taxonomy submenus
        $i = 15;
        foreach ( get_taxonomies( array(), 'objects' ) as $tax ) {
            if ( ! $tax->show_ui || ! $tax->show_in_menu || ! in_array( $ptype, (array) $tax->object_type, true ) ) {
                continue;
            }

            add_submenu_page(
                $ptype_file,           // Parent slug
                esc_attr( $tax->labels->menu_name ), // Page title
                esc_attr( $tax->labels->menu_name ), // Menu title
                $tax->cap->manage_terms,  // Capability required
                sprintf( $edit_tags_file, $tax->name ) // Menu slug
            );
        }
    }
}
?>
