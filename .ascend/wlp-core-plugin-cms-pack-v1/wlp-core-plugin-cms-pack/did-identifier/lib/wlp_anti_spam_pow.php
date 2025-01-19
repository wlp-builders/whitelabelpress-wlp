<?php


require_once 'christian-riesen--base32-vendor/autoload.php';
use Base32\Base32;

function verifyProofOfWork($input,$nonce,$startsWith='WLP') {
  $data = $input . $nonce;
  
  // hash 5 times (sha3-512, sha2-512,sha3-512, sha2-512,sha3-512)
  $hash = hash('sha3-512',hash('sha512',hash('sha3-512',hash('sha512',hash('sha3-512',$data)))));
  $base32Encoded = (Base32::encode(hex2bin($hash)));
 
  // Check if the first two bytes of the hash are 0x00
  return startsWithCheck($base32Encoded,$startsWith);     
}

function generateProofOfWork($input,$startsWith='WLP') {
    // Start with a nonce (random value)
    $nonce = 0;

    // Loop to generate hashes until the first two bytes are 0
    while (true) {
        // Concatenate the input with the nonce to create a unique string
        $data = $input . $nonce;

        // hash 5 times (sha3-512, sha2-512,sha3-512, sha2-512,sha3-512)
        $hash = hash('sha3-512',hash('sha512',hash('sha3-512',hash('sha512',hash('sha3-512',$data)))));
        $base32Encoded = (Base32::encode(hex2bin($hash)));
 
        // Check if the first two bytes of the hash are 0x00
        if (startsWithCheck($base32Encoded,$startsWith)) {
            return [
                'nonce' => $nonce,
                'hash' => rtrim($base32Encoded,'='),
            ];
        }

        // Increment the nonce and try again
        $nonce++;
    }
}

function startsWithCheck($string,$startsWith="WLP") {
    // Use substr and comparison to check if the string starts with "WLP"
    return substr($string, 0, 3) === $startsWith;
}

/*
// Input string (e.g., "challenge")
$input = time();

// Generate proof of work - wlp_anti_spam_pow
$result = generateProofOfWork($input);

// Output the nonce and hash that satisfies the proof of work
echo "result:\n";
var_dump($result);
echo "Nonce: " . $result['nonce'] . "\n";
echo "Hash: " . $result['hash'] . "\n";

// Easy verification
$result = verifyProofOfWork($input,$result['nonce']);
var_dump($result);

// double check wrong nonce
$result = verifyProofOfWork($input,$result['nonce']+1);
var_dump($result);

// double check wrong input
$result = verifyProofOfWork($input.'x',$result['nonce']);
var_dump($result);
//*/