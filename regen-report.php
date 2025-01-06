<?php
/*
Plugin Name: Regen Report
Description: Bioregional report plugin
Version: 1.0
Author: Kahlil Calavas
*/

if (!defined('ABSPATH')) exit;

class RegenReport {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
    }

    public function register_post_type() {
        register_post_type('regen_report', array(
            'labels' => array(
                'name' => 'Regen Reports',
                'singular_name' => 'Regen Report',
                'add_new' => 'Add New Report',
                'add_new_item' => 'Add New Regen Report',
                'edit_item' => 'Edit Regen Report',
                'new_item' => 'New Regen Report',
                'view_item' => 'View Regen Report',
                'search_items' => 'Search Regen Reports',
                'not_found' => 'No regen reports found',
                'not_found_in_trash' => 'No regen reports found in trash'
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-chart-area',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'rewrite' => array('slug' => 'regen-report')
        ));

        // Register the Report Data meta box
        add_action('add_meta_boxes_regen_report', array($this, 'add_report_data_meta_box'));
        add_action('save_post_regen_report', array($this, 'save_report_data_meta_box'));
    }

    public function add_meta_boxes() {
        $meta_boxes = array(
            'environmental_info' => 'Environmental Information',
            'agricultural_resources' => 'Agricultural Resources',
            'cultural_historical' => 'Cultural & Historical',
            'building_zoning' => 'Building & Zoning',
            'business_economic' => 'Business & Economic'
        );

        foreach ($meta_boxes as $id => $title) {
            add_meta_box(
                $id,
                $title,
                array($this, 'render_meta_box'),
                'regen_report',
                'normal',
                'high',
                array('field' => $id)
            );
        }
    }

    public function render_meta_box($post, $metabox) {
        wp_nonce_field('regen_report_meta_box', 'regen_report_meta_box_nonce');
        $value = get_post_meta($post->ID, '_' . $metabox['args']['field'], true);
        
        // Editor settings
        $settings = array(
            'textarea_name' => $metabox['args']['field'],
            'textarea_rows' => 10,
            'media_buttons' => true,
            'tinymce'      => true,
            'quicktags'    => true,
            'editor_height' => 200
        );
        
        // Output editor
        wp_editor(
            $value, 
            'editor_' . $metabox['args']['field'], // Unique ID for each editor
            $settings
        );
    }

    public function save_meta_boxes($post_id) {
        if (!isset($_POST['regen_report_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['regen_report_meta_box_nonce'], 'regen_report_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = array(
            'environmental_info',
            'agricultural_resources',
            'cultural_historical',
            'building_zoning',
            'business_economic'
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $allowed_html = array(
                    'a'      => array('href' => array(), 'title' => array()),
                    'br'     => array(),
                    'em'     => array(),
                    'strong' => array(),
                    'p'      => array(),
                    'ul'     => array(),
                    'ol'     => array(),
                    'li'     => array(),
                    'h2'     => array(),
                    'h3'     => array(),
                    'h4'     => array(),
                    'img'    => array(
                        'src'    => array(),
                        'alt'    => array(),
                        'class'  => array(),
                        'width'  => array(),
                        'height' => array()
                    )
                );
                
                update_post_meta(
                    $post_id,
                    '_' . $field,
                    wp_kses(wp_unslash($_POST[$field]), $allowed_html)
                );
            }
        }
    }

    public function add_report_data_meta_box() {
        add_meta_box(
            'rr-report-data',
            __('Report Description', 'regen-reports'),
            array($this, 'render_report_data_meta_box'),
            'regen_report',
            'normal',
            'high'
        );
    }

    public function render_report_data_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('regen_report_data', 'regen_report_data_nonce');

        // Get existing description
        $content = get_post_meta($post->ID, 'report_description', true);

        // Editor settings
        $settings = array(
            'textarea_name' => 'report_description',
            'textarea_rows' => 10,
            'media_buttons' => true,
            'tinymce'      => true,
            'quicktags'    => true
        );

        // Output editor
        wp_editor($content, 'report_description_editor', $settings);
    }

    public function save_report_data_meta_box($post_id) {
        // Security checks
        if (!isset($_POST['regen_report_data_nonce']) || 
            !wp_verify_nonce($_POST['regen_report_data_nonce'], 'regen_report_data')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save description
        if (isset($_POST['report_description'])) {
            $content = wp_kses_post($_POST['report_description']);
            update_post_meta($post_id, 'report_description', $content);
        }
    }
}

new RegenReport();

// Add template for single regen report
add_filter('single_template', function($single) {
    global $post;

    if ($post->post_type === 'regen_report') {
        if (file_exists(plugin_dir_path(__FILE__) . 'single-regenreport.php')) {
            return plugin_dir_path(__FILE__) . 'single-regenreport.php';
        }
    }
    return $single;
});
