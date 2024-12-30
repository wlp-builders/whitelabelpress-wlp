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
    </div>
    <?php

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_repo_update'])) {
        
        $obj = [
            'repo__search' => [$_POST['search']]
        ];

        $repo = 'http://wlp41.local/';
        $headers = ['DID' => 'did:wlp:wlp42.local#sig'];
        $response = didql_repo_send_request($repo, $obj, $headers);


        // Display response
        echo '<div class="notice notice-info">';
        echo '<p>Response:</p>';
        echo '<pre>' . esc_html(print_r($response, true)) . '</pre>';
        echo '</div>';
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

