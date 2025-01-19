<?php
/**
 * Plugin Name: Widgets
 * Description: Core plugin for widgets
 * Version: 1.0
 * Author: Neil
 */

// Hook to the admin menu action
//add_action( 'admin_menu', 'custom_rest_api' );
custom_widgets();
function custom_widgets() {
    // skip this menu if set to disabled
    if(defined('WLP_CORE_PLUGINS_DISABLE')) {
        $bname = basename(__DIR__);
        if(in_array($bname, WLP_CORE_PLUGINS_DISABLE)) {
            return false;
        }
    }

// require_once all dependencies
require_once ABSPATH . WPINC . '/widgets.php';
require_once ABSPATH . WPINC . '/class-wp-widget.php';
require_once ABSPATH . WPINC . '/class-wp-widget-factory.php';
}