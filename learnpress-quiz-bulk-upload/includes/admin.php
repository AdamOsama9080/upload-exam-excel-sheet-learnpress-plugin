<?php
defined('ABSPATH') || exit;

// Add admin menu
add_action('admin_menu', 'lpqbu_add_admin_menu');
function lpqbu_add_admin_menu() {
    add_submenu_page(
        'learn_press',
        __('Bulk Quiz Upload', 'learnpress-quiz-bulk-upload'),
        __('Bulk Quiz Upload', 'learnpress-quiz-bulk-upload'),
        'manage_options',
        'learnpress-quiz-bulk-upload',
        'lpqbu_admin_page'
    );
}

// Admin page content
function lpqbu_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'learnpress-quiz-bulk-upload'));
    }
    
    // Process upload if form submitted
    if (isset($_POST['lpqbu_submit'])) {
        lpqbu_handle_upload();
    }
    
    // Show admin notices
    settings_errors('lpqbu_messages');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('LearnPress Quiz Bulk Upload', 'learnpress-quiz-bulk-upload'); ?></h1>
        
        <div class="card">
            <h2><?php esc_html_e('Upload Excel File', 'learnpress-quiz-bulk-upload'); ?></h2>
            
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('lpqbu_upload_action', 'lpqbu_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="quiz_file"><?php esc_html_e('Excel File', 'learnpress-quiz-bulk-upload'); ?></label>
                        </th>
                        <td>
                            <input type="file" name="quiz_file" id="quiz_file" required>
                            <p class="description"><?php esc_html_e('Upload your Excel file (.xlsx, .xls)', 'learnpress-quiz-bulk-upload'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="quiz_id"><?php esc_html_e('Target Quiz', 'learnpress-quiz-bulk-upload'); ?></label>
                        </th>
                        <td>
                            <select name="quiz_id" id="quiz_id" required>
                                <option value=""><?php esc_html_e('-- Select Quiz --', 'learnpress-quiz-bulk-upload'); ?></option>
                                <?php
                                $quizzes = get_posts(array(
                                    'post_type' => LP_QUIZ_CPT,
                                    'posts_per_page' => -1,
                                    'post_status' => 'publish'
                                ));
                                
                                foreach ($quizzes as $quiz) {
                                    echo '<option value="' . esc_attr($quiz->ID) . '">' . esc_html($quiz->post_title) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Upload Questions', 'learnpress-quiz-bulk-upload'), 'primary', 'lpqbu_submit'); ?>
            </form>
        </div>
        
        <div class="card">
            <h2><?php esc_html_e('Download Template', 'learnpress-quiz-bulk-upload'); ?></h2>
            <a href="<?php echo esc_url(plugin_dir_url(__FILE__) . '../templates/quiz-template.xlsx'); ?>" class="button button-primary">
                <?php esc_html_e('Download Template', 'learnpress-quiz-bulk-upload'); ?>
            </a>
        </div>
    </div>
    <?php
}

// Handle file upload
function lpqbu_handle_upload() {
    if (!wp_verify_nonce($_POST['lpqbu_nonce'], 'lpqbu_upload_action')) {
        wp_die(__('Security check failed', 'learnpress-quiz-bulk-upload'));
    }
    
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    $uploadedfile = $_FILES['quiz_file'];
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        $result = lpqbu_process_excel_file($movefile['file'], intval($_POST['quiz_id']));
        
        if ($result['success']) {
            add_settings_error(
                'lpqbu_messages',
                'lpqbu_message',
                sprintf(__('Successfully added %d questions', 'learnpress-quiz-bulk-upload'), $result['count']),
                'success'
            );
        } else {
            add_settings_error(
                'lpqbu_messages',
                'lpqbu_message',
                __('Error: ', 'learnpress-quiz-bulk-upload') . $result['message'],
                'error'
            );
        }
        
        @unlink($movefile['file']);
    } else {
        add_settings_error(
            'lpqbu_messages',
            'lpqbu_message',
            __('Upload error: ', 'learnpress-quiz-bulk-upload') . $movefile['error'],
            'error'
        );
    }
}