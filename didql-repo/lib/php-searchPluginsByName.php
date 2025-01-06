<?php

function searchPluginsByName($filePath, $plugin_name) {
    // Read the file content
    if (!file_exists($filePath)) {
        return "File not found: $filePath";
    }
    
    $jsonContent = file_get_contents($filePath);
    $plugins = json_decode($jsonContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return "Invalid JSON format in file.";
    }

    return $plugins['pluginsKv'][$plugin_name];
}

/*
// Usage example:
$filePath = '/home/neil/yoga/repos/plugins-txt-repo/data.json';
$keywords = 'WLP Markdown'; // Search keywords
$results = searchPluginsByName($filePath, $keywords);

if (is_string($results)) {
    echo $results; // Print error message
} else {
    echo json_encode($results, JSON_PRETTY_PRINT); // Print search results
}
//*/
