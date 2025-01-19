<?php

function download_and_validate_zip($url, $savePath, $data = [], $headers = []) {
    // Convert data to JSON if provided
    $jsonData = !empty($data) ? json_encode($data) : null;

    // Open the file for writing
    $fp = fopen($savePath, 'wb');
    if (!$fp) {
        die('Failed to open file for writing: ' . $savePath);
    }
  
  
    // Initialize cURL session
    $ch = curl_init($url);

    // Ensure cURL session initializes correctly
    if (!$ch) {
        fclose($fp);
        die('Failed to initialize cURL.');
    }

    // Set cURL options to handle the file download with POST data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);      // Return the response as a string
    curl_setopt($ch, CURLOPT_POST, true);                // Use POST method
    if ($jsonData) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Attach the POST data if available
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);      // Set custom headers
    curl_setopt($ch, CURLOPT_FILE, $fp);                 // Write directly to file
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);      // Follow redirects if any
    curl_setopt($ch, CURLOPT_HEADER, false);             // Don't include the headers in the output
    curl_setopt($ch, CURLOPT_FAILONERROR, true);         // Fail on HTTP error codes

    // Execute the cURL session
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        fclose($fp); // Close file handle
        unlink($savePath); // Remove the partial file if download fails
        die('Error downloading file: ' . curl_error($ch));
    }

    // Get HTTP status code
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  

  
    // Check if the request was successful
    if ($status_code != 200) {
        fclose($fp); // Close file handle
        //unlink($savePath); // Remove the partial file if HTTP error
        die('Request failed with status code: ' . $status_code);
    }

    // Close cURL session and file handle
    curl_close($ch);
    fclose($fp);

    // Validate if the file is a valid ZIP
    $zip = new ZipArchive();
    if ($zip->open($savePath) !== true) {
        //unlink($savePath); // Remove invalid ZIP file
        die('The downloaded file is not a valid ZIP file.');
    }

    // Close the ZIP archive
    $zip->close();

    echo "File downloaded and validated successfully as a ZIP file at: $savePath";
}

/*
// Example usage:
$fileUrl = 'http://wlp1.local'; // Replace with your actual URL
$savePath = '/home/neil/Downloads/didql-all-in-one-seo-pack.4.7.3.zip';
$data = [
    'repo__downloadOne' => ['value1',2],
];
$headers = [
    'DID: did:wlp:wlp1.local#sig'
];


// Call the function
download_and_validate_zip($fileUrl, $savePath, $data, $headers);
//*/
