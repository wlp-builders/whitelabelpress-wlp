<?php

// Function to generate a keypair and return both public and secret keys
function wlp_did_generate_box_keys() {
    $keypair = sodium_crypto_box_keypair();
    return [
        'public' => sodium_crypto_box_publickey($keypair),
        'secret' => sodium_crypto_box_secretkey($keypair)
    ];
}

// Function to generate sign keys (used for signing and verifying)
function wlp_did_generate_sign_keys() {
    $sign_pair = sodium_crypto_sign_keypair();
    return [
        'public' => sodium_crypto_sign_publickey($sign_pair),
        'secret' => sodium_crypto_sign_secretkey($sign_pair)
    ];
}

// Function to encrypt a message
function wlp_did_encrypt_message($message, $secret_key1, $public_key2) {
    $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
    $encryption_key = sodium_crypto_box_keypair_from_secretkey_and_publickey($secret_key1, $public_key2);
    $encrypted = sodium_crypto_box($message, $nonce, $encryption_key);
    return ['encrypted' => $encrypted, 'nonce' => $nonce];
}

// Function to decrypt a message
function wlp_did_decrypt_message($encrypted, $nonce, $secret_key2, $public_key1) {
    $decryption_key = sodium_crypto_box_keypair_from_secretkey_and_publickey($secret_key2, $public_key1);
    return sodium_crypto_box_open($encrypted, $nonce, $decryption_key);
}

// Function to sign a message
function wlp_did_sign_message($message, $secret_key) {
    return sodium_crypto_sign($message, $secret_key);
}

// Function to verify a signed message
function wlp_did_verify_signature($message,$signed_message, $public_key) {
    $result = sodium_crypto_sign_open($signed_message, $public_key);
    if($result !== $message) {
      return false;
    }
  
    if($result) return true;
    else return false; 
}

// Function to decode the signature (returns the original message if signature is valid)
function wlp_did_decode_signature($signed_message, $public_key) {
    $result = sodium_crypto_sign_open('x'.$signed_message, $public_key);
    return $result;
}

/*
// Testing the functions with a simple echo

// Generate key pairs for encryption (box keys) for two persons
$box_keys1 = wlp_did_generate_box_keys();
$box_keys2 = wlp_did_generate_box_keys();

// Generate signing keys for two persons
$sign_keys1 = wlp_did_generate_sign_keys();
$sign_keys2 = wlp_did_generate_sign_keys();

// Encrypt a message for person 2
$message = 'hello there';
$encrypted_data = wlp_did_encrypt_message($message, $box_keys1['secret'], $box_keys2['public']);

// Print encrypted message
echo "Encrypted: " . base64_encode($encrypted_data['encrypted']) . "\n";

// Decrypt the message using person 2 secret and person 1 public key
$decrypted_message = wlp_did_decrypt_message($encrypted_data['encrypted'], $encrypted_data['nonce'], $box_keys2['secret'], $box_keys1['public']);

// Print decrypted message
echo "Decrypted: " . $decrypted_message . "\n";

// Person 1 signs the message using signing keys
$signed_message = wlp_did_sign_message($message, $sign_keys1['secret']);
echo "Signed Message: " . base64_encode($signed_message) . "\n";

// Person 2 verifies the signed message using Person 1's public signing key
$verified_message = wlp_did_verify_signature($message,$signed_message,$sign_keys1['public']);
echo "Verified Message: " . json_encode($verified_message) . "\n";
//*/

?>
