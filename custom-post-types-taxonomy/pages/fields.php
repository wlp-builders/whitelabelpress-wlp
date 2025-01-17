<?php
/**
 * Plugin Name: Before and After Meta Fields
 * Description: Adds BEFORE and AFTER meta fields to posts with a settings page for managing custom post types and meta fields.
 * Version: 2.4
 * Author: Neil
 * License: Spirit of Time 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class BeforeAfterMetaFields {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes_before_after' ] );
        add_action( 'save_post', [ $this, 'save_meta_fields' ] );
        add_filter( 'the_content', [ $this, 'display_before_after_meta' ] );
        //add_filter( 'manage_posts_columns', [ $this, 'add_custom_column' ] );
        //add_action( 'manage_posts_custom_column', [ $this, 'display_custom_column_data' ], 10, 2 );
    }

    public function register_settings_page() {
        // Add submenu item under the parent menu
add_submenu_page(
    'cpt-tc-admin',           // Parent menu slug
    'Custom Fields', // Page title for the submenu
    'Custom Fields',       // Menu title for the submenu
    'manage_options',         // Capability required to access this submenu
    'before_after_settings',  // Slug name for the submenu
    [ $this, 'render_settings_page' ]  // Callback function to render the settings page
);
    }

    public function register_settings() {
        register_setting( 'before_after_settings_group', 'before_after_post_types' );
        register_setting( 'before_after_settings_group', 'before_after_meta_fields' );
    }

    public function render_settings_page() {
        $post_types = get_post_types( [ 'public' => true ], 'objects' );
        $meta_fields = get_option( 'before_after_meta_fields', [] );
        //var_dump($meta_fields);
        ?>
        <div class="wrap">
            <h1>Before and After Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'before_after_settings_group' ); ?>
                <?php do_settings_sections( 'before_after_settings_group' ); ?>

                <h2>Select Post Type</h2>
                <ul class="nav nav-tabs">
                    <?php foreach ( $post_types as $post_type ) : ?>
                        <li><a href="#<?php echo esc_attr( $post_type->name ); ?>" data-post_type="<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_html( $post_type->label ); ?></a></li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content">
                    <?php foreach ( $post_types as $post_type ) : ?>
                        <div id="<?php echo esc_attr( $post_type->name ); ?>" class="tab-pane">
                            <h3><?php echo esc_html( $post_type->label ); ?> Meta Fields</h3>
                            <table class="form-table" id="meta-fields-table-<?php echo esc_attr( $post_type->name ); ?>">
                                <thead>
                                    <tr>
                                        <th>Field Name</th>
                                        <th>Type</th>
                                        <th>Position (Before/After)</th>
                                        <th>Order</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ( isset( $meta_fields[ $post_type->name ] ) ) : ?>
                                        <?php foreach ( $meta_fields[ $post_type->name ] as $key => $field ) : ?>
                                            <tr>
                                                <td><input type="text" name="before_after_meta_fields[<?php echo esc_attr( $post_type->name ); ?>][<?php echo $key; ?>][name]" value="<?php echo esc_attr( $field['name'] ); ?>" /></td>
                                                <td>
                                                    <select name="before_after_meta_fields[<?php echo esc_attr( $post_type->name ); ?>][<?php echo $key; ?>][type]">
                                                        <option value="string" <?php selected( $field['type'], 'string' ); ?>>String</option>
                                                        <option value="number" <?php selected( $field['type'], 'number' ); ?>>Number</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="before_after_meta_fields[<?php echo esc_attr( $post_type->name ); ?>][<?php echo $key; ?>][position]">
                                                        <option value="before" <?php selected( $field['position'], 'before' ); ?>>Before</option>
                                                        <option value="after" <?php selected( $field['position'], 'after' ); ?>>After</option>
                                                    </select>
                                                </td>
                                                <td><input type="number" name="before_after_meta_fields[<?php echo esc_attr( $post_type->name ); ?>][<?php echo $key; ?>][order]" value="<?php echo esc_attr( $field['order'] ); ?>" /></td>
                                                <td><button type="button" class="remove-field">Remove</button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <button type="button" id="add-meta-field-<?php echo esc_attr( $post_type->name ); ?>">Add Field</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <script>
                    // Toggle tabs
                    document.querySelectorAll('.nav-tabs a').forEach(function(tabLink) {
                        tabLink.addEventListener('click', function(event) {
                            event.preventDefault();
                            var postType = tabLink.getAttribute('data-post_type');
                            document.querySelectorAll('.tab-pane').forEach(function(tabContent) {
                                tabContent.classList.remove('active');
                            });
                            document.querySelector('#' + postType).classList.add('active');
                        });
                    });

                    document.querySelectorAll('.remove-field').forEach(function(button) {
                        button.addEventListener('click', function() {
                            var row = button.closest('tr');
                            row.parentNode.removeChild(row);
                        });
                    });

                    document.querySelectorAll('[id^="add-meta-field-"]').forEach(function(button) {
                        button.addEventListener('click', function() {
                            var postType = button.id.replace('add-meta-field-', '');
                            const table = document.getElementById('meta-fields-table-' + postType).querySelector('tbody');
                            const rowCount = table.rows.length;
                            const row = table.insertRow();
                            row.innerHTML = `
                                <td><input type="text" name="before_after_meta_fields[${postType}][${rowCount}][name]" /></td>
                                <td>
                                    <select name="before_after_meta_fields[${postType}][${rowCount}][type]">
                                        <option value="string">String</option>
                                        <option value="number">Number</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="before_after_meta_fields[${postType}][${rowCount}][position]">
                                        <option value="before">Before</option>
                                        <option value="after">After</option>
                                    </select>
                                </td>
                                <td><input type="number" name="before_after_meta_fields[${postType}][${rowCount}][order]" /></td>
                                <td><button type="button" class="remove-field">Remove</button></td>
                            `;
                        });
                    });
                </script>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function add_meta_boxes_before_after() {
        $selected_post_types = get_option( 'before_after_meta_fields', [] );
        //var_dump($selected_post_types);
        foreach ( $selected_post_types as $post_type ) {
            add_meta_box( 
                'before_after_meta', 
                'Custom Meta Fields', 
                [ $this, 'render_meta_boxes' ], 
                $post_type, 
                'normal', 
                'default' 
            );
        }
    }

    public function render_meta_boxes( $post ) {
        $meta_fields = get_option( 'before_after_meta_fields', [] );
        $post_type = $post->post_type;

        if ( isset( $meta_fields[ $post_type ] ) ) {
            wp_nonce_field( 'save_before_after_meta', 'before_after_meta_nonce' );

            foreach ( $meta_fields[ $post_type ] as $field ) {
                $meta_key = $post_type . '_' . str_replace(' ','_',$field['name']);

                $value = get_post_meta( $post->ID, $meta_key, true );
                echo '<label for="' . esc_attr( $meta_key ) . '">' . esc_html( str_replace( '_', ' ', $field['name'] ) ) . ' (' . esc_html( ucfirst( $field['type'] ) ) . '):</label>';
                echo '<input type="' . esc_attr( $field['type'] ) . '" id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $value ) . '" style="width:100%;" />';
            }
        }
    }

    public function save_meta_fields( $post_id ) {
        if ( ! isset( $_POST['before_after_meta_nonce'] ) || ! wp_verify_nonce( $_POST['before_after_meta_nonce'], 'save_before_after_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $post_type = get_post_type( $post_id );
        $meta_fields = get_option( 'before_after_meta_fields', [] );

        if ( isset( $meta_fields[ $post_type ] ) ) {
            foreach ( $meta_fields[ $post_type ] as $field ) {
                $meta_key = $post_type . '_' . str_replace(' ','_',$field['name']);
                if ( isset( $_POST[ $meta_key ] ) ) {
                    $value = sanitize_text_field( $_POST[ $meta_key ] );
                    if ( $field['type'] === 'number' ) {
                        $value = floatval( $value );
                    }
                    //var_dump(json_encode(['update_post_meta'=>[$post_id, $meta_key, $value]]));
                    update_post_meta( $post_id, $meta_key, $value );
                } else {
                    //var_dump(json_encode(['delete_post_meta'=>[$post_id, $meta_key]]));
                    delete_post_meta( $post_id, $meta_key );
                }
            }
        }
    }

    public function display_before_after_meta( $content ) {
        if ( is_singular() && in_the_loop() && is_main_query() ) {
            $post_id = get_the_ID();
            $post_type = get_post_type( $post_id );

            $meta_fields = get_option( 'before_after_meta_fields', [] );

            if ( isset( $meta_fields[ $post_type ] ) ) {
                $before_content = '';
                $after_content = '';

                foreach ( $meta_fields[ $post_type ] as $field ) {
                    $meta_key = $post_type . '_' . str_replace(' ','_',$field['name']);

                    $meta_value = get_post_meta( $post_id, $meta_key, true );

                    if ( $meta_value !== '' ) {
                        $div_class = 'meta-' . sanitize_html_class( $field['name'] );
                        $wrapped_value = '<div class="' . esc_attr( $div_class ) . '"><label>' . esc_html( str_replace( '_', ' ', $field['name'] ) ) . ':</label> <span>' . esc_html( $meta_value ) . '</span></div>';

                        if ( $field['position'] === 'before' ) {
                            $before_content .= $wrapped_value;
                        } elseif ( $field['position'] === 'after' ) {
                            $after_content .= $wrapped_value;
                        }
                    }
                }

                $content = $before_content . $content . $after_content;
            }
        }

        return $content;
    }
}

new BeforeAfterMetaFields();


