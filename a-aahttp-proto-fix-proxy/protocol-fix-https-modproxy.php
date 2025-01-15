<?php
// protocol fix for when you're behind mod_proxy, ex. using Podman container
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    // Set $_SERVER['HTTPS'] to 'on' to emulate HTTPS environment
    $_SERVER['HTTPS'] = 'on';
    
    // Adjust other related variables as needed
    $_SERVER['SERVER_PORT'] = 443; // Standard HTTPS port
    $_SERVER['REQUEST_SCHEME'] = 'https'; // Define scheme if your app uses it
}


