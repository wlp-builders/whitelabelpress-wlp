<?php
/**
 * Plugin Name: My Dark Theme Plugin
 * Description: A plugin to add dark theme styling with !important to specific forms in the WordPress customizer.
 * Version: 1.6
 * Author: Neil
 * License: Spirit of Time 1.0
 */

function add_customizer_dark_theme_colors_with_important() {
    $custom_css = "
        /* General Elements */
        .wrap, .wp-full-overlay-sidebar, .wp-full-overlay-header {
            background-color: #121212 !important;
            color: #ffffff !important;
        }

        .customize-save-button-wrapper,
        .accordion-section-content,
        .control-section,
        .control-section-outer,
        .customize-pane-child {
            background-color: #1e1e1e !important;
            color: #ffffff !important;
            border: 1px solid #333 !important;
        }

        /* Buttons */
        .button, .button-primary, .save, .publish-settings, .button-secondary {
            background-color: #0066cc !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 4px !important;
        }

        .button:hover, .button-primary:hover, .save:hover, .publish-settings:hover {
            background-color: #005bb5 !important;
        }

        .button-link, .button-link-delete {
            color: #ff6666 !important;
            background: transparent !important;
            border: none !important;
        }

        .button-link:hover, .button-link-delete:hover {
            color: #ff4d4d !important;
            text-decoration: underline !important;
        }

        /* Icons and Spinners */
        .dashicons, .dashicons-admin-generic, .spinner, .dashicons-editor-help {
            color: #ffffff !important;
        }

        /* Customizer Controls */
        .customize-controls-preview-toggle,
        .customize-section-title,
        .customize-section-title h3,
        .accordion-section-title,
        .panel-title,
        .site-title,
        .customize-section-title-menu_locations-heading,
        .customize-section-title-menu_locations-description {
            color: #ffffff !important;
        }

        .customize-section-description-container,
        .section-meta,
        .no-drag,
        .customize-action,
        .customize-control-notifications-container,
        .customize-control,
        .customize-control-title {
            background-color: #181818 !important;
            color: #ffffff !important;
        }

        .customize-inside-control-row,
        .customize-control-description,
        .description {
            color: #bbbbbb !important;
        }

        /* Date and Time Fields */
        .date-time-fields, .includes-time, .day-row, .time-row, .time-fields {
            background-color: #202020 !important;
            color: #ffffff !important;
            border: 1px solid #444 !important;
        }

        .title-day, .title-time, .day-fields, .time-fields {
            color: #ffffff !important;
        }

        /* Sidebar and Footer */
        .wp-full-overlay-sidebar-content,
        .wp-full-overlay-footer,
        .collapse-sidebar {
            background-color: #121212 !important;
            color: #ffffff !important;
        }

        /* Device Preview */
        .devices-wrapper, .devices, .preview-desktop, .preview-tablet, .preview-mobile {
            color: #ffffff !important;
        }

        .active {
            background-color: #005bb5 !important;
        }
        
        .customize-control .attachment-media-view .button-add-media, .wp-customizer .menu-item-bar .menu-item-handle, #available-menu-items .accordion-section-content .new-content-item, .customize-control-dropdown-pages .new-content-item, .wp-customizer .menu-item-bar .menu-item-handle,.widget-inside, .customize-control-widget_form .widget-top,#available-menu-items, #available-widgets,input, textarea , #available-menu-items .item-tpl, #available-widgets .widget-tpl, #available-widgets .widget-tpl.selected, #available-widgets .widget-tpl:hover, #available-widgets-list,#available-widgets-filter,.customize-save-button-wrapper, .accordion-section-content, .control-section, .control-section-outer, .customize-pane-child, .wrap, .wp-full-overlay-sidebar, .wp-full-overlay-header,.wp-full-overlay-footer .devices,.wp-full-overlay-sidebar-content, .wp-full-overlay-footer, .collapse-sidebar, #customize-controls .customize-info .customize-panel-description, #customize-controls .customize-info .customize-section-description, #customize-controls .no-widget-areas-rendered-notice, #customize-outer-theme-controls .customize-info .customize-section-description, .customize-section-description-container, .section-meta, .no-drag, .customize-action, .customize-control-notifications-container, .customize-control, .customize-control-title, .customize-controls-preview-toggle, .customize-section-title, .customize-section-title h3, .accordion-section-title, .panel-title, .site-title, .customize-section-title-menu_locations-heading, .customize-section-title-menu_locations-description, .customize-controls-close, .customize-panel-back, .customize-section-back, #customize-controls .customize-info .accordion-section-title, #customize-outer-theme-controls .accordion-section-title, #customize-theme-controls .accordion-section-title {
  background: black!important;  color: white!important;
        }
        
        #customize-controls h3 {
  color: white!important;
}

#available-widgets .widget-title::before, .wp-full-overlay-footer .devices button.active::before, .widget-title h3, #available-widgets .widget .widget-description {

  color: white!important;
  }
  
.active, .button, .button-primary, .save, .publish-settings, .button-secondary, .button:hover, .button-primary:hover, .save:hover, .publish-settings:hover {
    background-color: #a91bbe !important;
    }
   
.wp-full-overlay-footer .devices {
  box-shadow: none;
}

#customize-header-actions, .customize-panel-back, .customize-controls-close, .accordion-section-title, .customize-section-back, .customize-section-title {
border:none;
}
.wp-core-ui .button-primary-disabled, .wp-core-ui .button-primary.disabled, .wp-core-ui .button-primary:disabled, .wp-core-ui .button-primary[disabled] {
    color: #444 !important;
    background: black !important;
}
    .wp-full-overlay-sidebar, #customize-header-actions, .customize-panel-back, .customize-controls-close, .accordion-section-title, .customize-section-back, .customize-section-title {
  background: #f0f0f1;
  border-right: 1px solid #333;
}

  
    ";

    // simply compatible interfaces
    wp_enqueue_style('wp-admin'); // Ensure the admin styles are loaded.
    wp_add_inline_style('wp-admin', $custom_css); // Add inline custom CSS.
}

// simply compatible
add_action('customize_controls_enqueue_scripts', 'add_customizer_dark_theme_colors_with_important');

