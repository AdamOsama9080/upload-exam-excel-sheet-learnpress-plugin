<?php
defined('ABSPATH') || exit;

function lpqbu_process_excel_file($file_path, $quiz_id) {
    if (!file_exists($file_path)) {
        return array('success' => false, 'message' => __('File not found', 'learnpress-quiz-bulk-upload'));
    }
    
    if (!defined('LP_QUESTION_CPT')) {
        error_log('LearnPress Quiz Bulk Upload: LP_QUESTION_CPT not defined.');
        return array('success' => false, 'message' => __('LearnPress is not properly loaded.', 'learnpress-quiz-bulk-upload'));
    }
    
    // Load PhpSpreadsheet
    if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
        $vendor_path = plugin_dir_path(__FILE__) . '../../vendor/autoload.php';
        if (file_exists($vendor_path)) {
            require_once $vendor_path;
        } else {
            return array(
                'success' => false,
                'message' => __('PhpSpreadsheet library missing', 'learnpress-quiz-bulk-upload')
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
            if (empty(array_filter($row))) continue;
            
            // Validate row
            if (count($row) < 7) {
                $errors[] = sprintf(__('Row %d: Missing columns', 'learnpress-quiz-bulk-upload'), $index + 1);
                continue;
            }
            
            // Prepare data
            $question_data = array(
                'question' => sanitize_text_field($row[0]),
                'options' => array(
                    'A' => sanitize_text_field($row[1]),
                    'B' => sanitize_text_field($row[2]),
                    'C' => sanitize_text_field($row[3]),
                    'D' => sanitize_text_field($row[4])
                ),
                'answer' => strtoupper(sanitize_text_field($row[5])),
                'marks' => floatval($row[6])
            );
            
            // Validate answer
            if (!in_array($question_data['answer'], array('A', 'B', 'C', 'D'))) {
                $errors[] = sprintf(__('Row %d: Invalid answer', 'learnpress-quiz-bulk-upload'), $index + 1);
                continue;
            }
            
            // Create question
            $question_id = wp_insert_post(array(
                'post_title' => $question_data['question'],
                'post_type' => LP_QUESTION_CPT,
                'post_status' => 'publish'
            ));
            
            if (is_wp_error($question_id)) {
                $errors[] = sprintf(__('Row %d: %s', 'learnpress-quiz-bulk-upload'), $index + 1, $question_id->get_error_message());
                continue;
            }
            
            // Set question metadata
            update_post_meta($question_id, '_lp_type', 'multi_choice');
            update_post_meta($question_id, '_lp_mark', $question_data['marks']);
            
            // Prepare answers
            $answers = array();
            foreach ($question_data['options'] as $key => $option) {
                $answers[] = array(
                    'is_true' => ($key == $question_data['answer']) ? 'yes' : 'no',
                    'title' => $option,
                    'value' => uniqid()
                );
            }
            
            update_post_meta($question_id, '_lp_answer_options', $answers);
            
            // Add to quiz
            $quiz_questions = get_post_meta($quiz_id, '_lp_quiz_questions', true) ?: array();
            $quiz_questions[$question_id] = array('question_id' => $question_id);
            update_post_meta($quiz_id, '_lp_quiz_questions', $quiz_questions);
            
            $count++;
        }
        
        if (!empty($errors)) {
            return array(
                'success' => true,
                'count' => $count,
                'message' => implode('<br>', $errors)
            );
        }
        
        return array('success' => true, 'count' => $count);
        
    } catch (Exception $e) {
        return array('success' => false, 'message' => $e->getMessage());
    }
}