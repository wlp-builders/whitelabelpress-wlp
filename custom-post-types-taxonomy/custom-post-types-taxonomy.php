<?php
/**
 * Plugin Name: Custom Post Types and Taxonomies Creator
 * Description: A single-page plugin to create and delete custom post types and taxonomies in WLP.
 * Version: 1.8
 * Author: Neil
 * License: GPL
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register admin menu
add_action('admin_menu', 'cpt_tc_admin_menu');
function cpt_tc_admin_menu() {
    add_menu_page(
        'Custom Types',
        'Custom Types',
        'manage_options',
        'cpt-tc-admin',
        'cpt_tc_admin_page',
        'dashicons-admin-tools',
        20
    );
}

// Load saved post types and taxonomies on init
//add_action('init', 'cpt_tc_load_saved_data');
add_action('after_setup_theme', 'cpt_tc_load_saved_data', 5); // Priority 5 to run earlier
function cpt_tc_load_saved_data() {
    $saved_post_types = get_option('cpt_tc_post_types', []);
    foreach ($saved_post_types as $post_type => $args) {
        register_post_type($post_type, $args);
    }

    $saved_taxonomies = get_option('cpt_tc_taxonomies', []);
    foreach ($saved_taxonomies as $taxonomy => $args) {
        register_taxonomy($taxonomy, $args['object_type'], $args['args']);
    }
}

// Render admin page
function cpt_tc_admin_page() {
    // Handle form submission for post types and taxonomies
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpt_tc_nonce']) && wp_verify_nonce($_POST['cpt_tc_nonce'], 'cpt_tc_action')) {
        if (!empty($_POST['custom_post_type_name'])) {
            cpt_tc_save_post_type($_POST);
        }

        if (!empty($_POST['custom_taxonomy_name'])) {
            cpt_tc_save_taxonomy($_POST);
        }

        if (!empty($_POST['remove_post_type'])) {
            cpt_tc_remove_post_type($_POST['remove_post_type']);
        }

        if (!empty($_POST['remove_taxonomy'])) {
            cpt_tc_remove_taxonomy($_POST['remove_taxonomy']);
        }
    }

    // Fetch all registered post types (including built-in ones) for taxonomy association
    $post_types = get_post_types([], 'objects');
    $saved_post_types = get_option('cpt_tc_post_types', []);
    $saved_taxonomies = get_option('cpt_tc_taxonomies', []);
    ?>
    <div class="wrap">
        <h1>Custom Post Types & Taxonomies Creator</h1>

        <h2>Create or Update Custom Post Type</h2>
        <p>Enter a name for the post type (e.g., "Products", "Votes", etc.). Labels will be generated automatically based on the name.</p>

        <form method="POST">
            <?php wp_nonce_field('cpt_tc_action', 'cpt_tc_nonce'); ?>

            <label for="custom_post_type_name">Post Type Name (e.g., "Products"): </label>
            <input type="text" id="custom_post_type_name" name="custom_post_type_name" required>

            <button type="submit" class="button button-primary">Create/Update Post Type</button>
        </form>

        <hr>

        <h2>Create or Update Custom Taxonomy</h2>
        <p>Enter a name for the taxonomy. Labels will be generated automatically based on the name. You must associate at least one post type.</p>

        <form method="POST">
            <?php wp_nonce_field('cpt_tc_action', 'cpt_tc_nonce'); ?>

            <label for="custom_taxonomy_name">Taxonomy Name (e.g., "Product Categories"): </label>
            <input type="text" id="custom_taxonomy_name" name="custom_taxonomy_name" required>

            <label for="associated_post_types">Select Associated Post Types: </label>
            <select id="associated_post_types" name="associated_post_types[]" multiple required>
                <?php foreach ($post_types as $post_type): ?>
                    <option value="<?php echo esc_attr($post_type->name); ?>">
                        <?php echo esc_html($post_type->labels->singular_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button button-primary">Create/Update Taxonomy</button>
        </form>

        <hr>

        <h2>Remove Post Types and Taxonomies</h2>
        <p>Select a post type or taxonomy to remove.</p>

        <!-- Form to remove a post type -->
        <form method="POST">
            <?php wp_nonce_field('cpt_tc_action', 'cpt_tc_nonce'); ?>

            <label for="remove_post_type">Remove Post Type: </label>
            <select id="remove_post_type" name="remove_post_type">
                <option value="">Select Post Type</option>
                <?php foreach ($saved_post_types as $post_type => $args): ?>
                    <option value="<?php echo esc_attr($post_type); ?>"><?php echo esc_html($args['labels']['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button button-secondary">Remove Post Type</button>
        </form>

        <hr>

        <!-- Form to remove a taxonomy -->
        <form method="POST">
            <?php wp_nonce_field('cpt_tc_action', 'cpt_tc_nonce'); ?>

            <label for="remove_taxonomy">Remove Taxonomy: </label>
            <select id="remove_taxonomy" name="remove_taxonomy">
                <option value="">Select Taxonomy</option>
                <?php foreach ($saved_taxonomies as $taxonomy => $args): ?>
                    <option value="<?php echo esc_attr($taxonomy); ?>"><?php echo esc_html($args['args']['labels']['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button button-secondary">Remove Taxonomy</button>
        </form>

    </div>
    <?php
}

// Save custom post type to the database
function cpt_tc_save_post_type($data) {
    $post_type_name = sanitize_text_field($data['custom_post_type_name']);
    $plural_label = ucfirst($post_type_name); // Singular label based on the input


    // Handle edge case for words that don't pluralize naturally (like "bus" -> "buses")
    if (substr($post_type_name, -3) === 'ses') {
    $singular_label = rtrim($post_type_name, 'es'); // If ends with "ies", replace it with "y"
} elseif (substr($post_type_name, -3) === 'ies') {
    $singular_label = rtrim($post_type_name, 'ies') . 'y'; // If ends with "ies", replace it with "y"
} elseif (substr($post_type_name, -1) === 's') {
    $singular_label = rtrim($post_type_name, 's'); // If ends with "s", remove the "s" to make it singular
} else {
    $singular_label = $post_type_name; // If it doesn't end in "s" or "ies", assume it's already singular
}

    $labels = [
        'name' => $singular_label,
        'singular_name' => $singular_label,
        'add_new' => 'Add New',
        'add_new_item' => "Add New $singular_label",
        'edit_item' => "Edit $singular_label",
        'new_item' => "New $singular_label",
        'view_item' => "View $singular_label",
        'view_items' => "View $plural_label",
        'search_items' => "Search $plural_label",
        'not_found' => "No $plural_label Found",
        'not_found_in_trash' => "No $plural_label Found in Trash",
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'supports' => ['title', 'editor', 'thumbnail'],
    ];

    $post_types = get_option('cpt_tc_post_types', []);
    $post_types[$post_type_name] = $args;
    update_option('cpt_tc_post_types', $post_types);
}

// Save custom taxonomy to the database
function cpt_tc_save_taxonomy($data) {
    $taxonomy_name = sanitize_text_field($data['custom_taxonomy_name']);
    $plural_label = ucfirst($taxonomy_name); // Singular label based on the input

    // Handle edge case for words that don't pluralize naturally (like "bus" -> "buses")
    if (substr($taxonomy_name, -3) === 'ses') {
        $singular_label = rtrim($taxonomy_name, 'es'); // If ends with "ies", replace it with "y"
    } elseif (substr($taxonomy_name, -3) === 'ies') {
        $singular_label = rtrim($taxonomy_name, 'ies') . 'y'; // If ends with "ies", replace it with "y"
    } elseif (substr($taxonomy_name, -1) === 's') {
        $singular_label = rtrim($taxonomy_name, 's'); // If ends with "s", remove the "s" to make it singular
    } else {
        $singular_label = $taxonomy_name; // If it doesn't end in "s" or "ies", assume it's already singular
    }

    $labels = [
        'name' => $plural_label,
        'singular_name' => $singular_label,
        'search_items' => "Search $plural_label",
        'all_items' => "All $plural_label",
        'edit_item' => "Edit $singular_label",
        'update_item' => "Update $singular_label",
        'add_new_item' => "Add New $singular_label",
        'new_item_name' => "New $singular_label Name",
        'menu_name' => $plural_label,
    ];

    $associated_post_types = array_map('sanitize_text_field', $data['associated_post_types']);

    $args = [
        'labels' => $labels,
        'public' => true,
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
    ];

    $taxonomies = get_option('cpt_tc_taxonomies', []);
    $taxonomies[$taxonomy_name] = [
        'args' => $args,
        'object_type' => $associated_post_types,
    ];
    update_option('cpt_tc_taxonomies', $taxonomies);
}

// Remove a custom post type
function cpt_tc_remove_post_type($post_type) {
    $post_types = get_option('cpt_tc_post_types', []);
    if (isset($post_types[$post_type])) {
        unset($post_types[$post_type]);
        update_option('cpt_tc_post_types', $post_types);
    }
    // Optionally deregister the post type
    unregister_post_type($post_type);
}

// Remove a custom taxonomy
function cpt_tc_remove_taxonomy($taxonomy) {
    $taxonomies = get_option('cpt_tc_taxonomies', []);
    if (isset($taxonomies[$taxonomy])) {
        unset($taxonomies[$taxonomy]);
        update_option('cpt_tc_taxonomies', $taxonomies);
    }
    // Optionally deregister the taxonomy
    unregister_taxonomy($taxonomy);
}

// Next Load Custom Fields
require_once __DIR__.'/pages/fields.php';
