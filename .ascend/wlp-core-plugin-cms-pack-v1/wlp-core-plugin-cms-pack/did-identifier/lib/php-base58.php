<?php

// Autoload the custom base32 library (adjusted for your custom path)
require 'christian-riesen--base32-vendor/autoload.php';

use Base32\Base32;

// Example Ed25519 public key in hexadecimal (32 bytes)
$publicKeyHex = '6f1c67d6beaf28b67e35d1c29dbcf5f0b674b6c882833b57d89e043a8b14d7db';

// Convert the public key from hexadecimal to binary
$publicKeyBinary = hex2bin($publicKeyHex);

// Base32 encode the public key
$base32Encoded = Base32::encode($publicKeyBinary);

// Stellar address has 'G' prefix, add that prefix to the encoded address
$stellarAddress = 'G' . $base32Encoded;

// Output the resulting Stellar address
echo "Stellar Address: " . $stellarAddress . "\n";
