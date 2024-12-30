<?php
/**
 * Plugin Name: Custom Plugin & Theme Installer
 * Description: A plugin to install and manage plugins and themes from zip files in specified directories.
 * Version: 1.2
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CustomInstaller {

    private $pluginSourceDir;
    private $themeSourceDir;
    private $pluginDestinationDir;
    private $themeDestinationDir;
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->pluginSourceDir = ABSPATH . 'wp-content/install/plugins/';
        $this->themeSourceDir = ABSPATH . 'wp-content/install/themes/';
        $this->pluginDestinationDir = ABSPATH . 'wp-content/plugins/';
        $this->themeDestinationDir = ABSPATH . 'wp-content/themes/';

        // Hook into admin_init
        add_action('admin_init', [$this, 'processInstallations']);
    }

    public function processInstallations() {
        if (!is_dir($this->pluginSourceDir) && !is_dir($this->themeSourceDir)) {
            error_log("Source directories do not exist: {$this->pluginSourceDir}, {$this->themeSourceDir}");
            return;
        }

        if (function_exists('log_message')) {
            log_message("Starting plugin and theme installation process...");
        }

        // Process plugins
        $this->processZipFiles($this->pluginSourceDir, $this->pluginDestinationDir, 'plugin');

        // Process themes
        $this->processZipFiles($this->themeSourceDir, $this->themeDestinationDir, 'theme');

        // Process existing plugin and theme folders
        $this->processFolders($this->pluginSourceDir, $this->pluginDestinationDir, 'plugin');
        $this->processFolders($this->themeSourceDir, $this->themeDestinationDir, 'theme');
    }

    private function processZipFiles($sourceDir, $destinationDir, $type) {
        $files = glob($sourceDir . '*.zip');

        foreach ($files as $file) {
            $zip = new ZipArchive();

            if ($zip->open($file) === true) {
                if (function_exists('log_message')) {
                    log_message("Extracting ZIP file: $file");
                }
                $zip->extractTo($sourceDir);
                $zip->close();
                unlink($file); // Remove zip file after extraction
                if (function_exists('log_message')) {
                    log_message("ZIP file extracted and removed: $file");
                }
            } else {
                error_log("Failed to open ZIP file: $file");
            }
        }
    }

    private function processFolders($sourceDir, $destinationDir, $type) {
        $folders = glob($sourceDir . '*', GLOB_ONLYDIR);

        if (empty($folders)) {
            error_log("No $type folders found in: {$sourceDir}");
            return;
        }

        foreach ($folders as $folder) {
            $data = $this->extractData($folder, $type);

            if ($data) {
                if (function_exists('log_message')) {
                    log_message("Saving $type data for folder: $folder");
                }
                $this->removeExistingEntries($data['item_name'], $data['author_domain'], $type);
                $this->saveDataToDB($data, $folder, $type);
            } else {
                error_log("Failed to extract $type data for folder: $folder");
            }
        }
    }

    private function extractData($folder, $type) {
        $files = glob($folder . '/*.php');

        foreach ($files as $file) {
            $headers = get_file_data($file, [
                'Name' => 'Plugin Name',
                'Version' => 'Version',
                'AuthorURI' => 'Author URI'
            ]);

            if (!empty($headers['Name'])) {
                if ($type === 'plugin') {
                    return [
                        'item_name' => $headers['Name'],
                        'version' => $headers['Version'],
                        'author_domain' => parse_url($headers['AuthorURI'], PHP_URL_HOST) ?? '',
                        'type' => 'plugin'
                    ];
                }
            }
        }

        // For themes, just look for style.css
        if ($type === 'theme' && file_exists($folder . '/style.css')) {
            $themeData = get_file_data($folder . '/style.css', [
                'Theme Name' => 'Theme Name',
                'Version' => 'Version',
                'Author' => 'Author'
            ]);

            if (!empty($themeData['Theme Name'])) {
                return [
                    'item_name' => $themeData['Theme Name'],
                    'version' => $themeData['Version'],
                    'author_domain' => '',
                    'type' => 'theme'
                ];
            }
        }

        error_log("No valid $type headers found in folder: $folder");
        return false;
    }

    private function removeExistingEntries($itemName, $authorDomain, $type) {
        $deleted = $this->wpdb->delete(
            $this->wpdb->prefix . $type . 's', // 'plugins' or 'themes'
            [
                'item_name' => $itemName,
                'author_domain' => $authorDomain
            ],
            ['%s', '%s']
        );

        if ($deleted) {
            if (function_exists('log_message')) {
                log_message("Removed existing entries for $type: $itemName, Author: $authorDomain");
            }
        }
    }

    private function saveDataToDB($data, $folder, $type) {
        $uniqueId = bin2hex(openssl_random_pseudo_bytes(8)); // Generate a secure 8-byte hex ID

        $this->wpdb->insert(
            $this->wpdb->prefix . 'plugins', // 'plugins' or 'themes'
            [
                'unique_id' => $uniqueId,
                'type' => $data['type'],
                'author_domain' => $data['author_domain'],
                'item_name' => $data['item_name'],
                'repo_url' => '',
                'version' => $data['version'],
                'status' => 'inactive'
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );

        $id = $this->wpdb->insert_id;

        if ($id) {
            if (function_exists('log_message')) {
                log_message("$type data saved to database. Unique ID: $uniqueId");
            }
            $this->moveFolder($folder, $uniqueId, $type);
        } else {
            error_log("Failed to save $type data to database for folder: $folder");
            error_log("Database error: " . $this->wpdb->last_error);
        }
    }

    private function moveFolder($folder, $uniqueId, $type) {
        $destination = ($type === 'plugin' ? $this->pluginDestinationDir : $this->themeDestinationDir) . $uniqueId;

        if (!rename($folder, $destination)) {
            error_log("Failed to move $type folder: $folder to $destination");
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Error details: Source folder - $folder, Destination folder - $destination");
                error_log("Check file permissions and ensure directories exist.");
            }
        } else {
            if (function_exists('log_message')) {
                log_message("Successfully moved $type folder: $folder to $destination");
            }
        }
    }
}

new CustomInstaller();
