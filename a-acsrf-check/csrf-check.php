<?php
/**
 * Plugin Name: CSRF Referrer Check with Site URL Validation
 * Description: A simple plugin to prevent CSRF attacks by checking the referer for POST requests, with special handling for /wp-admin and excluding admin-ajax.php.
 * Version: 1.1
 * Author: Your Name
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handle CSRF protection for POST requests
 */
function handle_csrf_with_referrer() {
    // Only apply to non-cli POST requests
	if (php_sapi_name() != 'cli' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_SERVER['HTTP_WLP_AUTHORIZATION'])) {
            return;  // Skip the CSRF checks for headless apps
        }

        // Check for admin-ajax.php
        if (strpos($_SERVER['REQUEST_URI'], 'admin-ajax.php') !== false) {
            check_site_url();  // Validate referer for admin-ajax.php
        } 
        // Check for /wp-admin area
        elseif (strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== false) {
            check_site_url_with_wp_admin(); // Validate referer for /wp-admin
        }
        // Check for other POST requests
        else {
            check_site_url();  // Validate referer for general POST requests
        }
    }
}

/**
 * Check if the referer matches the site URL for general requests and admin-ajax.php
 */
function check_site_url() {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $site_url = site_url(); // Get the full site URL (including scheme and domain)
    
    if (strpos($referer, $site_url) !== 0) {
        // Log the CSRF attempt (optional)
        error_log('CSRF detected: invalid referer ' . $referer);
        
        // Return a 403 Forbidden response
        wp_die(
            'Invalid referer. CSRF attempt detected.',
            'CSRF Protection',
            ['response' => 403]
        );
    }
}

/**
 * Check if the referer matches the site URL specifically for /wp-admin requests
 */
function check_site_url_with_wp_admin() {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $site_url = site_url(); // Get the full site URL (including scheme and domain)
    
    if (strpos($referer, $site_url) !== 0) {
        // Log the CSRF attempt (optional)
        error_log('CSRF detected: invalid referer for /wp-admin request: ' . $referer);
        
        // Return a 403 Forbidden response
        wp_die(
            'Invalid referer for /wp-admin request. CSRF attempt detected.',
            'CSRF Protection',
            ['response' => 403]
        );
    }
}

// Hook the CSRF check into WordPress's init action
add_action('init', 'handle_csrf_with_referrer');

