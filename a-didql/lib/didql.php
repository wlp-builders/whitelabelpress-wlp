<?php
/*
License: Spirit of Time 1.0
Author: Neil
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $didql;

require_once __DIR__.'/kissql.php';

// Initialize DidQl 
$didql = new KissQl(); // kissql is the general purpose framework for securely exposing functions

require_once __DIR__.'/../../did-identifier/lib/wlp_did_document_retrieve_and_verify.php';

/*
The main auth function (this is what makes DidQL so easy)
1. HTTP: Retrieve the users DID Document according to did:wlp method (GET http(s)://domain + .well-known/did.json)
2. Verify the DID document (Check Signature + Check WLPHash512 Proof of Work for Anti Spam)
3. Set the route to 'did_user' or 'guest' and decode the did_user data (if valid)
*/
$didql->setAuthFunction(function () {
    $did = $_SERVER['HTTP_DID'] ?? null; // ex did:wlp:wlp.local#sig
    if($did) {
        $didParts = getDidParts($did);
        $did_document_url = 'http://'.$didParts['domain'].'/.well-known/did.json'; // ex http://wlp.local/.well-known/did.json
        log_message('checking did_document_url '.$did_document_url);
        $did_document_check = wlp_did_document_retrieve_and_verify($did,$did_document_url);
        if($did_document_check['valid']) {
            return ['route' => 'did_user', 'decodedUser' => $did_document_check['did_user']];
        }
    }

    // if all else fails we return 'guest' route
    return ['route' => 'guest', 'decodedUser' => []];
});

function didql_execute() {
    global $didql;
    $did = $_SERVER['HTTP_DID'] ?? null; // ex did:wlp:wlp.local#sig
    log_message('didql execute: '.json_encode($didql->getRoutes()));
    if($did) {
        // Handle incoming POST request
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Only POST method is allowed']);
            exit;
        }

        // Get JSON body data
        $inputData = json_decode(file_get_contents('php://input'), true);

        // Check for function name and extract arguments in the new structure
        if (empty($inputData) || count($inputData) != 1) {
            $didql->execute(null, []); // return docs
        } else {
            // Extract function name and arguments from the body
            $funcName = key($inputData);  // Get the function name (key)
            $args = $inputData[$funcName]; // Get the function arguments (value)
            if (!$funcName) {
                http_response_code(400);
                echo json_encode(['error' => 'fn (function name) is required']);
                exit;
            }

            $didql->execute($funcName, $args);
        }

    }
}

