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
        
        // Add enqueue actions
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts() {
        // Enqueue jQuery
        wp_enqueue_script('jquery');
        
        // Enqueue our styles
        wp_enqueue_style(
            'regen-report-styles',
            plugin_dir_url(__FILE__) . 'assets/css/regen-report.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'assets/css/regen-report.css')
        );
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

// Add shortcode for displaying regen reports
add_shortcode('regen_report', function($atts) {
    // Get attributes
    $atts = shortcode_atts(array(
        'id' => 0
    ), $atts);

    // If no ID provided, return empty
    if (empty($atts['id'])) {
        return 'Please provide a report ID.';
    }

    // Get the post
    $report = get_post($atts['id']);

    // If post doesn't exist or is not a regen_report
    if (!$report || $report->post_type !== 'regen_report') {
        return 'Report not found.';
    }

    // Start output buffering
    ob_start();

    // Get all the meta fields
    $environmental_info = get_post_meta($report->ID, '_environmental_info', true);
    $agricultural_resources = get_post_meta($report->ID, '_agricultural_resources', true);
    $cultural_historical = get_post_meta($report->ID, '_cultural_historical', true);
    $building_zoning = get_post_meta($report->ID, '_building_zoning', true);
    $business_economic = get_post_meta($report->ID, '_business_economic', true);
    ?>
    <div class="mx-auto px-4 py-8">
        <header class="bg-green-800 text-white p-8 rounded-lg mb-12 text-center">
            <h1 class="text-3xl"><?php echo esc_html($report->post_title); ?></h1>
        </header>

        <div class="tabs flex justify-center flex-wrap gap-4 mb-8">
            <button class="tab active px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'environmental-<?php echo $report->ID; ?>')">Environmental Information</button>
            <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'agricultural-<?php echo $report->ID; ?>')">Agricultural Resources</button>
            <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'cultural-<?php echo $report->ID; ?>')">Cultural & Historical</button>
            <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'building-<?php echo $report->ID; ?>')">Building & Zoning</button>
            <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'business-<?php echo $report->ID; ?>')">Business & Economic</button>
        </div>

        <div id="environmental-<?php echo $report->ID; ?>" class="tab-content active bg-white p-8 rounded-lg shadow mb-8">
            <?php echo wpautop($environmental_info); ?>
        </div>

        <div id="agricultural-<?php echo $report->ID; ?>" class="tab-content hidden bg-white p-8 rounded-lg shadow mb-8">
            <?php echo wpautop($agricultural_resources); ?>
        </div>

        <div id="cultural-<?php echo $report->ID; ?>" class="tab-content hidden bg-white p-8 rounded-lg shadow mb-8">
            <?php echo wpautop($cultural_historical); ?>
        </div>

        <div id="building-<?php echo $report->ID; ?>" class="tab-content hidden bg-white p-8 rounded-lg shadow mb-8">
            <?php echo wpautop($building_zoning); ?>
        </div>

        <div id="business-<?php echo $report->ID; ?>" class="tab-content hidden bg-white p-8 rounded-lg shadow mb-8">
            <?php echo wpautop($business_economic); ?>
        </div>
    </div>

    <script>
    function openTab(evt, tabName) {
        const tabContents = document.getElementsByClassName("tab-content");
        for (let content of tabContents) {
            if (content.id.includes('<?php echo $report->ID; ?>')) {
                content.classList.add('hidden');
                content.classList.remove('active');
            }
        }
        
        const tabs = evt.currentTarget.parentElement.getElementsByClassName("tab");
        for (let tab of tabs) {
            tab.classList.remove('active', 'bg-green-800', 'text-white');
        }
        
        document.getElementById(tabName).classList.remove('hidden');
        document.getElementById(tabName).classList.add('active');
        evt.currentTarget.classList.add('active', 'bg-green-800', 'text-white');
    }

    // Activate first tab by default
    document.addEventListener('DOMContentLoaded', function() {
        const reportTabs = document.querySelectorAll('.tab');
        reportTabs.forEach(tab => {
            if (tab.parentElement.querySelector('.tab-content.active')) {
                tab.click();
            }
        });
    });
    </script>
    <style>
    .mx-auto {
        background-color: #00000082 !important;
        color: #f4ebd8 !important;
        font-size: 22px;
        border: 15px solid #d8b670;
    }
    .tab {
        font-family: inherit;
        font-size: inherit;
        line-height: inherit;
        border-radius: 13px;
        background-color: #d8b670;
        padding: 5px 15px;
        margin: 15px;
    }
    .tab-content p, .tab-content ul li {
        text-align: left;
    }
    .tab-content ul li {
        padding: 12px;
        padding-left: 32px;
    }
    .tab-content ul {
        background:#333;
        padding: 35px 11px;
        margin: 0px 30px;
        border-radius:13px;
        margin-bottom:33px;
    }
    </style>
    <?php
    return ob_get_clean();
});

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
