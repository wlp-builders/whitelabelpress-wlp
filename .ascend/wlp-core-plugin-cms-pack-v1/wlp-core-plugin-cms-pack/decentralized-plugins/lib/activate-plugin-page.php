<?php
/**
 * Plugin Name: Upload and Activate Plugin
 * Description: A plugin to upload a .zip file, extract it, and activate the plugin automatically.
 * Version: 1.0
 * Author: Your Name
 */

function uap_enqueue_scripts() {
    // Enqueue any necessary styles or scripts here, if needed.
}
add_action('admin_enqueue_scripts', 'uap_enqueue_scripts');

function uap_plugin_page() {
    ?>
    <div class="wrap">
        <h1>Upload and Activate Plugin</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="plugin_zip" accept=".zip" required>
            <input type="submit" name="submit" value="Upload and Activate">
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['plugin_zip'])) {
            $result_message = uap_activate_uploaded_plugin($_FILES['plugin_zip']);
            echo "<div class='result-message'>$result_message</div>";
        }
        ?>
    </div>
    <?php
}

function uap_activate_uploaded_plugin($uploaded_file) {
    // Include necessary WordPress file for plugin activation
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    
    $messages = []; // Array to hold success/error messages

    // Check if the uploaded file is a valid .zip file
    $file_type = wp_check_filetype($uploaded_file['name']);
    if ($file_type['ext'] !== 'zip') {
        return "Error: Please upload a valid .zip file.";
    }

    // Define the temporary upload path in wp-content/tmp
    $temp_dir = WP_CONTENT_DIR . '/tmp';
    if (!is_dir($temp_dir)) {
        if (!mkdir($temp_dir, 0755, true)) {
            return "Error: Unable to create a temporary directory in wp-content.";
        }
    }

    // Move uploaded file to the wp-content/tmp directory
    $temp_file_path = $temp_dir . '/' . basename($uploaded_file['name']);
    if (!move_uploaded_file($uploaded_file['tmp_name'], $temp_file_path)) {
        return "Error: Failed to move the uploaded file to the temporary directory.";
    }
    $messages[] = "Successfully uploaded the .zip file to the temporary directory.";

    // Initialize ZipArchive and try to open the .zip file
    $zip = new ZipArchive;
    $res = $zip->open($temp_file_path);

    // Enhanced error handling to capture actual error code
    if ($res !== TRUE) {
        unlink($temp_file_path); // Clean up temp file
        return "Error: Failed to open the .zip file (Error code: $res).";
    }
    $messages[] = "Successfully opened the .zip file.";

    // Ensure there's a single top-level folder in the .zip
    $plugin_folder = null;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $file_info = $zip->statIndex($i);
        $file_name = $file_info['name'];

        // Determine the top-level folder name
        if (!$plugin_folder) {
            $plugin_folder = explode('/', $file_name)[0];
            $messages[] = "Successfully detected root folder: '$plugin_folder'";
        }

        // Confirm all files are within the single top-level folder
        if (strpos($file_name, $plugin_folder . '/') !== 0) {
            $zip->close();
            unlink($temp_file_path); // Clean up temp file
            return "Error: Invalid plugin structure. Ensure the .zip contains a single top-level folder.";
        }
    }

    // Define the extraction path in the plugins directory
    $extract_to = WP_PLUGIN_DIR . '/' . $plugin_folder;
    if (!is_dir($extract_to) && !mkdir($extract_to, 0755, true)) {
        $zip->close();
        unlink($temp_file_path); // Clean up temp file
        return "Error: Could not create directory for plugin extraction.";
    }

    // Extract files from the root of the detected top-level folder
    $zip->extractTo($extract_to, $zip->getNameIndex(0) . '*'); // Extract the contents of the detected root folder
    $zip->close();
    $messages[] = "Successfully extracted the plugin files to the plugins directory.";

    // Clean up the uploaded .zip file after extraction
    unlink($temp_file_path);

    // Check for the main plugin file with a valid header
    $plugin_files = glob($extract_to . '/*.php');
    if (!$plugin_files) {
        return "Error: No PHP files found in the plugin folder '$extract_to'. Please check the contents of the uploaded .zip file.";
    }

    // Assume the first PHP file found is the main plugin file
    $main_plugin_file = $plugin_folder . '/' . basename($plugin_files[0]);

    // Check for a valid plugin header
    $plugin_data = get_plugin_data($extract_to . '/' . basename($plugin_files[0]));
    if (empty($plugin_data['Name'])) {
        return "Error: The plugin does not have a valid header. Please check the plugin file.";
    }

    // Activate the plugin
    $result = activate_plugin($main_plugin_file);

    // Check for errors during activation
    if (is_wp_error($result)) {
        return "Error activating plugin: " . $result->get_error_message();
    } else {
        $messages[] = "Successfully activated the plugin.";
    }

    // Return all messages as a string
    return implode('<br>', $messages);
}

function uap_add_menu() {
    add_submenu_page(
        'plugins.php',            // Parent slug (this is for the Plugins menu)
        'Upload',         // Page title
        'Upload',         // Menu title
        'manage_options',        // Capability
        'upload-plugin',         // Menu slug
        'uap_plugin_page'        // Callback function
    );
}
add_action('admin_menu', 'uap_add_menu');


