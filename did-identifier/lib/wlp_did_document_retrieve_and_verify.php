<?php
require_once 'php-getDidParts.php';
require_once 'WLP256Signature2024.php';

function wlp_did_document_retrieve_and_verify($didWitHashtag, $did_document_url) {
    // Fetch the JSON data
    $jsonData = file_get_contents($did_document_url);
    //var_dump($jsonData);
    if ($jsonData === false) {
        return ["error" => "Error fetching data from $did_document_url"];
    }

    // Decode the JSON into an associative array
    $data = json_decode($jsonData, true);

    if ($data === null) {
        return ["error" => "Error decoding JSON: " . json_last_error_msg()];
    }
  
    //var_dump($data);
    return parseAndVerifyDidDocument($data,$didWitHashtag);
}

// helper function
function parseAndVerifyDidDocument($did_document_data,$didWitHashtag) { 
  $proofSignature = $did_document_data['proof']['signatureValue'];

  // Double check if we use the right supported type
  if($did_document_data['proof']['type'] != "WLP256Signature2024") {
     throw new Exception('Unsupported proof type '.$did_document_data['proof']['type'].', expected WLP256Signature2024');
  }
  

  $verificationMethod;
  $verificationMethods = $did_document_data['verificationMethod'];
  foreach($verificationMethods as $v) {
      if($v['id'] === $didWitHashtag) {
        $verificationMethod = $v;
        // check here
        $decodedKey = base64_decode($v['publicKeyBase64']);
          
        // Clone the array and remove proof
        $clonedArray = $did_document_data;
        $keyToRemove = 'proof';
        unset($clonedArray[$keyToRemove]);
        
        // recompute signed hash according to WLP256Signature2024 specs (simply sha3-512 the  json encoded object without proof)
        $data_for_hash = json_encode($clonedArray);
        $hash = hash('sha3-512',$data_for_hash);

        // get public key 
        $publicKey = base64_decode($verificationMethod['publicKeyBase64']);
        
        if($v['type'] === 'Ed25519VerificationKey2018') {
          $valid = wlp256_verify($proofSignature, $publicKey);
          if(false == $valid || $valid['hash-sha3-512'] !== $hash) {
            return ["valid"=>false,"error" =>'Proof signature not valid' ,"did_user"=>$verificationMethod];
          }
        } else {
          throw new Error('unsupported verificationMethod type');
        }
        
        return ["valid"=>true,"did_user"=>$verificationMethod];
      } // end if
    } // end for

    // Return the modified data
    return ["valid"=>false,"did_user"=>$verificationMethod];
}

/*
// Example usage
$didWithHashtag = "did:wlp:wlp1.local#sig";
$fullUrl = "http://wlp1.local/.well-known/did.json";
$result = wlp_did_document_retrieve_and_verify($didWithHashtag, $fullUrl);
if (isset($result['error'])) {
    echo $result['error'] . "\n";
} else {
  // good
  var_dump(json_encode($result, JSON_PRETTY_PRINT) . "\n");
}
//*/