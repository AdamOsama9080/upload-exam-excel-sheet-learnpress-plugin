<?php
/*
Plugin Name: LearnPress Quiz Bulk Upload
Plugin URI: 
Description: Bulk upload quizzes to LearnPress from Excel files
Version: 1.0
Author: Adam Osama Hammad
Author URI: 
License: GPLv2 or later
Text Domain: learnpress-quiz-bulk-upload
*/

defined('ABSPATH') || exit;

// Check if LearnPress is active
register_activation_hook(__FILE__, 'lpqbu_check_dependencies');
function lpqbu_check_dependencies() {
    if (!is_plugin_active('learnpress/learnpress.php')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            __('This plugin requires LearnPress to be installed and active.', 'learnpress-quiz-bulk-upload'),
            __('Plugin dependency error', 'learnpress-quiz-bulk-upload'),
            array('back_link' => true)
        );
    }
}

// Load plugin files
add_action('plugins_loaded', 'lpqbu_init_plugin');
function lpqbu_init_plugin() {
    if (!class_exists('LP_Addon')) return;
    
    require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
    require_once plugin_dir_path(__FILE__) . 'includes/processor.php';
    require_once plugin_dir_path(__FILE__) . 'includes/documentation.php';
    
    load_plugin_textdomain(
        'learnpress-quiz-bulk-upload',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
    
    // Add action links
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'lpqbu_add_action_links');
}

function lpqbu_add_action_links($links) {
    $new_links = array(
        '<a href="' . admin_url('admin.php?page=learnpress-quiz-bulk-upload') . '">' . __('Upload Questions', 'learnpress-quiz-bulk-upload') . '</a>',
        '<a href="' . admin_url('admin.php?page=learnpress-quiz-bulk-docs') . '">' . __('Documentation', 'learnpress-quiz-bulk-upload') . '</a>'
    );
    return array_merge($links, $new_links);
}