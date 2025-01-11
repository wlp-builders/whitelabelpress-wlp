<?php
/**
 * Plugin Name: DIDQL Repo Client
 * Description: ..
 * Version: 1.1.0
 * Author: Your Name
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Add admin menu page
function didql_repo_client_menu() {
    add_menu_page(
        'Repo Client',
        'Repo Client',
        'manage_options',
        'didql-repo-client',
        'didql_repo_client_menu_page'
    );
}
    
    
require_once __DIR__.'/lib/php-didql_repo_download_zip_operation.php';
    
add_action('admin_menu', 'didql_repo_client_menu');

// Display admin page content
function didql_repo_client_menu_page() {
    ?>
    <div class="wrap">
    <?php if (isset($_POST['send_didql_message'])): ?>
                <div class="notice notice-success">
                    <p>Message Sent!</p>
                </div>
                <div class="notice notice-info">
                    <p>Package Response:</p>
                    <pre><?php echo esc_html($_POST['didql_response']); ?></pre>
                </div>
            <?php endif; ?>

        <h1>Package Updater</h1>
        <form method="post">
            <input type="text" placeholder="search.." name="search" />
            <?php submit_button('Send Message', 'primary', 'send_repo_update'); ?>
        </form>

        <h1>repo__downloadOner</h1>

        <form method="post">
            <input type="text" placeholder="search.." name="search" />
            <?php submit_button('Send Message', 'primary', 'repo__downloadOne'); ?>
        </form>

        
    </div>
    <?php

      // Handle form submission
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repo__downloadOne'])) {
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'repositories';

        $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        foreach($results as $repo) {
            $obj = [
                'repo__downloadOne' => [$_POST['search']]
            ];

            $repo = $repo['repo_url'];
            $headers = ['DID' => 'did:wlp:wlp42.local#sig'];
            $response = didql_repo_send_request($repo, $obj, $headers);

$data = [
    'repo__downloadOne' => ['Private Document Tracker']
];
$install_path = ABSPATH; //'/var/www/wlp146.local';
$type = 'plugins';
$download = 'Private Document Tracker';

// Execute the function
$result = didql_repo_download_zip_operation($repo, $data, $headers, $install_path, $type, $download);



            // Display response
            echo '<div class="notice notice-info">';
            echo '<p>Response '.$repo.':</p>';
            echo '<pre>' . ($result) . ' characters</pre>';
            echo '</div>';
        }
        if(count($results) == 0) {
            echo '<div class="notice notice-info">';
            echo '<p>No repositories added yet.:</p>';
            echo '</div>';
        }
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_repo_update'])) {
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'repositories';

        $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        foreach($results as $repo) {
            $obj = [
                'repo__search' => [$_POST['search']]
            ];

            $repo = $repo['repo_url'];
            $headers = ['DID' => 'did:wlp:wlp42.local#sig'];
            $response = didql_repo_send_request($repo, $obj, $headers);


            // Display response
            echo '<div class="notice notice-info">';
            echo '<p>Response '.$repo.':</p>';
            echo '<pre>' . esc_html(print_r($response, true)) . '</pre>';
            echo '</div>';
        }
        if(count($results) == 0) {
            echo '<div class="notice notice-info">';
            echo '<p>No repositories added yet.:</p>';
            echo '</div>';
        }
    }
}

// Function to send HTTP POST request
function didql_repo_send_request($url, $data, $headers) {
    $args = [
        'headers' => $headers,
        'body' => wp_json_encode($data),
        'timeout' => 15,
        'method' => 'POST',
        'blocking' => true,
    ];

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        return $response->get_error_message();
    }

    return wp_remote_retrieve_body($response);
}

