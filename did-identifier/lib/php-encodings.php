<?php

// cryptos for did.json
/// only ed25519 supported
//// bc initial public keys
//// and ease, libsodium

// Autoload the custom base32 library (adjusted for your custom path)
require_once 'christian-riesen--base32-vendor/autoload.php';
require_once('stephenhill--base58-vendor/autoload.php');
use Base32\Base32;

/*
// Example Ed25519 public key in hexadecimal (32 bytes)
$publicKeyHex = '5f1c67d4beaf30b67e36d1c29dbcf5f0b674b6c882833b57d89e043a8b14d7db';

// Convert the public key from hexadecimal to binary
$publicKeyBinary = hex2bin($publicKeyHex);
echo "Address: " . json_encode(convertBinaryPublicKeyToStellarAddrBase58($publicKeyBinary),JSON_PRETTY_PRINT). "\n";
//*/

function convertBinaryPublicKeyToBases($publicKeyBinary) {
$base58 = new StephenHill\Base58();

// Base58 encode the public key
$base58Encoded = $base58->encode($publicKeyBinary);
$base58Encoded = rtrim($base58Encoded, '=');

// Base32 encode the public key
$base32Encoded = Base32::encode($publicKeyBinary);
$base32Encoded = rtrim($base32Encoded, '=');
// Stellar address has 'G' prefix, add that prefix to the encoded address
$stellarAddress = 'G' . $base32Encoded;
$solanaAddress = $base58Encoded;//rtrim($base58->encode($publicKeyBinary),'=');

// Output the resulting Stellar address
return [
  "base32"=>$base32Encoded,
  "base58"=>$base58Encoded,
];
}