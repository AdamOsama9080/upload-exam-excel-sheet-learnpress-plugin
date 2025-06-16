<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu
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
add_action('admin_menu', 'lpqbu_add_admin_menu');

// Admin page content
function lpqbu_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('LearnPress Quiz Bulk Upload', 'learnpress-quiz-bulk-upload'); ?></h1>
        
        <div class="card">
            <h2><?php _e('Upload Excel File', 'learnpress-quiz-bulk-upload'); ?></h2>
            <p><?php _e('Upload an Excel file containing your quiz questions. The file should have the following columns:', 'learnpress-quiz-bulk-upload'); ?></p>
            <ul>
                <li><?php _e('Question: The question text', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php _e('Option A: First multiple choice option', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php _e('Option B: Second multiple choice option', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php _e('Option C: Third multiple choice option', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php _e('Option D: Fourth multiple choice option', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php _e('Correct Answer: The correct option (A, B, C, or D)', 'learnpress-quiz-bulk-upload'); ?></li>
                <li><?php _e('Marks: Points for this question (number)', 'learnpress-quiz-bulk-upload'); ?></li>
            </ul>
            
            <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin.php?page=learnpress-quiz-bulk-upload'); ?>">
                <input type="hidden" name="action" value="process_quiz_upload">
                <?php wp_nonce_field('lpqbu_upload_nonce', 'lpqbu_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="quiz_file"><?php _e('Excel File', 'learnpress-quiz-bulk-upload'); ?></label>
                        </th>
                        <td>
                            <input type="file" name="quiz_file" id="quiz_file" accept=".xlsx,.xls,.csv" required>
                            <p class="description"><?php _e('Upload your Excel file (.xlsx, .xls, or .csv)', 'learnpress-quiz-bulk-upload'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="quiz_id"><?php _e('Select Quiz', 'learnpress-quiz-bulk-upload'); ?></label>
                        </th>
                        <td>
                            <select name="quiz_id" id="quiz_id" required>
                                <option value=""><?php _e('-- Select Quiz --', 'learnpress-quiz-bulk-upload'); ?></option>
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
                            <p class="description"><?php _e('Select the quiz to which you want to add these questions', 'learnpress-quiz-bulk-upload'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Upload and Process', 'learnpress-quiz-bulk-upload')); ?>
            </form>
        </div>
        
        <div class="card">
            <h2><?php _e('Download Template', 'learnpress-quiz-bulk-upload'); ?></h2>
            <p><?php _e('Download our Excel template to ensure proper formatting:', 'learnpress-quiz-bulk-upload'); ?></p>
            <a href="<?php echo plugin_dir_url(__FILE__) . '../templates/quiz-template.xlsx'; ?>" class="button button-primary">
                <?php _e('Download Template', 'learnpress-quiz-bulk-upload'); ?>
            </a>
        </div>
    </div>
    <?php
}

// Handle file upload
function lpqbu_handle_upload() {
    if (isset($_POST['action']) && $_POST['action'] === 'process_quiz_upload' && check_admin_referer('lpqbu_upload_nonce', 'lpqbu_nonce')) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploadedfile = $_FILES['quiz_file'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            $quiz_id = intval($_POST['quiz_id']);
            $result = lpqbu_process_excel_file($movefile['file'], $quiz_id);
            
            if ($result['success']) {
                add_settings_error(
                    'lpqbu_messages',
                    'lpqbu_message',
                    sprintf(__('Successfully added %d questions to the quiz.', 'learnpress-quiz-bulk-upload'), $result['count']),
                    'updated'
                );
            } else {
                add_settings_error(
                    'lpqbu_messages',
                    'lpqbu_message',
                    __('Error processing file: ', 'learnpress-quiz-bulk-upload') . $result['message'],
                    'error'
                );
            }
            
            // Delete the temporary file
            unlink($movefile['file']);
        } else {
            add_settings_error(
                'lpqbu_messages',
                'lpqbu_message',
                __('File upload error: ', 'learnpress-quiz-bulk-upload') . $movefile['error'],
                'error'
            );
        }
    }
}
add_action('admin_init', 'lpqbu_handle_upload');