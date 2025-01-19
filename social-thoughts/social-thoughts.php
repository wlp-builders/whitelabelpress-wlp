<?php
/**
 * Plugin Name: Social Thoughts
 * Description: Adds a custom post type for Social Thoughts with no title, using the post ID, and a 500-character description.
 * Version: 1.1
 * Author: Neil
 * License: LGPL
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register the 'Social Thoughts' custom post type
function human_thoughts_post_type() {
    // cp,wp,other compatible
    $args = array(
        'labels' => array(
            'name'                  => 'Human Thoughts',
            'singular_name'         => 'Human Thought',
            'menu_name'             => 'Thoughts',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Thought',
            'edit_item'             => 'Edit Thought',
            'new_item'              => 'New Thought',
            'view_item'             => 'View Thought',
            'search_items'          => 'Search Thoughts',
            'not_found'             => 'No Human Thoughts found',
            'not_found_in_trash'    => 'No Human Thoughts found in Trash',
        ),
        'public'            => true,
        'show_in_menu'      => true,
        'supports'          => array( 'editor' ), // Only support the editor (no title or other fields)
        'has_archive'       => true,
        'rewrite'           => array( 'slug' => 'human-thoughts' ),
        'show_in_rest'      => true, // Enable the REST API for the custom post type
        'menu_icon'         => 'dashicons-format-quote',
    );

    // cp,wp,other compatible
    register_post_type( 'human_thought', $args );
}

// cp,wp,other compatible
add_action( 'init', 'human_thoughts_post_type' );

// Automatically populate the post title with the post ID (when creating/editing the post)
function set_human_thought_title( $data, $postarr ) {
    // Check if the post type is "human_thought"
    if ( 'human_thought' === $data['post_type'] ) {
        // Set the title to the post ID
        $data['post_title'] = 'Thought #' . $postarr['ID'];
    }

    return $data;
}

// cp,wp,other compatible
add_filter( 'wp_insert_post_data', 'set_human_thought_title', 10, 2 );

// Limit the description (post content) to 500 characters
function limit_human_thought_description_length( $content ) {
    if ( get_post_type() === 'human_thought' ) {
        // Trim content to 500 characters
        if ( strlen( $content ) > 500 ) {
            $content = substr( $content, 0, 500);
        }
    }
    return $content;
}

// cp,wp,other compatible
add_filter( 'content_save_pre', 'limit_human_thought_description_length' );

// Optional: Add a custom message to the post editor for Social Thoughts (indicating the limit)
function human_thought_editor_message() {
    global $post;
    if ( 'human_thought' === $post->post_type ) {
        echo '<div style="color: red; font-weight: bold;">Please limit your description to 500 characters.</div>';
    }
}

// cp,wp,other compatible
add_action( 'edit_form_after_title', 'human_thought_editor_message' );

