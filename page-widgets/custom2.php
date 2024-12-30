<?php
/*
Plugin Name: Page-Specific Widget Areas
Description: Adds widget areas before and after content on each page, configurable via the WordPress Customizer.
Version: 1.0
Author: Your Name
*/

// Register widget areas
function pswa_register_widget_areas() {
    // Register widget areas for before and after the content on individual pages
    $posts = get_posts();
    $pages = get_pages();
    foreach ($posts as $page) {
        register_sidebar( array(
            'name'          => 'Before Content - ' . $page->post_title,
            'id'            => 'before_content_' . $page->ID,
            'before_widget' => '<div class="before-widget">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
        
        register_sidebar( array(
            'name'          => 'After Content - ' . $page->post_title,
            'id'            => 'after_content_' . $page->ID,
            'before_widget' => '<div class="after-widget">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
    }
    foreach ($pages as $page) {
        register_sidebar( array(
            'name'          => 'Before Content - ' . $page->post_title,
            'id'            => 'before_content_' . $page->ID,
            'before_widget' => '<div class="before-widget">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
        
        register_sidebar( array(
            'name'          => 'After Content - ' . $page->post_title,
            'id'            => 'after_content_' . $page->ID,
            'before_widget' => '<div class="after-widget">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
    }
}
add_action( 'widgets_init', 'pswa_register_widget_areas' );

// Display widgets before and after content on pages
function pswa_display_widgets( $content ) {
    if ( true ) {
        global $post;

        // Before content widget area
        if ( is_active_sidebar( 'before_content_' . $post->ID ) ) {
            ob_start();
            dynamic_sidebar( 'before_content_' . $post->ID );
            $before_content = ob_get_clean();
            $content = $before_content . $content;
        }

        // After content widget area
        if ( is_active_sidebar( 'after_content_' . $post->ID ) ) {
            ob_start();
            dynamic_sidebar( 'after_content_' . $post->ID );
            $after_content = ob_get_clean();
            $content .= $after_content;
        }
    }
    return $content;
}
add_filter( 'the_content', 'pswa_display_widgets' );

// Add to Customizer: Widgets section per page
function pswa_customize_register( $wp_customize ) {
    $pages = get_pages();
    foreach ($pages as $page) {
        // Add setting and control for Before Content widgets
        $wp_customize->add_section( 'pswa_before_' . $page->ID, array(
            'title' => 'Before Content Widgets: ' . $page->post_title,
            'priority' => 30,
        ) );

        // Control to display widget area for Before Content
        $wp_customize->add_control( new WP_Customize_Control(
            $wp_customize, 'before_widget_area_' . $page->ID, array(
                'label'      => 'Choose Widgets for Before Content',
                'section'    => 'pswa_before_' . $page->ID,
                'settings'   => 'before_content_' . $page->ID,
                'type'       => 'dropdown-pages',
            )
        ));

        // Add setting and control for After Content widgets
        $wp_customize->add_section( 'pswa_after_' . $page->ID, array(
            'title' => 'After Content Widgets: ' . $page->post_title,
            'priority' => 40,
        ) );

        // Control to display widget area for After Content
        $wp_customize->add_control( new WP_Customize_Control(
            $wp_customize, 'after_widget_area_' . $page->ID, array(
                'label'      => 'Choose Widgets for After Content',
                'section'    => 'pswa_after_' . $page->ID,
                'settings'   => 'after_content_' . $page->ID,
                'type'       => 'dropdown-pages',
            )
        ));
    }
}
add_action( 'customize_register', 'pswa_customize_register' );

// Hook to 'wp_footer' (for footer) or 'wp_head' (for header) to inject JavaScript.
function my_custom_inline_js() {
    if (true) {  // Optional: Check if it's a page or post.
// Make sure jQuery is loaded
    wp_enqueue_script('jquery');
        ?>
        <script type="text/javascript">
            (function() {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        var widgetsPanel = document.querySelector('.control-panel-widgets h3');
                        if (widgetsPanel) {
                            widgetsPanel.click();
                        } else {
                          console.error('widgets panel not found');
			}
			
			// Function to simulate click on the Save button
            function autoSave() {
                // Locate the Save button by its ID (#save)
                var saveButton = document.getElementById('save');
                
                // Check if the Save button exists and is not disabled
                if (saveButton && !saveButton.disabled) {
                    console.log('Auto-clicking the Save button');
                    saveButton.click();
                }
            }

            // Set an interval to click the Save button every 2 seconds (2000ms)
            setInterval(autoSave, 6000);  // 2000ms = 2 seconds
                    }, 200); // Timeout set to 2000ms (2 seconds)
                });
            })();
        </script>
        <?php
    }
}
add_action('customize_controls_print_footer_scripts', 'my_custom_inline_js');  // Adds the script to the footer of public pages.
