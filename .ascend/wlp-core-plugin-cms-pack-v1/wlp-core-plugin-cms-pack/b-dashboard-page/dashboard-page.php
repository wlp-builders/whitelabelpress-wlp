<?php
/*
Plugin Name: Custom Dashboard Grid with Collapsible Menu (Dark Mode)
Description: Replaces the default Dashboard with a custom page displaying the top-level menu items in a 3-column grid layout. Adds a collapsible sidebar menu with dark theme.
Version: 1.0
Author: Your Name
*/


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Callback function to render the admin page
function display_did_and_keys_page() {
    echo '<div class="wrap">';
    echo '<h1>Welcome to you, <a href="https://wlp.builders" target="_blank">builder</a>.</h1>';

    $did_file_path = ABSPATH . '.well-known/did.json';
    $wlp_keys_dir = ABSPATH . 'wlp-keys/';

    // Display the content of .well-known/did.json
    echo '<h2><a target="_blank" href="'.get_site_url().'/.well-known/did.json">Your DID Document 🛡️</a></h2>';

    echo '<p><em>This is your decentralized identity file. You can also use it to receive funds & awards. It is strongly recommended not to delete or modify it unless regeneration is absolutely necessary.</em></p>';
    if (file_exists($did_file_path)) {
      
    } else {
        echo '<p><strong>File not found:</strong> .well-known/did.json</p>';
    }

    if (!is_dir($wlp_keys_dir)) {
        echo '<p><strong>Directory not found:</strong></p>';
    } else {
    // Display the list of files in /wlp-keys
          $files = scandir($wlp_keys_dir);
        $filtered_files = array_filter($files, fn($file) => $file !== '.' && $file !== '..');
        $file_count = count($filtered_files);
        echo '<h2>'.$file_count.'/4 Files in wlp-keys</h2>';
        echo '<p><em>Less private keys on a server is better. You can move all keys locally except the sig key, which is used for generic signings interactions with decentralized apps like repositories.</em></p>';
     
        echo '<ul>';
        foreach ($filtered_files as $file) {
            if($file == 'sig_secret_base64.php') {
                echo '<li><b>🔑 ' . esc_html(str_replace('_secret_base64.php','',$file)) . '</b></li> ';

            } else {
                echo '<li>🔑 ' . esc_html(str_replace('_secret_base64.php','',$file)) . '</li> ';
            }
        }
        echo '</ul>';
    }

    echo '</div>';
}




// Hook into the admin_menu action to modify the Dashboard menu
add_action( 'admin_menu', 'custom_dashboard_menu', 10 );

function custom_dashboard_menu() {
    // Add the custom Dashboard menu item
    add_menu_page(
        __( 'Dashboard' ),                        // Page title
        __( 'Dashboard' ),                        // Menu title
        'read',                                   // Capability required
        'dashboard2',                              // Custom menu slug
        'custom_dashboard_page_content',           // Function for custom page content
        'dashicons-dashboard',                    // Menu icon
        2                                         // Menu position (2 is for the Dashboard position)
    );
}

// Callback function for the custom Dashboard page content
function custom_dashboard_page_content() {
    display_did_and_keys_page();
    ?>
    <div class="wrap wrap--dashboard2">
        <h1 class="wp-heading-inline">Actions</h1>
        <div id="custom-dashboard-grid" class="custom-dashboard-grid">
            <!-- Placeholder grid items will be here initially -->
            <div class="custom-dashboard-item empty"></div>
            <div class="custom-dashboard-item empty"></div>
            <div class="custom-dashboard-item empty"></div>
            <div class="custom-dashboard-item empty"></div>
            <div class="custom-dashboard-item empty"></div>
            <div class="custom-dashboard-item empty"></div>
            <div class="custom-dashboard-item empty"></div>
            <div class="custom-dashboard-item empty"></div>
            <div class="custom-dashboard-item empty"></div>
        </div>
    </div>
    <style>
        .wrap--dashboard2 {
            padding: 20px;
            border-radius: 8px;
        }

        .wrap--dashboard2 h1 {
            color: white;
        }

        .custom-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 columns layout */
            gap: 20px;
            margin-top: 20px;
        }

        .custom-dashboard-item {
            text-align: center;
            background: #111;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            opacity: 0; /* Initially hidden */
            transition: opacity 0.5s ease-in-out; /* Fade-in effect */
        }

        .custom-dashboard-item.empty {
            background: #444;
            visibility: hidden; /* Keep placeholders invisible */
        }

        .custom-dashboard-item:hover {
            background-color: #444;
        }

        .custom-dashboard-link {
            text-decoration: none;
            color: inherit;
        }

        .custom-dashboard-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #ffffff;
        }

        .custom-dashboard-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ffffff;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Function to fetch top-level menu items and inject into the custom dashboard grid
            function populateDashboardGrid() {
                // Wait for 300ms to ensure the admin menu is fully loaded
                setTimeout(function() {
                    // Get all top-level menu items from the admin menu
                    const menuItems = document.querySelectorAll('#adminmenu li.menu-top > a');
                    const gridContainer = document.getElementById('custom-dashboard-grid');

                    // Remove all empty placeholders before inserting real items
                    const emptyItems = gridContainer.querySelectorAll('.custom-dashboard-item.empty');
                    emptyItems.forEach(function(item) {
                        item.remove();
                    });

                    // Loop through each menu item
                    menuItems.forEach(function(menuItem) {
                        let title='';
                        let menuItemClean = menuItem.querySelector('.wp-menu-name');
                        if(menuItemClean.innerHTML.includes('<')) {
                            title = menuItemClean.innerHTML.split('<')[0];
                        }
                        else { 
                            title = menuItemClean.textContent.trim();
                        }
                        
                        if(title === 'Dashboard') return true;
                        const url = menuItem.href;  // Get the href attribute for the URL

                        // Find the icon inside the menu item
                        const iconElement = menuItem.querySelector('.wp-menu-image');
                        const iconClass = iconElement ? iconElement.className : 'dashicons-admin-generic';

                        // Create a new grid item for each menu item
                        const gridItem = document.createElement('div');
                        gridItem.classList.add('custom-dashboard-item');
                        gridItem.innerHTML = `
                            <a href="${url}" class="custom-dashboard-link">
                                <div class="custom-dashboard-icon">
                                    <span class="${iconClass}"></span>
                                </div>
                                <div class="custom-dashboard-title">
                                    ${title}
                                </div>
                            </a>
                        `;

                        // Append the new grid item to the grid container
                        gridContainer.appendChild(gridItem);

                        // Trigger the fade-in effect by setting opacity to 1
                        setTimeout(function() {
                            gridItem.style.opacity = 1;
                        }, 50); // Add slight delay to ensure transition occurs
                    });
                }, 0); // Delay of 300ms before replacing placeholders
            }

            // Call the function to populate the dashboard grid after the page is loaded
            populateDashboardGrid();
        });

        // JavaScript to toggle the menu collapse and expand
        document.addEventListener('DOMContentLoaded', function () {
            function clickCollapseMainMenu() {
                const element = document.querySelector('[aria-label="Collapse Main menu"]');
                
                if (element) {
                    element.click();
                    console.log('Main menu collapsed');
                } else {
                    console.log('Element not found');
                }
            }

            setTimeout(() => {
                clickCollapseMainMenu();
                
            }, 300);

        });
    </script>
<?php
}
?>

