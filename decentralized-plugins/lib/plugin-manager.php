<?php
/*
Plugin Name: Plugin and Theme Manager
Description: Unified interface to manage plugins and themes with batch operations, activation, deactivation, switching, and deletion.
Version: 1.8
Author: Your Name
Author URI: https://yourwebsite.com
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

// Add admin menu
function pm_add_admin_menu() {
    add_submenu_page(
        'plugins.php',
        'Plugin Manager',
        'Plugin Manager',
        'manage_options',
        'plugin-manager',
        'pm_render_admin_page',
        100
    );
}
add_action('admin_menu', 'pm_add_admin_menu');

// Function to recursively delete a directory
function pm_recursive_delete_dir($dir) {
    if (!is_dir($dir)) return false;

    $items = array_diff(scandir($dir), array('.', '..'));
    foreach ($items as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            pm_recursive_delete_dir($path);
        } else {
            unlink($path);
        }
    }
    return rmdir($dir);
}

// Render the admin page
function pm_render_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Handle POST actions
    if (isset($_POST['action'], $_POST['item'], $_POST['type']) && check_admin_referer('pm_action')) {
        $item = sanitize_text_field(wp_unslash($_POST['item']));
        $type = sanitize_text_field(wp_unslash($_POST['type']));

        if ($type === 'plugin') {
            if ($_POST['action'] === 'activate') {
                activate_plugin($item);
                echo '<div class="notice notice-success"><p>Plugin activated successfully.</p></div>';
            } elseif ($_POST['action'] === 'deactivate') {
                deactivate_plugins($item);
                echo '<div class="notice notice-success"><p>Plugin deactivated successfully.</p></div>';
            } elseif ($_POST['action'] === 'delete') {
                $plugin_dir = WP_PLUGIN_DIR . '/' . dirname($item);
                if (pm_recursive_delete_dir($plugin_dir)) {
                    echo '<div class="notice notice-success"><p>Plugin deleted successfully.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Failed to delete the plugin.</p></div>';
                }
            }
        } elseif ($type === 'theme') {
            if ($_POST['action'] === 'switch') {
                switch_theme($item);
                echo '<div class="notice notice-success"><p>Theme switched successfully.</p></div>';
            } elseif ($_POST['action'] === 'delete') {
                $current_theme = wp_get_theme();
                if ($current_theme->get_stylesheet() !== $item) {
                    $theme_dir = WP_CONTENT_DIR . '/themes/' . $item;
                    if (pm_recursive_delete_dir($theme_dir)) {
                        echo '<div class="notice notice-success"><p>Theme deleted successfully.</p></div>';
                    } else {
                        echo '<div class="notice notice-error"><p>Failed to delete the theme.</p></div>';
                    }
                } else {
                    echo '<div class="notice notice-error"><p>You cannot delete the currently active theme. Please switch to another theme first.</p></div>';
                }
            }
        }
    }

    // Combine plugins and themes into a single list
    $plugins = get_plugins();
    $themes = wp_get_themes();

    $items = [];

    // Add plugins to the items list
    foreach ($plugins as $plugin_file => $plugin_data) {
        $items[] = [
            'type' => 'plugin',
            'name' => $plugin_data['Name'],
            'description' => $plugin_data['Description'],
            'file' => $plugin_file,
            'is_active' => is_plugin_active($plugin_file),
            'version' => $plugin_data['Version'],
            'author' => $plugin_data['Author'],
            'author_uri' => $plugin_data['AuthorURI'],
        ];
    }

    // Add themes to the items list
    foreach ($themes as $theme_slug => $theme_data) {
        $items[] = [
            'type' => 'theme',
            'name' => $theme_data->get('Name'),
            'description' => $theme_data->get('Description'),
            'file' => $theme_slug,
            'is_active' => (wp_get_theme()->get_stylesheet() === $theme_slug),
            'version' => $theme_data->get('Version'),
            'author' => $theme_data->get('Author'),
            'author_uri' => $theme_data->get('AuthorURI'),
        ];
    }

    // Sort items by activation status
    usort($items, function ($a, $b) {
        return $b['is_active'] <=> $a['is_active'];
    });

    // Render the table
    echo '<div class="wrap">';
    echo '<h1>Plugin Manager</h1>';
    echo '<p>You can use the wp-content/install folder to install plugins and themes. Simply put a folder or zip in the right subfolder.</p>';
    echo '<form method="post">';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th><input type="checkbox" id="select_all" /></th><th style="width:270px;">Name</th><th>Description</th></tr></thead>';
    echo '<tbody>';

    foreach ($items as $item) {
        $is_active = $item['is_active'];
        $type = $item['type'];
        $name = esc_html($item['name']);
        $file = esc_html($item['file']);
        $description = esc_html($item['description']);
        $version = esc_html($item['version']);
        $author = esc_html($item['author']);
        $author_uri = esc_url($item['author_uri']);
        $action_button = $type === 'theme' ? 'Switch' : ($is_active ? 'Deactivate' : 'Activate');
        $row_class = $is_active ? 'class="tr_active" style="background-color: #fcf0fc;"' : 'class="tr_inactive"';

        echo "<tr {$row_class}>";
        echo '<td><input type="checkbox" class="item_checkbox" name="selected_items[]" value="' . $file . '"></td>';
        echo "<td><strong>{$name}</strong><br><span>{$file}</span><br />";
        echo '<form method="post" style="display:inline;">';
        echo wp_nonce_field('pm_action', '_wpnonce', true, false);
        echo '<input type="hidden" name="item" value="' . $file . '">';
        echo '<input type="hidden" name="type" value="' . $type . '">';
        echo '<input type="hidden" name="action" value="' . ($is_active ? 'deactivate' : ($type === 'theme' ? 'switch' : 'activate')) . '">';
        echo '<input type="submit" class="button button-primary" value="' . $action_button . '">';
        echo '</form>';

        if (!$is_active || $type === 'theme') {
            echo ' ';
            echo '<form method="post" style="display:inline;">';
            echo wp_nonce_field('pm_action', '_wpnonce', true, false);
            echo '<input type="hidden" name="item" value="' . $file . '">';
            echo '<input type="hidden" name="type" value="' . $type . '">';
            echo '<input type="hidden" name="action" value="delete">';
            echo '<input type="submit" class="button button-secondary" value="Delete">';
            echo '</form>';
        }

        echo "</td>";
        echo "<td>{$description}<br>Version {$version} By <a href='{$author_uri}' target='_blank'>{$author}</a></td>";
        
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</form>';
    echo '</div>';
    echo "
    <style>

    .plugin_manager_title {
        padding: 10px 9px;
            white-space: nowrap;
            padding-right: 12px;
        
        }
        tr td {
        padding-top:12px!important;
        padding-bottom:21px!important;
        border-bottom:1px solid #d9d9d9;
        }
                .btn_activate {cursor:pointer;border:none;box-shadow:none;color: #a91bbe;background:none;padding:0;margin:0;}
                .btn_deactivate {cursor:pointer;border:none;box-shadow:none;color: #a91bbe;background:none;padding:0;margin:0;}
                .btn_delete {cursor:pointer;border:none;box-shadow:none;color: #b32d2e;background:none;padding:0;margin:0;}
        .plugin_manager_name { display: block;margin-bottom: .2em;font-size: 14px; font-weight: bold; } th:first-child,
                td:first-child {
                    width: 24px;
                text-align:center;
                } th:first-child input,td:first-child input {
            margin: 0 !important;
            padding: 0 !important;
        } 
            
 .tr_active       {
  background-color: #fcf0fc;
}
        .tr_active th:first-child, 
        .tr_active td:first-child {     
        border-left: 4px solid #f6aaff!important;
         }  
        .tr_inactive .plugin_manager_name { opacity:0.81;font-weight:normal}
    </style>
    ";

    // JavaScript for "Select All" checkbox
    ?>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select_all');
        const itemCheckboxes = document.querySelectorAll('.item_checkbox');

        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    });
    </script>
    <?php
}
