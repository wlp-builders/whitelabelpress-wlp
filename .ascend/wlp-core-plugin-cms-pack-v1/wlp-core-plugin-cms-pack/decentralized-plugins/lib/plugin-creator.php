<?php
/*
Plugin Name: Plugin Creator
Description: A simple plugin that adds a page to create other plugins from a code snippet.
Version: 1.1
Author: Your Name
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Hook to add a menu item in the WordPress admin sidebar
add_action( 'admin_menu', 'plugin_creator_add_admin_menu',1000);


function plugin_creator_add_admin_menu() {
    add_submenu_page(
        'plugins.php',            // Parent slug (this is for the Plugins menu)
        'Creator',               // Menu title
        'Creator',               // Menu title
        'manage_options',               // Capability
        'plugin_creator',               // Menu slug
        'plugin_creator_page_content',  // Callback function
        1000                              // Position
    );
}

function plugin_creator_page_content() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Handle form submission
    if ( isset( $_POST['plugin_code'] ) ) {
        $plugin_code = wp_unslash( $_POST['plugin_code'] ); // Unsanitize the input to get raw data
        plugin_creator_create_plugin( $plugin_code );
    }

    ?>
    <div class="wrap">
        <h1>Create a New Plugin or Replace Existing</h1>
        <form method="POST">
            <textarea name="plugin_code" rows="20" style="width:100%;"></textarea>
            <br><br>
            <button type="submit" class="button button-primary">Create Plugin</button>
        </form>
    </div>
    <?php
}

function plugin_creator_create_plugin( $plugin_code ) {
    // Sanitize the entire plugin code
    //$plugin_code = sanitize_textarea_field( $plugin_code );

    // Use regex to extract the plugin name from the "Plugin Name: " line
    if ( preg_match( '/Plugin Name:\s*(.*)/', $plugin_code, $matches ) ) {
        $plugin_name = sanitize_text_field( trim( $matches[1] ) );
        $plugin_slug = sanitize_title( $plugin_name );

        // Define the directory and file paths
        $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
        $plugin_file = $plugin_dir . '/' . $plugin_slug . '.php';

        // Remove existing directory if it exists
        if ( is_dir( $plugin_dir ) ) {
            plugin_creator_delete_directory( $plugin_dir );
        }

        // Create the plugin directory
        wp_mkdir_p( $plugin_dir );

        // Write the sanitized code to the new plugin file
        file_put_contents( $plugin_file, $plugin_code);

        // Notify the user of success
        echo '<div class="notice notice-success is-dismissible"><p>Plugin created successfully: ' . esc_html( $plugin_name ) . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Error: Could not find a valid "Plugin Name" in the code.</p></div>';
    }
}

// Function to recursively delete a directory and its contents
function plugin_creator_delete_directory( $dir ) {
    if ( ! is_dir( $dir ) ) {
        return;
    }

    $files = scandir( $dir );
    foreach ( $files as $file ) {
        if ( $file === '.' || $file === '..' ) {
            continue;
        }

        $file_path = $dir . '/' . $file;
        if ( is_dir( $file_path ) ) {
            plugin_creator_delete_directory( $file_path );
        } else {
            unlink( $file_path );
        }
    }
    rmdir( $dir );
}

