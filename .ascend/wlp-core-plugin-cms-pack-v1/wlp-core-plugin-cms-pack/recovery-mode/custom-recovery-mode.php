<?php
/**
 * Plugin Name: Recovery Mode
 * Description: Core plugin for Recovery Mode
 * Version: 1.0
 * Author: Neil
 */

// Hook to the admin menu action
//add_action( 'admin_menu', 'custom_rest_api' );
custom_recovery_mode();
function custom_recovery_mode() {
    // skip this menu if set to disabled
    if(defined('WLP_CORE_PLUGINS_DISABLE')) {
        $bname = basename(__DIR__);
        if(in_array($bname, WLP_CORE_PLUGINS_DISABLE)) {
            return false;
        }
    }

// require_once all dependencies
require_once ABSPATH . WPINC . '/class-wp-recovery-mode-cookie-service.php';
require_once ABSPATH . WPINC . '/class-wp-recovery-mode-key-service.php';
require_once ABSPATH . WPINC . '/class-wp-recovery-mode-link-service.php';
require_once ABSPATH . WPINC . '/class-wp-recovery-mode-email-service.php';
require_once ABSPATH . WPINC . '/class-wp-recovery-mode.php';
}