<?php

/**
 * Serves a specified ZIP file as a downloadable file.
 *
 * @param string $filePath The absolute path to the ZIP file.
 * @return void
 */
function serveZipFile($filePath) {
    // Check if the file exists
    if (!file_exists($filePath)) {
        die('File not found.');
    }

    // Get the filename from the file path
    $fileName = basename($filePath);

    // Set headers to serve the file as a download
    header('Content-Type: application/zip'); // Indicate the MIME type as a ZIP file
    header('Content-Disposition: attachment; filename="' . $fileName . '"'); // Specify the download file name
    header('Content-Length: ' . filesize($filePath)); // Set the content length for the file

    // Clear the output buffer to ensure clean file delivery
    flush();

    // Read and output the file to the user
    readfile($filePath);

    // Terminate the script after serving the file
    exit;
}

/*
// test
// Example usage
$filePath = '/home/neil/yoga/repos/easy-repo1/all-in-one-seo-pack.4.7.3.zip';
serveZipFile($filePath);
//*/

?>
