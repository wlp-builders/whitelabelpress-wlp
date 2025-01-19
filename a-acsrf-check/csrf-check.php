<?php
/**
 * A simple plugin to prevent CSRF attacks by checking the referer for POST requests, with special handling for /wp-admin and excluding admin-ajax.php.
 * Version: 1.4
 * Author: Neil
 * License: Spirit of Time 1.0
 */
/**
 * Handle CSRF protection for POST requests
 */
function handle_csrf_with_referrer() {
    // Only apply to non-cli POST requests
	if (php_sapi_name() != 'cli' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_SERVER['HTTP_DID'])) {
                            //log_message('[csrf check]: skipped HTTP DID');
                            return;  // Skip the CSRF checks for DID.json clients

        }

        if (isset($_SERVER['HTTP_WLP_AUTHORIZATION'])) {
                //log_message('[csrf check]: skipped WLP AUTH');
            return;  // Skip the CSRF checks for headless apps
        }

        // Check for admin-ajax.php
        if (strpos($_SERVER['REQUEST_URI'], 'admin-ajax.php') !== false) {
        //log_message('[csrf check]: admin-ajax.php');
        check_site_url();  // Validate referer for admin-ajax.php
        } 
        // Check for /wp-admin area
        elseif (strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== false) {
        //log_message('[csrf check]: /wp-admin');
            check_site_url_with_admin(); // Validate referer for admin urls
        }
        // Check for other POST requests
        else {
        //log_message('[csrf check]: else');
            check_site_url();  // Validate referer for general POST requests
        }
    } else {
    	//log_message('[csrf check]: skipped');
    }
}

/**
 * Check if the referer matches the site URL for general requests and admin-ajax.php
 */
function check_site_url() {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $site_url = site_url(); // Get the full site URL (including scheme and domain)
    
    if (strpos($referer, $site_url) !== 0) {
	die('Invalid referer. CSRF attempt detected.');
    }
}

/**
 * Check if the referer matches the site URL specifically for /wp-admin requests
 */
function check_site_url_with_admin() {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $site_url = site_url(); // Get the full site URL (including scheme and domain)
    
    if (strpos($referer, $site_url.'/wp-admin') !== 0) {
	die('Invalid referer for wp-admin request. CSRF attempt detected.');
    }
}

handle_csrf_with_referrer();


