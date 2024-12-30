<?php
require_once 'wlp_did.php';
/*
Functions to Sign and verify using the wlp256 JWT method (introducing new method)
  the default method for signing commands, communicating with plugin repos and other decentralized apps

  wlp256_sign($payload,$privateKey,$expirationTime=300)
  wlp256_verify($jwt, $publicKey)

  To generate a keypair you can use generateP512Keypair()
*/

function wlp256_sign($payload, $privateKey, $expirationTime=300) {

    if($expirationTime != false) {
      $expirationTime = time() + $expirationTime; // Expiration time in seconds from now
      // Add the expiration claim to the payload
      $payload['exp'] = $expirationTime;
    }
  
    // Header
    $header = [
        'alg' => 'wlp256',
        'typ' => 'JWT'
    ];

    // Encode Header
    $encodedHeader = _wlp_jwt_wlp256_base64UrlEncode(json_encode($header));

    // Encode Payload
    $encodedPayload = _wlp_jwt_wlp256_base64UrlEncode(json_encode($payload));

    $signature = '';
  
    // wlp256 uses SHA3-512 hash
    $signatureHash = hash('sha3-512',"$encodedHeader.$encodedPayload");
    $signature = wlp_did_sign_message($signatureHash, $privateKey);

    // Base64Url encode the signature
    $encodedSignature = _wlp_jwt_wlp256_base64UrlEncode($signature);

    // Concatenate the parts
    $jwt = "$encodedHeader.$encodedPayload.$encodedSignature";

    return $jwt;
}

function wlp256_verify($jwt, $publicKey) {
    // just in case trim the payload
    $jwt = trim($jwt);
  
    // Split the JWT into its parts
    $parts = explode('.', $jwt);

    if (count($parts) !== 3) {
        return false; // Invalid JWT format
    }

    list($encodedHeader, $encodedPayload, $encodedSignature) = $parts;
    // Decode the payload
    $decodedPayload = json_decode(_wlp_jwt_wlp256_base64UrlDecode($encodedPayload), true);

    $decodedSignature = _wlp_jwt_wlp256_base64UrlDecode($encodedSignature);
  
    // wlp256 uses SHA3-512 hash
    $signatureHash = hash('sha3-512',"$encodedHeader.$encodedPayload");
    
    // Verify the wlp_did
    $isValid = wlp_did_verify_signature($signatureHash, $decodedSignature, $publicKey);

    if (!$isValid) {
        //var_dump('invalid sig: '.json_encode($isValid));
        return false; // Signature does not match
    }

    // Check if the token is expired
    if (isset($decodedPayload['exp']) && time() > $decodedPayload['exp']) {
        //var_dump('token expired');
        return false; // Token has expired
    }

    return $decodedPayload;
}


// Helper function to base64Url decode data
function _wlp_jwt_wlp256_base64UrlDecode($data) {
    $padding = strlen($data) % 4;
    if ($padding) {
        $data .= str_repeat('=', 4 - $padding);
    }

    return base64_decode(strtr($data, '-_', '+/'));
}

// Helper function to base64Url encode data
function _wlp_jwt_wlp256_base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/*
// Test
$sign_keys1 = wlp_did_generate_sign_keys();
//var_dump($sign_keys1);

// Sign and verify JWT
$token = wlp256_sign(['user_id' => 1], $sign_keys1['secret']);  // 5min expiration default
var_dump($token);

// 1. This one should return the user obj with exp in int
$tokenv1 = wlp256_verify($token, $sign_keys1['public']);
var_dump($tokenv1);
var_dump(json_encode(["1"=>$tokenv1])); // Should output longer exp than 124

// 2. This one should be false
$tampered_token = 'x'.$token;
$tokenv2 = wlp256_verify($tampered_token, $sign_keys1['public']);
var_dump(json_encode(["2"=>$tokenv2])); // Should output longer exp than 124

// 3. Just double check that exp is not overwritable by user
$token3 = wlp256_sign(['user' => 'example', "exp" => 123], $sign_keys1['secret'], 3000);  // 3000 seconds expiration
$tokenv3 = wlp256_verify($token3, $sign_keys1['public']);
var_dump(json_encode(["3"=>$tokenv3])); // Should output longer exp than 124

// 4. Verify EXP (expiration time set to time()-1)
$token4 = wlp256_sign(['user' => 'example', "exp" => 124], $sign_keys1['secret'], -1);  // 4000 seconds expiration
$tokenv4 = wlp256_verify($token4, $sign_keys1['public']);
var_dump(json_encode(["4"=>$tokenv4])); // Should output longer exp than 124//*/