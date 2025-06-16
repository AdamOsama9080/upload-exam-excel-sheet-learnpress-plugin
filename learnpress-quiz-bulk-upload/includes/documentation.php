<?php
defined('ABSPATH') || exit;

// Add documentation page
add_action('admin_menu', 'lpqbu_add_documentation_page');
function lpqbu_add_documentation_page() {
    add_submenu_page(
        'learn_press',
        __('Bulk Upload Docs', 'learnpress-quiz-bulk-upload'),
        __('Documentation', 'learnpress-quiz-bulk-upload'),
        'manage_options',
        'learnpress-quiz-bulk-docs',
        'lpqbu_documentation_content'
    );
}

function lpqbu_documentation_content() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'learnpress-quiz-bulk-upload'));
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('LearnPress Quiz Bulk Upload Documentation', 'learnpress-quiz-bulk-upload'); ?></h1>
        
        <div class="card">
            <h2><?php esc_html_e('How to Use', 'learnpress-quiz-bulk-upload'); ?></h2>
            <ol>
                <li><?php esc_html_e('Prepare your questions in Excel using the template format', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php esc_html_e('Go to LearnPress → Bulk Quiz Upload', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php esc_html_e('Select your Excel file and target quiz', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php esc_html_e('Click "Upload Questions"', 'learnpress-quiz-bulk-upload'); ?></li>
            </ol>
            
            <a href="<?php echo esc_url(admin_url('admin.php?page=learnpress-quiz-bulk-upload')); ?>" class="button button-primary">
                <?php esc_html_e('Go to Upload Page', 'learnpress-quiz-bulk-upload'); ?>
            </a>
        </div>
        
        <div class="card">
            <h2><?php esc_html_e('Excel Format Requirements', 'learnpress-quiz-bulk-upload'); ?></h2>
            <ul>
                <li><?php esc_html_e('Column 1: Question text', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php esc_html_e('Column 2: Option A', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php esc_html_e('Column 3: Option B', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php esc_html_e('Column 4: Option C', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php esc_html_e('Column 5: Option D', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php esc_html_e('Column 6: Correct answer (A, B, C, or D)', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php esc_html_e('Column 7: Marks/points for question', 'learnpress-quiz-bulk-upload'); ?></li>
            </ul>
            
            <a href="<?php echo esc_url(plugin_dir_url(__FILE__) . '../templates/quiz-template.xlsx'); ?>" class="button">
                <?php esc_html_e('Download Template', 'learnpress-quiz-bulk-upload'); ?>
            </a>
        </div>
    </div>
    <?php
}