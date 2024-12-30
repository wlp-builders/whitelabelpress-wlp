<?php
/**
 * Plugin Name: Sitemaps
 * Description: Core plugin for Sitemaps
 * Version: 1.0
 * Author: Neil
 */

// Hook to the admin menu action
//add_action( 'admin_menu', 'custom_rest_api' );
custom_sitemaps();
function custom_sitemaps() {
    // skip this menu if set to disabled
    if(defined('WLP_CORE_PLUGINS_DISABLE')) {
        $bname = basename(__DIR__);
        if(in_array($bname, WLP_CORE_PLUGINS_DISABLE)) {
            return false;
        }
    }

// require_once all dependencies
require_once ABSPATH . WPINC . '/sitemaps.php';
require_once ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps.php';
require_once ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps-index.php';
require_once ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps-provider.php';
require_once ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps-registry.php';
require_once ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps-renderer.php';
require_once ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps-stylesheet.php';
require_once ABSPATH . WPINC . '/sitemaps/providers/class-wp-sitemaps-posts.php';
require_once ABSPATH . WPINC . '/sitemaps/providers/class-wp-sitemaps-taxonomies.php';
require_once ABSPATH . WPINC . '/sitemaps/providers/class-wp-sitemaps-users.php';
}