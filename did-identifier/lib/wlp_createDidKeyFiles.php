<?php

// Function to create PHP files for each base64-encoded secret
function createDidKeyFiles($docReturn, $directory) {
    // Ensure the directory exists
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true); // Create directory if it doesn't exist
    }

    // Function to create individual PHP file with the base64-encoded secret
    function createKeyFile($filename, $keyValue, $directory) {
        $content = "<?php\n" . "define('$filename', '$keyValue');\n";
        file_put_contents($directory . '/' . $filename . '.php', $content);
    }

    // Create PHP files for each base64-encoded secret in the docReturn array
    if (isset($docReturn['e2e_secret_base64'])) {
        createKeyFile('e2e_secret_base64', $docReturn['e2e_secret_base64'], $directory);
    }
    if (isset($docReturn['sig_secret_base64'])) {
        createKeyFile('sig_secret_base64', $docReturn['sig_secret_base64'], $directory);
    }
    if (isset($docReturn['donate_secret_base64'])) {
        createKeyFile('donate_secret_base64', $docReturn['donate_secret_base64'], $directory);
    }
    if (isset($docReturn['donate_b_secret_base64'])) {
        createKeyFile('donate_b_secret_base64', $docReturn['donate_b_secret_base64'], $directory);
    }

    echo "PHP key files have been created successfully in the directory '$directory'!";
}

/*
// test
// Example docReturn array (replace with actual function or data)
$docReturn = [
    'e2e_secret_base64' => base64_encode('your_box_key1_secret_value_here'),
    'sig_secret_base64' => base64_encode('your_sign_key1_secret_value_here'),
    'donate_secret_base64' => base64_encode('your_sign_key2_secret_value_here'),
    'donate_b_secret_base64' => base64_encode('your_sign_key3_secret_value_here'),
];

// Directory to save the key files
$directory = './wlp-keys/';

// Call the function to create the PHP files
createKeyFiles($docReturn, $directory);
*/