<?php
/**
 * Plugin Name: Wrapper for DID.JSON functionality
 * Description: A wrapper plugin to use DIDs. Lib itself is licensed under Spirit of Time 1.0.
 * Version: 1.1
 * Author: Neil
 * License: GPL
 */



require_once __DIR__.'/lib/wlp_did_create_document.php';
require_once __DIR__.'/lib/wlp_createDidKeyFiles.php';

// generate W3 compliant DID document with proof and proof of work
function did_id_generate_did() {

    // The path to the .well-known folder
    $well_known_path = ABSPATH . '.well-known/';
    
    // Check if the .well-known directory exists; if not, create it.
    if ( ! file_exists( $well_known_path ) ) {
        mkdir($well_known_path , 0777, true); // Create directory if it doesn't exist
    }

    // The path to the .well-known folder
    $did_path = ABSPATH . '.well-known/did.json';
    if ( ! file_exists( $did_path ) ) {
      $domain = $_SERVER['SERVER_NAME'];
      $docReturn = wlp_did_create_document($domain, $did_path);

      // save the private keys to secure php files
      createDidKeyFiles($docReturn, ABSPATH . 'wlp-keys');
    }

  
    return file_get_contents($did_path);
}



// helper function

function did_id_get_current_domain() {
  return $_SERVER["HTTP_HOST"];
}


add_action('init','did_id_generate_did'); // generate did.json if not exists
