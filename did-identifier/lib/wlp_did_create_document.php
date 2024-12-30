<?php
require_once 'wlp_did.php';
require_once 'wlp256.php';
require_once 'php-encodings.php';
require_once 'php-generateSecp256k1Keypair.php';
require_once 'WLP256Signature2024.php';
require_once 'wlp_anti_spam_pow.php';

function wlp_did_create_document($domain, $fileName) {
  // Generate encryption keys 
  $box_keys1 = wlp_did_generate_box_keys();
  
  // Generate signing keys 
  $sign_keys1 = wlp_did_generate_sign_keys();

  // donate Ed25599 keypair for stellar, solana, ex.
  $sign_keys2 = wlp_did_generate_sign_keys();

  // donate-b Secp256k1 keypair for btc,eth, ex.
  $sign_keys3 = generateSecp256k1Keypair();
  $didDocument = wlp_did_create_document_full($box_keys1['public'], $sign_keys1['public'], $sign_keys2['public'],$sign_keys3['public'],$sign_keys1['secret'],$domain, $fileName);

  
  return [
    "didDocument"=>$didDocument,
      'e2e_secret_base64'=>rtrim(base64_encode($box_keys1['secret']),'='),
      'sig_secret_base64'=>rtrim(base64_encode($sign_keys1['secret']),'='),
      'donate_secret_base64'=>rtrim(base64_encode($sign_keys2['secret']),'='),
      'donate_b_secret_base64'=>rtrim(base64_encode($sign_keys3['secret']),'='),
    ];
}
  
/*
output keys description:
            "sig" => "Used for signing, authentication, and assertion purposes. The 'sig' private key must stay on the server.",
            "e2e" => "Used for receiving encrypted messages. The 'e2e' private key may be removed and stored locally."
*/
// $publicKeyForEncryption base64, $publicKeyForSigning base64, $domain example.com, $fileName = './did.json';
  function wlp_did_create_document_full($publicKeyForEncryption, $publicKeyForSigning,$publicKeyForDonations,$publicKeyForDonationsB,$secretKeyForSigning, $domain, $fileName) {
    // Use the domain as the controller
    $controllerId = "https://" . rtrim($domain, "/");
    $did = 'did:wlp:'.$domain;

    $donate_encodings = convertBinaryPublicKeyToBases($publicKeyForDonations);
    $donateB_encodings = convertBinaryPublicKeyToBases($publicKeyForDonationsB);
    // Construct the DID Document
    $didDocument = [
        "@context" => "https://www.w3.org/ns/did/v1",
        "id" => $did,
        "created" => time(),
        "latestVersion" => $controllerId.'/.well-known/did.json',
        "verificationMethod" => [
            [
                "id" => $did."#sig",
                "type" => "Ed25519VerificationKey2018",
                "publicKeyBase64" => rtrim(base64_encode($publicKeyForSigning),'=')
            ],
            [
                "id" => $did."#e2e",
                "type" => "X25519KeyAgreementKey2019",
                "publicKeyBase64" => rtrim(base64_encode($publicKeyForEncryption),'='),
                "encryption_algorithm" => "crypto_box",
            ],
            [
                "id" => $did."#donate",
                "type" => "Ed25519VerificationKey2018",
                "publicKeyBase64" => rtrim(base64_encode($publicKeyForDonations),'='),
                "publicKeyBase58" => $donate_encodings['base58'],
                "publicKeyBase32" => $donate_encodings['base32'],
              //$donate_encodings
            ],
            [
                "id" => $did."#donate-b",
                "type" => "Secp256k1VerificationKey2018",
                "publicKeyBase64" => rtrim(base64_encode($publicKeyForDonationsB),'='),
                "publicKeyBase58" => $donateB_encodings['base58'],
                "publicKeyBase32" => $donateB_encodings['base32'],
              //$donate_encodings
            ],
      //$publicKeyForDonationsB
        ]
    ];

    // prepare proof by signing the did document without "proof" key
    $input = json_encode($didDocument);
    $sig_result_obj = WLP256Signature2024($input,$did."#sig",$secretKeyForSigning);
    $created = $sig_result_obj['payload']['created'];
    $signed = $sig_result_obj['signed'];

    // Generate proof of work - wlp_anti_spam_pow
    $pow = generateProofOfWork(json_encode($didDocument),'WLP');
    $pow["type"] = "WLP512Hash2024";
    
    // add proof
    $didDocument['proof'] = [
      "type" => "WLP256Signature2024",
      "created"=> $created,
      "verificationMethod"=>$did."#sig",
      "signatureValue"=>($signed),
      "pow"=>$pow,
    ];

    
    
    // Convert the DID Document to JSON format
    $didDocumentJson = json_encode($didDocument, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if ($didDocumentJson === false) {
        throw new Exception("Error encoding DID Document to JSON: " . json_last_error_msg());
    }

    // Save the JSON encoded DID Document to the specified file
    if (file_put_contents($fileName, $didDocumentJson) === false) {
        throw new Exception("Error writing DID Document to file: $fileName");
    }

    return ($didDocument);
}


/*
// test
try {

    $domain = "wlp1.local";                                      // Your domain
    $dir='/var/www/wlp1.local/';
    mkdir($dir.'/.well-known');
    $fileName = $dir."/.well-known/did.json";                     // Output file name
    $result = wlp_did_create_document($domain, $fileName);
    var_dump($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
//*/
