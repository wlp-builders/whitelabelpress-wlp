<?php
/**
 * Plugin Name: Plugins.txt Importer
 * Description: A plugin to import plugins.txt information and save it to the database.
 * Version: 1.1
 * Author: Your Name
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once 'lib/php-fetchPlugins.php';

function pti_create_trusted_sources_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'trusted_source_urls'; // New table for trusted source URLs
    $charset_collate = $wpdb->get_charset_collate();

    // Create table if it does not exist
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        source_url varchar(255) NOT NULL UNIQUE,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('plugins_loaded', 'pti_create_trusted_sources_table');

function pti_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pluginstxt'; // Table name without _data
    $charset_collate = $wpdb->get_charset_collate();

    // Create table if it does not exist
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        Directory varchar(255) NOT NULL,
        Name varchar(255) NOT NULL,
        Version varchar(50) NOT NULL,
        Description text NOT NULL,
        Repository varchar(255) NOT NULL,
        Checksum_Sha256 varchar(255) NOT NULL,
        Updates varchar(255) NOT NULL,
        Author varchar(255) NOT NULL,
        Contact varchar(255) DEFAULT '',
        Icon_File varchar(255) DEFAULT '',
        License varchar(50) DEFAULT '',
        Meta_Data json DEFAULT NULL,
        Source varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('plugins_loaded', 'pti_create_table');

// Function to handle form submission and import plugins.txt data
function pti_import_plugins_txt() {
    global $wpdb;

    if (isset($_POST['submit'])) {
        $trusted_source_url = sanitize_text_field($_POST['trusted_source_url']);
        
        // Check if the trusted source URL already exists
        $table_name = $wpdb->prefix . 'trusted_source_urls';
        $existing_url = $wpdb->get_var($wpdb->prepare("SELECT source_url FROM $table_name WHERE source_url = %s", $trusted_source_url));

        if (!$existing_url) {
            // Insert the new trusted source URL
            $wpdb->insert($table_name, ['source_url' => $trusted_source_url]);

            // Fetch the plugins.txt file from the trusted source URL
            $json_data = fetchPlugins($trusted_source_url);

            // Save JSON data to database
            $table_name = $wpdb->prefix . 'pluginstxt';
            foreach ($json_data as $plugin) {
                $wpdb->insert($table_name, [
                    'Directory' => $plugin['Directory'],
                    'Name' => $plugin['Name'],
                    'Version' => $plugin['Version'],
                    'Description' => $plugin['Description'],
                    'Repository' => $plugin['Repository'],
                    'Checksum_Sha256' => $plugin['Checksum-Sha256'],
                    'Updates' => $plugin['Updates'],
                    'Author' => $plugin['Author'],
                    'Contact' => $plugin['Contact'],
                    'Icon_File' => $plugin['Icon-File'],
                    'License' => $plugin['License'],
                    'Meta_Data' => json_encode($plugin['Meta-Data']),
                    'Source' => $trusted_source_url // Use the trusted source URL
                ]);
            }

            echo '<div class="notice notice-success">Data imported and saved successfully!</div>';
        } else {
            echo '<div class="notice notice-error">This trusted source URL already exists!</div>';
        }
    }
}

// Add admin menu for the plugin
function pti_add_admin_menu() {
    add_submenu_page(
        'plugins.php',            // Parent slug (this is for the Plugins menu)
        'Update & Search',
        'Update & Search',
        'manage_options',
        'plugins-txt-importer',
        'pti_admin_page'
    );
}
add_action('admin_menu', 'pti_add_admin_menu');

function pti_admin_page() {
    ?>
    <div class="wrap">
        <h1>Import Plugins.txt</h1>
        <form method="post">
            <label for="trusted_source_url">Trusted Source URL:</label>
            <input type="text" id="trusted_source_url" name="trusted_source_url" required>
            <br><br>
            <input type="submit" name="submit" class="button button-primary" value="Import">
        </form>

        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
            <div style="flex: 1; margin-right: 20px;">
                <h2>Trusted Source URLs</h2>
                <?php pti_display_trusted_source_urls(); // Display trusted source URLs ?>
            </div>
            <div style="flex: 1; margin-left: 20px;">
                <h2>Imported Plugins</h2>
                <?php pti_display_imported_plugins(); // Display the imported plugins ?>
            </div>
        </div>
    </div>
    <?php
    pti_import_plugins_txt(); // Handle form submission
}

function pti_display_trusted_source_urls() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'trusted_source_urls';

    // Retrieve all trusted source URLs
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A);

    if (empty($results)) {
        echo '<p>No trusted source URLs added yet.</p>';
        return;
    }

    foreach ($results as $row) {
        echo '<div class="trusted-source-entry">';
        echo esc_html($row['source_url']);
        echo ' <form method="post" style="display:inline;">
                <input type="hidden" name="remove_trusted_source_id" value="' . esc_attr($row['id']) . '">
                <input type="submit" class="button button-small" name="remove_trusted_source" value="Remove">
                </form>';
        echo '</div>';
    }

    // Handle removal of a trusted source URL
    if (isset($_POST['remove_trusted_source'])) {
        pti_remove_trusted_source();
    }
}

// Function to handle removal of trusted source URLs
function pti_remove_trusted_source() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'trusted_source_urls';
    $id = intval($_POST['remove_trusted_source_id']);

    // Remove the trusted source URL
    $wpdb->delete($table_name, ['id' => $id]);

    echo '<div class="notice notice-success">Trusted source URL removed successfully!</div>';
}

// Function to display the imported plugins in a div format
function pti_display_imported_plugins() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pluginstxt';

    // Retrieve all records from the table
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A);

    if (empty($results)) {
        echo '<p>No plugins imported yet.</p>';
        return;
    }

    foreach ($results as $row) {
        echo '<div class="plugin-entry">';
        echo '<h4>' . esc_html($row['Name']) . '</h4>';
        echo '<p><strong>Directory:</strong> ' . esc_html($row['Directory']) . '<br>';
        echo '<strong>Version:</strong> ' . esc_html($row['Version']) . '<br>';
        echo '<strong>Description:</strong> ' . esc_html($row['Description']) . '<br>';
        echo '<strong>Repository:</strong> <a href="' . esc_url($row['Repository']) . '">' . esc_html($row['Repository']) . '</a><br>';
        echo '<strong>Checksum:</strong> ' . esc_html($row['Checksum_Sha256']) . '<br>';
        echo '<strong>Updates:</strong> <a href="' . esc_url($row['Updates']) . '">' . esc_html($row['Updates']) . '</a><br>';
        echo '<strong>Author:</strong> ' . esc_html($row['Author']) . '<br>';
        echo '<strong>Contact:</strong> ' . esc_html($row['Contact']) . '<br>';
        echo '<strong>Icon File:</strong> <img src="' . esc_url($row['Icon_File']) . '" alt="Icon" style="width: 50px; height: auto;"><br>';
        echo '<strong>License:</strong> ' . esc_html($row['License']) . '<br>';
        echo '<strong>Meta Data:</strong> ' . esc_html($row['Meta_Data']) . '<br>';
        echo '<strong>Source:</strong> <a href="' . esc_url($row['Source']) . '">' . esc_html($row['Source']) . '</a>';
        echo '</p>';
        echo '</div><hr>'; // Add a horizontal line between plugin entries
    }
}


