<?php
if (!defined('ABSPATH')) exit;

/**
 * Report Data Meta Box Handler
 */
class RR_Meta_Box_Report_Data {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue required scripts
     */
    public function enqueue_scripts($hook) {
        global $post;
        
        // Only enqueue on post edit screen and for our specific post type
        if (!in_array($hook, array('post.php', 'post-new.php')) || 
            !is_object($post) || 
            $post->post_type !== 'regen-report') { // Change this to match your post type
            return;
        }

        // Ensure all the required scripts are loaded
        wp_enqueue_editor();
        wp_enqueue_media();
        
        // Add any custom styles
        wp_add_inline_style('wp-admin', '
            .report_description_field {
                margin: 1em 0;
            }
            .report_description_field .wp-editor-area {
                height: 200px;
            }
        ');
    }

    /**
     * Output the meta box HTML
     *
     * @param WP_Post $post Post object.
     */
    public function output($post) {
        // Add nonce for security
        wp_nonce_field('regen_reports_save_data', 'regen_reports_meta_nonce');

        // Get existing description if any
        $report_description = get_post_meta($post->ID, 'report_description', true);

        echo '<div class="report_description_field">';
        echo '<p class="form-field">';
        echo '<label for="report_description">' . esc_html__('Description', 'regen-reports') . '</label>';
        
        $editor_id = 'report_description_' . $post->ID; // Unique ID
        $settings = array(
            'textarea_name' => 'report_description',
            'textarea_rows' => 10,
            'media_buttons' => true,
            'teeny'        => false,
            'quicktags'    => true,
            'tinymce'      => true,
            'editor_class' => 'required',
            'wpautop'      => true,
        );
        
        // Remove any existing editor instances
        remove_editor_styles();
        remove_all_filters('mce_buttons');
        remove_all_filters('mce_external_plugins');
        
        // Initialize editor
        wp_editor($report_description, $editor_id, $settings);
        
        echo '</p>';
        echo '</div>';
    }

    /**
     * Save meta box data
     *
     * @param int $post_id Post ID.
     */
    public function save($post_id) {
        // Check if our nonce is set and verify it
        if (!isset($_POST['regen_reports_meta_nonce']) || 
            !wp_verify_nonce($_POST['regen_reports_meta_nonce'], 'regen_reports_save_data')) {
            return;
        }

        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['report_description'])) {
            $allowed_html = array(
                'a'      => array('href' => array(), 'title' => array()),
                'br'     => array(),
                'em'     => array(),
                'strong' => array(),
                'p'      => array(),
                'ul'     => array(),
                'li'     => array(),
                'h2'     => array(),
                'h3'     => array(),
                'h4'     => array(),
            );
            
            $description = wp_kses(wp_unslash($_POST['report_description']), $allowed_html);
            update_post_meta($post_id, 'report_description', $description);
        }
    }
}