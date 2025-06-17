<?php
defined('ABSPATH') || exit;

// Add documentation page
add_action('admin_init', 'lpqbu_add_documentation_page', 100);
function lpqbu_add_documentation_page() {
    add_submenu_page(
        'learn_press',
        __('Bulk Upload Docs', 'learnpress-quiz-bulk-upload'),
        __('Documentation', 'learnpress-quiz-bulk-upload'),
        'edit_others_posts',
        'learnpress-quiz-bulk-docs',
        'lpqbu_documentation_content'
    );
}

function lpqbu_documentation_content() {
    if (!current_user_can('edit_others_posts')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'learnpress-quiz-bulk-upload'));
    }
    
    // Load the HTML documentation
    $doc_file = plugin_dir_path(__FILE__) . '../templates/documentation.html';
    if (file_exists($doc_file)) {
        echo '<div class="wrap">';
        include $doc_file;
        echo '</div>';
    } else {
        echo '<div class="wrap"><h1>' . esc_html__('Error: Documentation file not found.', 'learnpress-quiz-bulk-upload') . '</h1></div>';
    }
}