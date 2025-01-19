<?php
/*
Plugin Name: Add Repository Plugin
Description: A plugin to add repository URLs to the database.
Version: 1.6
Author: Neil
License: Spirit of Time 1.0
*/

// Hook to create the database table on plugin activation
add_action('init', 'add_repository_create_table');
function add_repository_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'repositories';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        repo_url varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Admin menu to view repositories
add_action('admin_menu', 'add_repository_admin_menu');
function add_repository_admin_menu() {
    add_menu_page(
        'Repositories',
        'Repositories',
        'manage_options',
        'repositories',
        'add_repository_admin_page',
        'dashicons-database',
        20
    );
}

function add_repository_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'repositories';

    // Handle addition
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repo_url']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'add_repository_nonce')) {
        $new_repo_url = esc_url($_POST['repo_url']);
        
        if (!empty($new_repo_url)) {
            $wpdb->insert(
                $table_name,
                ['repo_url' => $new_repo_url],
                ['%s']
            );
            echo '<div class="notice notice-success">Repository added successfully!</div>';
        } else {
            echo '<div class="notice notice-error">Please provide a valid repository URL.</div>';
        }

        // Redirect to current page without parameters
        //wp_redirect(remove_query_arg('repo_url'));
        //exit;
    }

    // Handle deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'delete_repository_nonce')) {
        $id = intval($_POST['id']);
        $wpdb->delete($table_name, ['id' => $id], ['%d']);
        echo '<div class="notice notice-success">Repository deleted successfully!</div>';
    }

    // Check for the repo_url parameter
    if (isset($_GET['repo_url'])) {
        $popup_repo_url = esc_url($_GET['repo_url']);
        ?>
        <div id="add-repo-popup" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 9999; display: flex; justify-content: center; align-items: center;">
            <div style="background: #fff; padding: 20px; border-radius: 8px; max-width: 500px; width: 100%; text-align: center;">
                <h2>Confirm Add Repository</h2>
                <p>Do you want to add the following repository?</p>
                <p><strong><?php echo esc_html($popup_repo_url); ?></strong></p>
                <form method="POST" action="/wp-admin/admin.php?page=repositories">
                    <?php wp_nonce_field('add_repository_nonce'); ?>
                    <input type="hidden" name="repo_url" value="<?php echo esc_attr($popup_repo_url); ?>">
                    <button type="submit" style="margin-right: 10px;">Confirm</button>
                    <button type="button" onclick="document.getElementById('add-repo-popup').style.display='none';">Cancel</button>
                </form>
            </div>
        </div>
        <?php
    }

    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    ?>
    <div class="wrap">
        <h1>Repositories</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Repository URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row['id']); ?></td>
                        <td><?php echo esc_html($row['repo_url']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <?php wp_nonce_field('delete_repository_nonce'); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo esc_attr($row['id']); ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this repository?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2><a href="https://did-apps.wlp.builders/repositories">Find Open Repositories</a></h2>

        <hr />

        <h2>Manually Add Repository</h2>
        <form method="POST" action="">
            <?php wp_nonce_field('add_repository_nonce'); ?>
            <label for="repo_url">Repository URL:</label>
            <input type="url" id="repo_url" name="repo_url" required>
            <button type="submit">Add Repository</button>
        </form>
    </div>
    <?php
}
