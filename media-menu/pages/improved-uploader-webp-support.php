<?php
/**
 * Plugin Name: Improved Image Upload with WebP Support
 * Description: A plugin to improve the image upload experience with AJAX in the WordPress admin panel, including WebP support.
 * Version: 1.3
 * Author: Your Name
 * License: GPL2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add the admin menu page under Media
function iiu_add_admin_menu() {
    // Add a sub-menu item under the Media menu
    add_submenu_page(
        'upload.php',                      // Parent slug (Media menu)
        'Upload New',           // Page title
        'Upload New',           // Menu title
        'upload_files',                  // Capability required to access the page
        'improved-upload',           // Menu slug
        'iiu_admin_page'                   // Function to display the page content
    );
}
add_action( 'admin_menu', 'iiu_add_admin_menu' );

// Display the admin page content
function iiu_admin_page() {
    // Get max upload size in bytes and convert to MB
    $max_upload_size = min(ini_get('upload_max_filesize'), ini_get('post_max_size'));
    $max_upload_size_mb = round( (int)$max_upload_size , 2 ); // Divide by 1MB (1024*1024 bytes)
    ?>
    <div class="wrap">
        <h1>Upload New Media</h1>
        <form id="iiu-upload-form" method="post" enctype="multipart/form-data">
            <input type="file" id="iiu-file-input" name="files[]" multiple />
            <p><strong>Max file size: <?php echo $max_upload_size_mb; ?>Mb</strong></p>
            <div id="iiu-upload-status"></div>
        </form>
    </div>
    <?php
}

// Enqueue scripts for the admin page
function iiu_enqueue_admin_scripts($hook) {
    // Only enqueue on the plugin's admin page
    if ($hook !== 'media_page_improved-upload') {
        return;
    }

    wp_enqueue_script( 'iiu-ajax-upload', plugin_dir_url( __FILE__ ) . 'iiu-ajax-upload.js', array( 'jquery' ), null, true );
    
    // Localize script to pass ajaxurl and nonce
    wp_localize_script( 'iiu-ajax-upload', 'iiu_ajax_obj', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'iiu_nonce' ),
    ));
}
add_action( 'admin_enqueue_scripts', 'iiu_enqueue_admin_scripts' );

// Allow WebP uploads
function iiu_allow_webp_uploads( $mimes ) {
    $mimes['webp'] = 'image/webp';  // Add WebP support
    return $mimes;
}
add_filter( 'upload_mimes', 'iiu_allow_webp_uploads' );

// Handle the file upload via AJAX and add it to the media library
function iiu_handle_file_upload() {
    // Verify the nonce for security
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'iiu_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Nonce validation failed' ) );
    }

    if ( !current_user_can( 'upload_files' ) ) {
        die( 'You do not have permission to upload media.' );
    }

    // Check if the files are set
    if ( isset( $_FILES['files'] ) ) {
        $files = $_FILES['files'];
        $uploaded_files = [];

        // Loop through each file and handle the upload
        foreach ( $files['name'] as $key => $file_name ) {
            // Create a $_FILES array for the individual file
            $file = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];

            // Define the upload overrides (to avoid form checks)
            $upload_overrides = array( 'test_form' => false );

            // Use wp_handle_upload to process the file
            $uploaded_file = wp_handle_upload( $file, $upload_overrides );

            if ( isset( $uploaded_file['error'] ) ) {
                wp_send_json_error( array( 'message' => 'Error uploading some files: ' . $uploaded_file['error'] ) );
                return; // Stop the process if any file failed
            }

            // If upload is successful, add the image to the Media Library
            $file_path = $uploaded_file['file'];
            $file_url = $uploaded_file['url'];
            $file_type = mime_content_type($file_path);

            // Prepare attachment data
            $attachment = array(
                'post_mime_type' => $file_type,
                'post_title' => basename($file_path),
                'post_content' => '',
                'post_status' => 'inherit',
            );

            // Insert the attachment to the media library
            $attachment_id = wp_insert_attachment( $attachment, $file_path );

            // Generate attachment metadata (thumbnails, etc.)
            $attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
            wp_update_attachment_metadata( $attachment_id, $attachment_metadata );

            // Store the uploaded file info
            $uploaded_files[] = $uploaded_file['url'];
        }

        // If all uploads are successful, return the success message
        if ( count( $uploaded_files ) === count( $files['name'] ) ) {
            wp_send_json_success( array( 'file_urls' => $uploaded_files ) );
        }

    } else {
        wp_send_json_error( array( 'message' => 'No files uploaded' ) );
    }

    wp_die(); // Always call wp_die() after an AJAX request
}
add_action( 'wp_ajax_iiu_upload_image', 'iiu_handle_file_upload' ); // For logged-in users

// Create the JavaScript to handle the file upload and redirect to media library (in the same PHP file)
function iiu_enqueue_inline_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Trigger the upload process when files are selected
            $('#iiu-file-input').on('change', function() {
                var files = $(this)[0].files; // Get all selected files
                var form_data = new FormData(); // Create FormData object

                // Append each file to form data
                for (var i = 0; i < files.length; i++) {
                    form_data.append('files[]', files[i]);
                }

                form_data.append('action', 'iiu_upload_image'); // The action hook
                form_data.append('nonce', iiu_ajax_obj.nonce); // Include nonce for security

                // Send the AJAX request
                $.ajax({
                    url: iiu_ajax_obj.ajaxurl,
                    type: 'POST',
                    data: form_data,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            // If all uploads are successful, redirect to Media Library
                            window.location.href = 'upload.php'; // Redirect to the Media Library page
                        } else {
                            // If failed, display the error message
                            $('#iiu-upload-status').html('<p>Error: ' + response.data.message + '</p>');
                        }
                    },
                    error: function() {
                        $('#iiu-upload-status').html('<p>An error occurred during the upload.</p>');
                    }
                });
            });
        });
    </script>
    <?php
}
add_action( 'admin_footer', 'iiu_enqueue_inline_script' );

