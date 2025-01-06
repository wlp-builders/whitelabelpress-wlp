<?php
function searchPluginsTxt($filePath, $keywords) {
    // Read the file content
    if (!file_exists($filePath)) {
        return "File not found: $filePath";
    }
  
    
    $jsonContent = file_get_contents($filePath);
    $plugins = json_decode($jsonContent, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return "Invalid JSON format in file.";
    }

  $plugins = $plugins['plugins'];
    
    // Split the keywords
    $keywords = array_filter(array_map('trim', explode(' ', $keywords))); // Split and trim
    $exactKeywords = array_slice($keywords, 0, -1); // All but the last word
    $partialKeyword = end($keywords); // The last keyword
    
    // Search the plugins
    $results = array_filter($plugins, function ($plugin) use ($exactKeywords, $partialKeyword) {
        // Combine name and description into a single searchable string
        $searchableString = $plugin['name'] . ' ' . $plugin['description'];
        
        // Check for exact match for all exactKeywords
        foreach ($exactKeywords as $keyword) {
            if (stripos($searchableString, $keyword) === false) {
                return false; // Exact match failed
            }
        }
        
        // Check for partial match on the last keyword
        return stripos($searchableString, $partialKeyword) !== false;
    });
    
    return array_values($results); // Reindex the array
}

/*
// Usage example:
$filePath = '/home/neil/yoga/repos/plugins-txt-repo/data.json';
$keywords = ''; // Search keywords
$results = searchPluginsTxt($filePath, $keywords);

if (is_string($results)) {
    echo $results; // Print error message
} else {
    echo json_encode($results, JSON_PRETTY_PRINT); // Print search results
}
//*/
