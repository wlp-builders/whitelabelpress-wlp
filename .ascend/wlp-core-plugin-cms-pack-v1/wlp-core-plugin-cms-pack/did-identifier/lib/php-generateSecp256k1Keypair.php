<?php

function generateSecp256k1Keypair($returnPem = false) {
    // Ensure the OpenSSL extension is available
    if (!extension_loaded('openssl')) {
        throw new Exception('OpenSSL extension is not enabled.');
    }

    // Create the secp256k1 keypair
    $config = [
        'curve_name' => 'secp256k1',
        'private_key_type' => OPENSSL_KEYTYPE_EC,
    ];

    $keyResource = openssl_pkey_new($config);

    if ($keyResource === false) {
        throw new Exception('Failed to generate key pair: ' . openssl_error_string());
    }

    // Extract the private key
    openssl_pkey_export($keyResource, $privateKeyPem);

    // Extract the public key
    $keyDetails = openssl_pkey_get_details($keyResource);
    if (!isset($keyDetails['key'])) {
        throw new Exception('Failed to extract public key.');
    }
    $publicKeyPem = $keyDetails['key'];

    if ($returnPem) {
        return [
            'secret' => $privateKeyPem,
            'public' => $publicKeyPem,
        ];
    }

    // Convert keys to byte strings
    $privateKeyBytes = base64_decode(preg_replace('/-----.*?-----|\n/', '', $privateKeyPem));
    $publicKeyBytes = base64_decode(preg_replace('/-----.*?-----|\n/', '', $publicKeyPem));

    return [
        'secret' => $privateKeyBytes,
        'public' => $publicKeyBytes,
    ];
}

function signMessageSecp256k1($privateKeyPem, $message) {
    $signature = '';
    $result = openssl_sign($message, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);
    if (!$result) {
        throw new Exception('Failed to sign the message.');
    }
    return $signature;
}

function verifySignatureSecp256k1($publicKeyPem, $message, $signature) {
    $result = openssl_verify($message, $signature, $publicKeyPem, OPENSSL_ALGO_SHA256);
    return $result === 1;
}

/*
try {
    // Generate key pair
    $keypairPem = generateSecp256k1Keypair(true);
    $keypairBytes = generateSecp256k1Keypair(false);
    var_dump(["keypairPem"=>$keypairPem,"keypairBytes"=>$keypairBytes]);
    // Extract PEM keys
    $privateKeyPem = $keypairPem['secret'];
    $publicKeyPem = $keypairPem['public'];

    // Extract byte keys
    $privateKeyBytes = $keypairBytes['secret'];
    $publicKeyBytes = $keypairBytes['public'];

    // Test message
    $message = "Test message for signing";

    // Sign and verify with original PEM keys
    $signaturePem = signMessageSecp256k1($privateKeyPem, $message);
    $isValidPem = verifySignatureSecp256k1($publicKeyPem, $message, $signaturePem);

    echo "Using Original PEM Keys:\n";
    echo "Signature: " . bin2hex($signaturePem) . "\n";
    echo "Verification: " . ($isValidPem ? "Valid" : "Invalid") . "\n\n";

    // Convert byte keys back to PEM format for signing and verification
    $privateKeyPemFromBytes = "-----BEGIN PRIVATE KEY-----\n" . chunk_split(base64_encode($privateKeyBytes), 64, "\n") . "-----END PRIVATE KEY-----\n";
    $publicKeyPemFromBytes = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($publicKeyBytes), 64, "\n") . "-----END PUBLIC KEY-----\n";

    // Sign and verify with converted byte keys
    $signatureBytes = signMessageSecp256k1($privateKeyPemFromBytes, $message);
    $isValidBytes = verifySignatureSecp256k1($publicKeyPemFromBytes, $message, $signatureBytes);

    echo "Using Converted Byte Keys:\n";
    echo "Signature: " . bin2hex($signatureBytes) . "\n";
    echo "Verification: " . ($isValidBytes ? "Valid" : "Invalid") . "\n";

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
//*/
