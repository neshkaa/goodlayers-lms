<?php
/**
 * Plugin Name: GoodLMS Access Control
 * Description: Controls access to GoodLMS course parts with public/private sections and quiz progression
 * Version: 1.0
 * Author: Your Name
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class GDLMS_Access_Control {
    
    // Define which parts are public vs restricted
    private $public_parts = array(1, 4, 5);
    private $restricted_parts = array(2, 3);
    
    // Map which quiz unlocks which part
    private $quiz_unlocks = array(
        1 => 2, // Quiz for part 1 unlocks part 2
        2 => 3  // Quiz for part 2 unlocks part 3
    );
    
    public function __construct() {
        // Access control filters
        add_filter('gdlms_course_item_access', array($this, 'control_course_item_access'), 10, 2);
        add_filter('gdlms_course_content_access', array($this, 'control_course_content_access'), 10, 2);
        
        // Quiz completion handling
        add_action('gdlms_after_quiz_submission', array($this, 'process_quiz_completion'), 10, 3);
        
        // Add custom meta box to mark items with their part number
        add_action('add_meta_boxes', array($this, 'add_part_meta_box'));
        add_action('save_post', array($this, 'save_part_meta'));
        
        // Add lock icons to navigation
        add_filter('gdlms_course_structure_item', array($this, 'add_lock_icons_to_nav'), 10, 3);
        
        // Style for lock icons
        add_action('wp_head', array($this, 'add_lock_icon_styles'));
    }
    
    /**
     * Control access to course items based on part number and user status
     */
    public function control_course_item_access($access, $course_item_id) {
        // Always allow access for administrators
        if (current_user_can('administrator')) {
            return true;
        }
        
        // Get the part number for this item
        $part = get_post_meta($course_item_id, '_gdlms_course_part', true);
        if (empty($part)) {
            // If no part is specified, use default access rules
            return $access;
        }
        
        // If it's a public part, grant access
        if (in_array((int)$part, $this->public_parts)) {
            return true;
        }
        
        // For restricted parts, first check if user is logged in
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Check if user has completed required quizzes
        $user_id = get_current_user_id();
        
        // For part 2, check if quiz 1 is completed
        if ((int)$part === 2) {
            $quiz_id = $this->get_quiz_for_part(1);
            if ($quiz_id && !$this->is_quiz_completed($quiz_id, $user_id)) {
                return false;
            }
            return true;
        }
        
        // For part 3, check if quiz 2 is completed
        if ((int)$part === 3) {
            $quiz_id = $this->get_quiz_for_part(2);
            if ($quiz_id && !$this->is_quiz_completed($quiz_id, $user_id)) {
                return false;
            }
            return true;
        }
        
        // Fall back to default access rules
        return $access;
    }
    
    /**
     * Control access to course content
     */
    public function control_course_content_access($access, $course_id) {
        // For course content listings, we'll use the default access rules
        return $access;
    }
    
    /**
     * Process quiz completion to unlock next parts
     */
    public function process_quiz_completion($quiz_id, $user_id, $score) {
        // Get the part for this quiz
        $quiz_part = get_post_meta($quiz_id, '_gdlms_course_part', true);
        if (empty($quiz_part)) {
            return;
        }
        
        // Check if this quiz unlocks a part
        if (!isset($this->quiz_unlocks[(int)$quiz_part])) {
            return;
        }
        
        $part_to_unlock = $this->quiz_unlocks[(int)$quiz_part];
        
        // Find course this quiz belongs to
        $course_id = $this->get_course_for_quiz($quiz_id);
        if (!$course_id) {
            return;
        }
        
        // Find all items from the part to unlock
        $items_to_unlock = $this->get_items_by_part($course_id, $part_to_unlock);
        
        // Update user progress to unlock these items
        foreach ($items_to_unlock as $item_id) {
            gdlms_update_user_progress($user_id, $item_id, 'active');
        }
    }
    
    /**
     * Add meta box to set which part an item belongs to
     */
    public function add_part_meta_box() {
        add_meta_box(
            'gdlms_course_part',
            'Course Part',
            array($this, 'render_part_meta_box'),
            array('gdlms-course-item', 'gdlms-quiz'),
            'side'
        );
    }
    
    /**
     * Render the part meta box
     */
    public function render_part_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('gdlms_course_part_nonce', 'gdlms_course_part_nonce');
        
        // Get current value
        $part = get_post_meta($post->ID, '_gdlms_course_part', true);
        
        echo '<select name="gdlms_course_part">';
        echo '<option value="">Select Part</option>';
        for ($i = 1; $i <= 5; $i++) {
            $access_type = in_array($i, $this->public_parts) ? 'Public' : 'Restricted';
            printf(
                '<option value="%1$d" %2$s>Part %1$d (%3$s)</option>',
                $i,
                selected($part, $i, false),
                $access_type
            );
        }
        echo '</select>';
        
        echo '<p class="description">';
        echo 'Parts 1, 4, 5 are public. Parts 2, 3 require registration and quiz completion.';
        echo '</p>';
    }
    
    /**
     * Save the part meta value
     */
    public function save_part_meta($post_id) {
        // Security checks
        if (!isset($_POST['gdlms_course_part_nonce']) || 
            !wp_verify_nonce($_POST['gdlms_course_part_nonce'], 'gdlms_course_part_nonce')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save the part number
        if (isset($_POST['gdlms_course_part'])) {
            update_post_meta($post_id, '_gdlms_course_part', intval($_POST['gdlms_course_part']));
        }
    }
    
    /**
     * Add lock icons to course navigation
     */
    public function add_lock_icons_to_nav($item_data, $course_id, $item_id) {
        // Only modify if this is a course item or quiz
        $post_type = get_post_type($item_id);
        if (!in_array($post_type, array('gdlms-course-item', 'gdlms-quiz'))) {
            return $item_data;
        }
        
        // Get the part number
        $part = get_post_meta($item_id, '_gdlms_course_part', true);
        if (empty($part) || !in_array((int)$part, $this->restricted_parts)) {
            return $item_data;
        }
        
        // If user is not logged in, add lock icon
        if (!is_user_logged_in()) {
            $register_url = wp_registration_url();
            $lock_icon = ' <a href="' . esc_url($register_url) . '" class="gdlms-lock-icon" title="Register to unlock this content"><span class="dashicons dashicons-lock"></span></a>';
            $item_data['title'] .= $lock_icon;
        } else {
            // Check if user has completed the prerequisite quiz
            $user_id = get_current_user_id();
            $previous_part = (int)$part - 1;
            $quiz_id = $this->get_quiz_for_part($previous_part);
            
            if ($quiz_id && !$this->is_quiz_completed($quiz_id, $user_id)) {
                $quiz_url = get_permalink($quiz_id);
                $lock_icon = ' <a href="' . esc_url($quiz_url) . '" class="gdlms-lock-icon" title="Complete the previous quiz to unlock this content"><span class="dashicons dashicons-lock"></span></a>';
                $item_data['title'] .= $lock_icon;
            }
        }
        
        return $item_data;
    }
    
    /**
     * Add styles for lock icons
     */
    public function add_lock_icon_styles() {
        ?>
        <style type="text/css">
            .gdlms-lock-icon {
                display: inline-block;
                vertical-align: middle;
                margin-left: 5px;
                color: #c0392b;
                text-decoration: none;
            }
            .gdlms-lock-icon:hover {
                color: #e74c3c;
            }
            .gdlms-lock-icon .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }
        </style>
        <?php
    }
    
    /**
     * Check if a quiz has been completed by a user
     */
    private function is_quiz_completed($quiz_id, $user_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'gdlms_quiz_submission';
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE quiz_id = %d AND user_id = %d AND status = 'complete'",
            $quiz_id, $user_id
        ));
        
        return $count > 0;
    }
    
    /**
     * Get the quiz for a specific part
     */
    private function get_quiz_for_part($part) {
        $args = array(
            'post_type' => 'gdlms-quiz',
            'meta_key' => '_gdlms_course_part',
            'meta_value' => $part,
            'posts_per_page' => 1
        );
        
        $quizzes = get_posts($args);
        
        if (!empty($quizzes)) {
            return $quizzes[0]->ID;
        }
        
        return false;
    }
    
    /**
     * Get the course a quiz belongs to
     */
    private function get_course_for_quiz($quiz_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'gdlms_course_structure';
        $course_id = $wpdb->get_var($wpdb->prepare(
            "SELECT course_id FROM $table WHERE item_id = %d",
            $quiz_id
        ));
        
        return $course_id;
    }
    
    /**
     * Get all course items belonging to a specific part
     */
    private function get_items_by_part($course_id, $part) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'gdlms_course_structure';
        
        $items = $wpdb->get_col($wpdb->prepare(
            "SELECT item_id FROM $table WHERE course_id = %d",
            $course_id
        ));
        
        $part_items = array();
        
        foreach ($items as $item_id) {
            $item_part = get_post_meta($item_id, '_gdlms_course_part', true);
            if ((int)$item_part === (int)$part) {
                $part_items[] = $item_id;
            }
        }
        
        return $part_items;
    }
}

// Initialize the plugin
new GDLMS_Access_Control();