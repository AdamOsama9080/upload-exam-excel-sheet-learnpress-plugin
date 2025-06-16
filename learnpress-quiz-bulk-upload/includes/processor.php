<?php
if (!defined('ABSPATH')) {
    exit;
}

// Process Excel file
function lpqbu_process_excel_file($file_path, $quiz_id) {
    if (!file_exists($file_path)) {
        return array('success' => false, 'message' => __('File does not exist', 'learnpress-quiz-bulk-upload'));
    }
    
    // Check if PHPExcel/PhpSpreadsheet is available
    if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
        // Try to include PhpSpreadsheet if it's bundled
        $vendor_path = plugin_dir_path(__FILE__) . '../../vendor/autoload.php';
        if (file_exists($vendor_path)) {
            require_once $vendor_path;
        } else {
            return array(
                'success' => false,
                'message' => __('PhpSpreadsheet library is required. Please install it via Composer.', 'learnpress-quiz-bulk-upload')
            );
        }
    }
    
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Remove header row if exists
        array_shift($rows);
        
        $count = 0;
        $errors = array();
        
        foreach ($rows as $index => $row) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Validate required fields
            if (count($row) < 7) {
                $errors[] = sprintf(__('Row %d: Not enough columns', 'learnpress-quiz-bulk-upload'), $index + 1);
                continue;
            }
            
            $question = sanitize_text_field($row[0]);
            $option_a = sanitize_text_field($row[1]);
            $option_b = sanitize_text_field($row[2]);
            $option_c = sanitize_text_field($row[3]);
            $option_d = sanitize_text_field($row[4]);
            $correct_answer = strtoupper(sanitize_text_field($row[5]));
            $marks = floatval($row[6]);
            
            // Validate correct answer
            if (!in_array($correct_answer, array('A', 'B', 'C', 'D'))) {
                $errors[] = sprintf(__('Row %d: Correct answer must be A, B, C, or D', 'learnpress-quiz-bulk-upload'), $index + 1);
                continue;
            }
            
            // Create question
            $question_id = wp_insert_post(array(
                'post_title' => $question,
                'post_type' => LP_QUESTION_CPT,
                'post_status' => 'publish'
            ));
            
            if (is_wp_error($question_id)) {
                $errors[] = sprintf(__('Row %d: Error creating question - %s', 'learnpress-quiz-bulk-upload'), $index + 1, $question_id->get_error_message());
                continue;
            }
            
            // Set question type
            update_post_meta($question_id, '_lp_type', 'multi_choice');
            
            // Set question mark
            update_post_meta($question_id, '_lp_mark', $marks);
            
            // Prepare answer data
            $answers = array(
                array(
                    'is_true' => ($correct_answer === 'A') ? 'yes' : 'no',
                    'title' => $option_a,
                    'value' => uniqid()
                ),
                array(
                    'is_true' => ($correct_answer === 'B') ? 'yes' : 'no',
                    'title' => $option_b,
                    'value' => uniqid()
                ),
                array(
                    'is_true' => ($correct_answer === 'C') ? 'yes' : 'no',
                    'title' => $option_c,
                    'value' => uniqid()
                ),
                array(
                    'is_true' => ($correct_answer === 'D') ? 'yes' : 'no',
                    'title' => $option_d,
                    'value' => uniqid()
                )
            );
            
            // Save answers
            update_post_meta($question_id, '_lp_answer_options', $answers);
            
            // Add question to quiz
            $quiz_questions = get_post_meta($quiz_id, '_lp_quiz_questions', true);
            if (empty($quiz_questions)) {
                $quiz_questions = array();
            }
            
            $quiz_questions[$question_id] = array('question_id' => $question_id);
            update_post_meta($quiz_id, '_lp_quiz_questions', $quiz_questions);
            
            $count++;
        }
        
        if (!empty($errors)) {
            return array(
                'success' => true,
                'count' => $count,
                'message' => sprintf(
                    __('Processed %d questions successfully, but encountered %d errors: %s', 'learnpress-quiz-bulk-upload'),
                    $count,
                    count($errors),
                    implode('; ', $errors)
                )
            );
        }
        
        return array('success' => true, 'count' => $count, 'message' => '');
        
    } catch (Exception $e) {
        return array('success' => false, 'message' => $e->getMessage());
    }
}