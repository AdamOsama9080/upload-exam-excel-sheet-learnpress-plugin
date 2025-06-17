<?php
/**
 * Plugin Name: LearnPress Quiz Bulk Upload
 * Description: Bulk upload questions to LearnPress quizzes from Excel files
 * Version: 1.0.1
 * Author: Adam Osama
 * Author URI: adamosama9080@outlook.com
 * Text Domain: learnpress-quiz-bulk-upload
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.0
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('LPQBU_VERSION', '1.0.1');
define('LPQBU_PLUGIN_FILE', __FILE__);
define('LPQBU_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LPQBU_PLUGIN_URL', plugin_dir_url(__FILE__));

// Check if LearnPress is active
register_activation_hook(__FILE__, 'lpqbu_activate_plugin');
function lpqbu_activate_plugin() {
    if (!is_plugin_active('learnpress/learnpress.php')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('LearnPress Quiz Bulk Upload requires LearnPress to be installed and active.', 'learnpress-quiz-bulk-upload'));
    }
}

// Initialize plugin
add_action('plugins_loaded', 'lpqbu_init_plugin');
function lpqbu_init_plugin() {
    // Check if LearnPress is loaded
    if (!function_exists('learn_press')) {
        add_action('admin_notices', 'lpqbu_learnpress_missing_notice');
        return;
    }

    // Load plugin files
    require_once LPQBU_PLUGIN_PATH . 'includes/admin.php';
    require_once LPQBU_PLUGIN_PATH . 'includes/processor.php';
    require_once LPQBU_PLUGIN_PATH . 'includes/documentation.php';
    
    // Load text domain
    load_plugin_textdomain(
        'learnpress-quiz-bulk-upload',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}

// LearnPress missing notice
function lpqbu_learnpress_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('LearnPress Quiz Bulk Upload requires LearnPress to be installed and active.', 'learnpress-quiz-bulk-upload'); ?></p>
    </div>
    <?php
}

// Add settings link to plugin actions
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'lpqbu_add_action_links');
function lpqbu_add_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=learnpress-quiz-bulk-upload') . '">' . __('Upload Questions', 'learnpress-quiz-bulk-upload') . '</a>';
    $docs_link = '<a href="' . admin_url('admin.php?page=learnpress-quiz-bulk-docs') . '">' . __('Documentation', 'learnpress-quiz-bulk-upload') . '</a>';
    array_unshift($links, $settings_link, $docs_link);
    return $links;
}