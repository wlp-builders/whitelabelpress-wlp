<?php
/**
 * Plugin Name: Decentralized Plugins
 * Description: A simple plugin to manage migration and display repository information.
 * Version: 1.8
 * Author: Neil
 * Author URI: https://yourwebsite.com
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}




// Include the necessary sub plugins
require_once (__DIR__) . '/../../../wp-load.php';
require_once (__DIR__) . '/lib/plugin-manager.php';
require_once (__DIR__) . '/lib/plugin-creator.php';
//require_once (__DIR__) . 'lib/pluginstxt-importer.php';  // This is a duplicate, so it can be removed
require_once (__DIR__) . '/lib/activate-plugin-page.php';

