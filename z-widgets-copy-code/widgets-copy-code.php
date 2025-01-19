<?php
/*
Plugin Name: Code Copy HTML Widget
Description: A widget that displays essential HTML content without the DOCTYPE, <html>, <head>, and <body> tags for easy site customization.
Version: 1.2
Author: Neil
License: GPL
*/

class Code_Copy_HTML_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'code_copy_html_widget',
            __('Code Copy HTML', 'text_domain'),
            array('description' => __('A widget to display essential HTML content for copying.', 'text_domain'))
        );
    }

    public function widget($args, $instance) {
        // Extract the custom HTML input
        $custom_html = !empty($instance['custom_html']) ? $instance['custom_html'] : '';

        // Extract essential content
        $essential_content = '';

        // Extract styles
        preg_match('/<style>(.*?)<\/style>/s', $custom_html, $style_matches);
        $styles = isset($style_matches[1]) ? $style_matches[1] : '';
        if (!empty($styles)) {
            $essential_content .= "<style>" . $styles . "</style>\n";
        }

        // Extract body content
        preg_match('/<body>(.*?)<\/body>/s', $custom_html, $body_matches);
        $body_content = isset($body_matches[1]) ? $body_matches[1] : '';
        $essential_content .= $body_content . "\n";

        // Extract JavaScript
        preg_match('/<script>(.*?)<\/script>/s', $custom_html, $script_matches);
        $scripts = isset($script_matches[1]) ? $script_matches[1] : '';
        if (!empty($scripts)) {
            $essential_content .= "<script>" . $scripts . "</script>\n";
        }

        // Display the widget
        echo $args['before_widget'];
        echo $essential_content; // Output the essential HTML directly
        echo $args['after_widget'];
    }

    public function form($instance) {
        $custom_html = !empty($instance['custom_html']) ? $instance['custom_html'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('custom_html'); ?>">Custom HTML:</label>
            <textarea class="widefat" id="<?php echo $this->get_field_id('custom_html'); ?>" name="<?php echo $this->get_field_name('custom_html'); ?>" rows="10"><?php echo esc_textarea($custom_html); ?></textarea>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['custom_html'] = (!empty($new_instance['custom_html'])) ? $new_instance['custom_html'] : '';
        return $instance;
    }
}

function register_code_copy_html_widget() {
    register_widget('Code_Copy_HTML_Widget');
}
add_action('widgets_init', 'register_code_copy_html_widget');

