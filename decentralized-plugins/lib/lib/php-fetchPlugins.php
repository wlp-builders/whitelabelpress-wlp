<?php
/**
 * Fetches and validates plugin data from a given URL.
 *
 * @param string $url The URL to fetch the plugin data from.
 * @return array An associative array containing 'plugins' (the validated plugins) and 'domain' (the extracted domain from the URL).
 * @throws Exception if there is an error fetching the data or if validation fails.
 */
function fetchPlugins($url) {
  
    // Required keys that must be present in each plugin
    $requiredKeys = [
        'Name',
        'Version',
        'Description',
        'Repository',
        'Checksum-Sha256',
        'License',
        'Author',
        'Contact',
    ];

    // Keys to check for existence
    $allKeysToCheck = [
        'Name',
        'Version',
        'Description',
        'Repository',
        'Updates',
        'Checksum-Sha256',
        'License',
        'Author',
        'Contact',
        'Icon-File',
        'Meta-Data',
        'Cover-File',
    ];

    // Initialize cURL session
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set a timeout for the request

    // Execute cURL and fetch the content
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check if the request was successful
    if ($httpCode !== 200) {
        throw new Exception("Error fetching plugins.txt: HTTP code $httpCode");
    }

    // Process the full text response to extract plugin data
    $pluginsData = extractPluginsFromText($response, $requiredKeys, $allKeysToCheck,$url);
    return $pluginsData;
}


/**
 * Extracts plugins from the given text input.
 *
 * @param string $text The text data containing plugin information.
 * @param array $requiredKeys The keys that must be present in each plugin.
 * @param array $allKeysToCheck The keys to validate against for each plugin entry.
 * @return array An array of validated plugins.
 * @throws Exception if the text size exceeds the limit or if validation fails.
 */
function extractPluginsFromText($text, $requiredKeys, $allKeysToCheck,$url) {
    $plugins = [];
    $currentPlugin = [];
    // Split the text into individual plugin entries using two newlines as a separator
    $pluginEntries = preg_split('/\n\s*\n/', trim($text));

    // Process each plugin entry
    foreach ($pluginEntries as $entry) {
        $lines = explode("\n", trim($entry));
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue; // Skip empty lines
            }

            // Split the line into key and value
            list($key, $value) = explode(': ', $line, 2);
            $key = trim($key);
            if(strpos($key,'Name')) {
              list($dir, $value2) = explode(' ', $key, 2);
              $key = 'Name'; 
              //var_dump(["key"=>$key]);
              $currentPlugin["Directory"] = trim($dir);
            }
          
            // Only add if the key is supported
            if (in_array($key, $allKeysToCheck)) {
                $currentPlugin[trim($key)] = trim($value);
            }
        }

        // Validate the completed plugin entry
        validatePlugin($currentPlugin, $requiredKeys);

        // Handle Meta-Data separately if it exists
        if (array_key_exists('Meta-Data', $currentPlugin)) {
            $metaData = json_decode($currentPlugin['Meta-Data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON in Meta-Data: " . json_last_error_msg());
            }
            // Replace string with parsed object/array
            $currentPlugin['Meta-Data'] = $metaData; // Store parsed JSON as an associative array
        }

        $currentPlugin['Source'] = $url;
        $plugins[] = $currentPlugin; // Add the validated plugin to the array
        $currentPlugin = []; // Reset for the next plugin
    }

    return $plugins; 
}

/**
 * Validates a single plugin entry to ensure it contains all required keys.
 *
 * @param array $plugin The plugin data to validate.
 * @param array $requiredKeys The required keys that must be present in the plugin.
 * @throws Exception if any required keys are missing.
 */
function validatePlugin($plugin, $requiredKeys) {
    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $plugin)) {
            throw new Exception("Missing required key: $key");
        }
    }
}


/*
// Example usage good
try {  
    $url = 'http://localhost:8080/plugins.txt'; // Use your local URL
    $newValueObj = fetchPlugins($url);
    var_dump($newValueObj);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

// Example usage
try {
    $url = 'http://localhost:8080/plugins-missing-sha.txt'; // Use your local URL
    $plugins = fetchPlugins($url);
    print_r($plugins); // Output the validated plugins array
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
//*/