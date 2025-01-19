<?php
/**
 * Plugin Name: Rest API
 * Description: Core plugin for Rest API
 * Version: 1.0
 * Author: Neil
 */

// Hook to the admin menu action
//add_action( 'admin_menu', 'custom_rest_api' );
custom_rest_api();
function custom_rest_api() {
    // skip this menu if set to disabled
    if(defined('WLP_CORE_PLUGINS_DISABLE')) {
        $bname = basename(__DIR__);
        if(in_array($bname, WLP_CORE_PLUGINS_DISABLE)) {
            return false;
        }
    }

// require_once all rest api dependencies
require_once ABSPATH . WPINC . '/embed.php';
require_once ABSPATH . WPINC . '/class-wp-embed.php';
require_once ABSPATH . WPINC . '/class-wp-oembed.php';
require_once ABSPATH . WPINC . '/class-wp-oembed-controller.php';
require_once ABSPATH . WPINC . '/http.php';
require_once ABSPATH . WPINC . '/class-wp-http.php';
require_once ABSPATH . WPINC . '/class-wp-http-streams.php';
require_once ABSPATH . WPINC . '/class-wp-http-curl.php';
require_once ABSPATH . WPINC . '/class-wp-http-proxy.php';
require_once ABSPATH . WPINC . '/class-wp-http-cookie.php';
require_once ABSPATH . WPINC . '/class-wp-http-encoding.php';
require_once ABSPATH . WPINC . '/class-wp-http-response.php';
require_once ABSPATH . WPINC . '/class-wp-http-requests-response.php';
require_once ABSPATH . WPINC . '/class-wp-http-requests-hooks.php';

require_once ABSPATH . WPINC . '/rest-api.php';
require_once ABSPATH . WPINC . '/rest-api/class-wp-rest-server.php';
require_once ABSPATH . WPINC . '/rest-api/class-wp-rest-response.php';
require_once ABSPATH . WPINC . '/rest-api/class-wp-rest-request.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-posts-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-attachments-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-post-types-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-post-statuses-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-revisions-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-autosaves-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-taxonomies-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-terms-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-menu-items-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-menus-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-menu-locations-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-users-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-comments-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-search-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-settings-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-themes-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-plugins-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-application-passwords-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-site-health-controller.php';
require_once ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-sidebars-controller.php';
require_once ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-meta-fields.php';
require_once ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-comment-meta-fields.php';
require_once ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-post-meta-fields.php';
require_once ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-term-meta-fields.php';
require_once ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-user-meta-fields.php';
require_once ABSPATH . WPINC . '/rest-api/search/class-wp-rest-search-handler.php';
require_once ABSPATH . WPINC . '/rest-api/search/class-wp-rest-post-search-handler.php';
require_once ABSPATH . WPINC . '/rest-api/search/class-wp-rest-term-search-handler.php';
require_once ABSPATH . WPINC . '/rest-api/search/class-wp-rest-post-format-search-handler.php';
}
