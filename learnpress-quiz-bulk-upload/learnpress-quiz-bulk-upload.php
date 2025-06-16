<?php
/*
Plugin Name: LearnPress Quiz Bulk Upload
Plugin URI: 
Description: Bulk upload quizzes to LearnPress from Excel files
Version: 1.0
Author: Adam Osama
Author URI: 
License: GPLv2 or later
Text Domain: learnpress-quiz-bulk-upload
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if LearnPress is active
if (!in_array('learnpress/learnpress.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    function lpqbu_admin_notice() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('LearnPress Quiz Bulk Upload requires LearnPress to be installed and active!', 'learnpress-quiz-bulk-upload'); ?></p>
        </div>
        <?php
    }
    add_action('admin_notices', 'lpqbu_admin_notice');
    return;
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/processor.php';

// Load text domain
function lpqbu_load_textdomain() {
    load_plugin_textdomain('learnpress-quiz-bulk-upload', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'lpqbu_load_textdomain');