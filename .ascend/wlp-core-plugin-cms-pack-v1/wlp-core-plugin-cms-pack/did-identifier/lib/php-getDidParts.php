<?php
  function getDidParts($did) {
    // Split the DID string by ':'
    $parts = explode(':', $did);

    // Check if the DID is well-formed
    if (count($parts) < 3) {
        return ['error' => 'Invalid DID format'];
    }

    $domainParts = explode('#',$parts[2]);

    // Return the components of the DID
    return [
        'method' => $parts[0],  // 'did'
        'method-specific' => $parts[1],  // 'web'
        'domain' => $domainParts[0],  // 'mydomain.com'
        'fragment' => $domainParts[1],  // 'mydomain.com'
    ];
}

function checkDidParts($did,$domain,$methodName) {
  $didArray = getDidParts($did);
  if(array_key_exists('error',$didArray)) return $didArray;

  if($didArray['domain'] != $domain) {
    return ['error'=>'Domain does not match '.$domain];
  }

  if($didArray['method-specific'] != $methodName) {
    return ['error'=>'Method name does not match '.$methodName];
  }

  return $didArray;
}

/*
// Test
// Example usage
$did = "did:web:mydomain.com";
$didParts = getDidParts($did);

// Print the parts
print_r($didParts);

// Example usage
$did = "did:wlp:other.mydomain.com";
$didParts = getDidParts($did);

// Print the parts
print_r($didParts);

$did = "did:web:mydomain.com";
$didParts = checkDidParts($did,'mydomain.com','wlp');

// Print the parts
print_r($didParts);

// Example usage
$did = "did:wlp:other.mydomain2.com";
$didParts = checkDidParts($did,'other.mydomain2.com','wlp');

// Print the parts
print_r($didParts);

// Example usage
$did = "did:wlp:other.mydomain3.com";
$didParts = checkDidParts($did,'other.mydomain2.com','wlp');

// Print the parts
print_r($didParts);
//*/