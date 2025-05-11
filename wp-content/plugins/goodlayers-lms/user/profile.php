<?php 
    global $current_user;
    $user_id = $current_user->data->ID;
    // Include the Twilio OTP verification functionality
    require_once WP_PLUGIN_DIR . '/twilio-otp-verification/otp-verification.php';

?>
<h3 class="gdlr-lms-admin-head"><?php esc_html_e('Edit Profile', 'gdlr-lms'); ?></h3>

<?php 
// Handle OTP verification for phone update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_phone_otp') {
    $otp = isset($_POST['otp_code']) ? trim($_POST['otp_code']) : '';
    $phone = isset($_POST['field_value']) ? trim($_POST['field_value']) : '';
    $phone_prefix = isset($_POST['phone_prefix']) ? trim($_POST['phone_prefix']) : '';
    $full_phone_number = format_phone_number($phone_prefix . $phone);
    
    // Store these values in session to maintain them through redirect
    $_SESSION['temp_phone'] = $phone;
    $_SESSION['temp_phone_prefix'] = $phone_prefix;
    
    // Verify the OTP
    $verify_result = verify_otp($full_phone_number, $otp);
    
    if ($verify_result['success']) {
        // OTP verified - update the phone number
        $result = update_user_meta($user_id, 'phone', $phone_prefix . $phone);
        
        if (!is_wp_error($result)) {
            echo '<div class="gdlr-lms-success">' . esc_html__('Phone number verified and updated successfully.', 'gdlr-lms') . '</div>';
            
            // Clear temporary session data
            unset($_SESSION['temp_phone']);
            unset($_SESSION['temp_phone_prefix']);
        } else {
            echo '<div class="gdlr-lms-error">' . esc_html__('Error updating phone number.', 'gdlr-lms') . '</div>';
        }
    } else {
        echo '<div class="gdlr-lms-error">' . $verify_result['message'] . '</div>';
    }
}

// Handle OTP sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_phone_otp') {
    $phone = isset($_POST['field_value']) ? trim($_POST['field_value']) : '';
    $phone_prefix = isset($_POST['phone_prefix']) ? trim($_POST['phone_prefix']) : '';
    $full_phone_number = format_phone_number($phone_prefix . $phone);
    
    if ($full_phone_number) {
        $otp_result = send_otp_verification($full_phone_number);
        
        if ($otp_result['success']) {
            // Store the phone number in a session for verification
            $_SESSION['temp_phone_update'] = [
                'phone' => $phone,
                'prefix' => $phone_prefix,
                'full_number' => $full_phone_number
            ];
            echo '<div class="gdlr-lms-success">' . $otp_result['message'] . '</div>';
        } else {
            echo '<div class="gdlr-lms-error">' . $otp_result['message'] . '</div>';
        }
    } else {
        echo '<div class="gdlr-lms-error">' . esc_html__('Невалиден формат на телефонен номер.', 'gdlr-lms') . '</div>';
    }
}

// Regular field update handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['field_name']) && $_POST['field_name'] !== 'phone' && 
   (!isset($_POST['action']) || ($_POST['action'] !== 'send_phone_otp' && $_POST['action'] !== 'verify_phone_otp'))) {
    $field = sanitize_key($_POST['field_name']);
    $value = sanitize_text_field($_POST['field_value']);

    if ($field === 'user_email') {
        // Store the pending email for verification instead of updating directly
        update_user_meta($user_id, 'pending_email', $value);
        
        // Generate verification token and send email
        $token = wp_generate_password(32, false);
    $expiry = time() + (10 * 24 * 60 * 60); // 10 days expiry 
        update_user_meta($user_id, 'email_verification_token', $token);
        update_user_meta($user_id, 'email_verification_expiry', $expiry);
        
        // Send verification email => IN functions.php

        
        $site_name = get_bloginfo('name');
        $subject = sprintf(__('Verify your email address for %s', 'gdlr-lms'), $site_name);
        $message = sprintf(
            __('Please click the link below to verify your email address:', 'gdlr-lms') . "\r\n\r\n" .
            '%s' . "\r\n\r\n" .
            __('This link will expire in 10 days.', 'gdlr-lms') . "\r\n\r\n" .
            __('If you did not request this change, you can safely ignore this email.', 'gdlr-lms'),
            $verification_link
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        $mail_sent = wp_mail($value, $subject, $message, $headers);
        
        if ($mail_sent) {
            echo '<div class="gdlr-lms-success">' . __('To finish updating your email, please check your email and click the link to verify.', 'gdlr-lms') . '</div>';
        } else {
            echo '<div class="gdlr-lms-error">' . __('Error sending verification email. Please try again.', 'gdlr-lms') . '</div>';
        }
    } else {
        $result = update_user_meta($user_id, $field, $value);
        
        if (!is_wp_error($result)) {
            echo '<div class="gdlr-lms-success">' . esc_html($_POST['field_label']) . ' updated successfully.</div>';
        } else {
            echo '<div class="gdlr-lms-error">Error updating ' . esc_html($_POST['field_label']) . '</div>';
        }
    }
}

function render_editable_field($label, $field_name, $value, $type = 'text') {
    $id = esc_attr($field_name);
    echo '<form method="post" class="editable-form" style="display:flex;align-items:center;gap:10px;margin-bottom:15px;">';
    echo '<label for="' . $id . '" style="width:120px;">' . esc_html($label) . '</label>';
    
    // Special handling for phone field
    if ($field_name === 'phone') {
        echo '<div class="phone-input-container" style="display:flex;flex:1;align-items:center;">';
        // Extract prefix and number if already formatted
        $phone_parts = extract_phone_parts($value);
        $prefix = $phone_parts['prefix'] ?: '+359';
        $number = $phone_parts['number'] ?: $value;
        
        // Use session data if available (to maintain values through errors)
        if (isset($_SESSION['temp_phone'])) {
            $number = $_SESSION['temp_phone'];
            $prefix = $_SESSION['temp_phone_prefix'];
        }
        
        // Phone prefix dropdown (initially hidden)
        echo '<select name="phone_prefix" class="phone-prefix" style="display:none;width:30%;">
                <option value="+359" ' . selected($prefix, '+359', false) . '>🇧🇬 +359 (Bulgaria)</option>
                <!-- other options -->
            </select>';
        echo '<input type="text" name="field_value" id="' . $id . '" value="' . esc_attr($number) . '" readonly style="flex:1;" />';
        echo '</div>';
        
        // OTP verification field (initially hidden)
        echo '<div class="otp-container" style="display:none;flex:1;align-items:center;margin-left:10px;">';
        echo '<input type="text" name="otp_code" placeholder="' . esc_attr__('Въведи код', 'gdlr-lms') . '" class="otp-input" style="width:100%;" />';
        echo '</div>';
        
        // Hidden fields for OTP action
        echo '<input type="hidden" name="action" class="action-field" value="" />';
    } else {
        echo '<input type="' . esc_attr($type) . '" name="field_value" id="' . $id . '" value="' . esc_attr($value) . '" readonly style="flex:1;" />';
    }
    
    echo '<input type="hidden" name="field_name" value="' . esc_attr($field_name) . '" />';
    echo '<input type="hidden" name="field_label" value="' . esc_attr($label) . '" />';
    echo '<button type="button" class="edit-btn" title="Edit" style="font-size:18px;background:none;border:none;cursor:pointer;">
            <i class="fa fa-pencil-alt"></i>
          </button>';
          
    // For phone field, we'll have separate buttons for different stages
    if ($field_name === 'phone') {
        echo '<button type="submit" class="next-btn" style="display:none;background:none;border:none;cursor:pointer;font-size:18px;">
                <i class="fa-solid fa-circle-check"></i>
              </button>';
        echo '<button type="button" class="resend-otp" style="display:none;background:none;border:none;cursor:pointer;font-size:14px;color:blue;margin-left:5px;">
                ' . esc_html__('Препрати код', 'gdlr-lms') . '
              </button>';
    } else {
        echo '<button type="submit" class="save-btn" style="display:none;background:none;border:none;cursor:pointer;font-size:18px;">
                <i class="fa-solid fa-circle-check"></i>
              </button>';
    }
    
    echo '</form>';
}

// Helper function to extract country code and phone number
function extract_phone_parts($phone) {
    $common_prefixes = array('+359', '+44', '+33', '+49', '+39', '+34', '+31', '+32', '+41', '+43', '+46', '+45', '+47', '+358', '+351', '+30', '+48', '+420', '+36', '+1');
    
    foreach ($common_prefixes as $prefix) {
        if (strpos($phone, $prefix) === 0) {
            return array(
                'prefix' => $prefix,
                'number' => substr($phone, strlen($prefix))
            );
        }
    }
    
    return array(
        'prefix' => '',
        'number' => $phone
    );
}
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

<!-- JavaScript to toggle editable fields and handle OTP verification -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.editable-form').forEach(form => {
            const input = form.querySelector('input[name="field_value"]');
            const editBtn = form.querySelector('.edit-btn');
            const saveBtn = form.querySelector('.save-btn');
            const nextBtn = form.querySelector('.next-btn');
            const actionField = form.querySelector('.action-field');
            
            // Phone specific elements
            const phonePrefix = form.querySelector('.phone-prefix');
            const otpContainer = form.querySelector('.otp-container');
            const resendOtpBtn = form.querySelector('.resend-otp');
            const otpInput = form.querySelector('.otp-input');
            
            // Standard edit button behavior for all fields
            if (editBtn) {
                editBtn.addEventListener('click', () => {
                    input.removeAttribute('readonly');
                    input.focus();
                    
                    // For regular fields
                    if (saveBtn) {
                        saveBtn.style.display = 'inline-block';
                    }
                    
                    // For phone field
                    if (nextBtn) {
                        nextBtn.style.display = 'inline-block';
                    }
                    
                    editBtn.style.display = 'none';
                    
                    // Show phone prefix dropdown if this is a phone field
                    if (phonePrefix) {
                        phonePrefix.style.display = 'block';
                    }
                });
            }
            
            // Special handling for phone field
            if (form.querySelector('input[name="field_name"]')?.value === 'phone') {
                // Next button sends OTP
                if (nextBtn) {
                    nextBtn.addEventListener('click', function(e) {
                        // If OTP container is visible, change action to verify
                        if (otpContainer && otpContainer.style.display === 'flex') {
                            actionField.value = 'verify_phone_otp';
                        } else {
                            actionField.value = 'send_phone_otp';
                        }
                    });
                }
                
                // After form is submitted for OTP sending, show OTP input field
                if (otpContainer && otpContainer.style.display === 'flex') {
                    // We're in OTP verification mode
                    nextBtn.style.display = 'inline-block';
                    resendOtpBtn.style.display = 'inline-block';
                    editBtn.style.display = 'none';
                }
                
                // Handle resend OTP button
                if (resendOtpBtn) {
                    resendOtpBtn.addEventListener('click', () => {
                        actionField.value = 'send_phone_otp';
                    });
                }
            }
        });
        
        // Check if we need to show OTP form after page load (after OTP has been sent)
        const successMsg = document.querySelector('.gdlr-lms-success');
        if (successMsg && (successMsg.textContent.includes('SMS') || successMsg.textContent.includes('OTP'))) {
            document.querySelectorAll('form.editable-form').forEach(form => {
                const fieldName = form.querySelector('input[name="field_name"]')?.value;
                
                if (fieldName === 'phone') {
                    const input = form.querySelector('input[name="field_value"]');
                    const editBtn = form.querySelector('.edit-btn');
                    const nextBtn = form.querySelector('.next-btn');
                    const phonePrefix = form.querySelector('.phone-prefix');
                    const otpContainer = form.querySelector('.otp-container');
                    const resendOtpBtn = form.querySelector('.resend-otp');
                    const actionField = form.querySelector('.action-field');
                    
                    // Show OTP verification UI
                    input.removeAttribute('readonly');
                    phonePrefix.style.display = 'block';
                    otpContainer.style.display = 'flex';
                    nextBtn.style.display = 'inline-block';
                    resendOtpBtn.style.display = 'inline-block';
                    editBtn.style.display = 'none';
                    
                    // Set action for verification
                    actionField.value = 'verify_phone_otp';
                }
            });
        }
        
        // Also handle error messages to maintain OTP state
        const errorMsg = document.querySelector('.gdlr-lms-error');
        if (errorMsg) {
            document.querySelectorAll('form.editable-form').forEach(form => {
                const fieldName = form.querySelector('input[name="field_name"]')?.value;
                
                if (fieldName === 'phone') {
                    const input = form.querySelector('input[name="field_value"]');
                    const editBtn = form.querySelector('.edit-btn');
                    const nextBtn = form.querySelector('.next-btn');
                    const phonePrefix = form.querySelector('.phone-prefix');
                    const otpContainer = form.querySelector('.otp-container');
                    const resendOtpBtn = form.querySelector('.resend-otp');
                    const actionField = form.querySelector('.action-field');
                    const otpInput = form.querySelector('.otp-input');
                    
                    // Show OTP verification UI
                    input.removeAttribute('readonly');
                    phonePrefix.style.display = 'block';
                    otpContainer.style.display = 'flex';
                    nextBtn.style.display = 'inline-block';
                    resendOtpBtn.style.display = 'inline-block';
                    editBtn.style.display = 'none';
                    
                    // Set action for verification
                    actionField.value = 'verify_phone_otp';
                    
                    // Clear OTP field for retry
                    if (otpInput) {
                        otpInput.value = '';
                    }
                }
            });
        }
    });
</script>

<?php
// Display verification status messages
if (isset($_GET['verification_status'])) {
    if ($_GET['verification_status'] === 'success') {
        echo '<div class="gdlr-lms-success">' . __('Your email has been successfully verified!', 'gdlr-lms') . '</div>';
    } else if ($_GET['verification_status'] === 'error') {
        echo '<div class="gdlr-lms-error">' . __('Email verification failed. Please try again.', 'gdlr-lms') . '</div>';
    }
}
?>


<div class="gdlr-lms-form">
    <?php
        render_editable_field(__('Имe *', 'gdlr-lms'), 'first_name', gdlr_lms_get_user_info($user_id, 'first_name'));
        render_editable_field(__('Фамилия *', 'gdlr-lms'), 'last_name', gdlr_lms_get_user_info($user_id, 'last_name'));
        // Always get the most current email directly from user object
        $current_email = get_userdata($user_id)->user_email;
        render_editable_field(__('Email *', 'gdlr-lms'), 'user_email', $current_email);        render_editable_field(__('Телефон', 'gdlr-lms'), 'phone', gdlr_lms_get_user_info($user_id, 'phone'));
        render_editable_field(__('Facebook/Insta', 'gdlr-lms'), 'facebook', gdlr_lms_get_user_info($user_id, 'facebook'));
        render_editable_field(__('Град', 'gdlr-lms'), 'city', gdlr_lms_get_user_info($user_id, 'city'));
        render_editable_field(__('Квартал', 'gdlr-lms'), 'area', gdlr_lms_get_user_info($user_id, 'area'));

        if ($current_user->roles[0] === 'administrator' || $current_user->roles[0] === 'instructor') {
            render_editable_field(__('Full Biography', 'gdlr-lms'), 'author-biography', gdlr_lms_get_user_info($user_id, 'author-biography'), 'textarea');
            render_editable_field(__('Location', 'gdlr-lms'), 'location', gdlr_lms_get_user_info($user_id, 'location'));
            render_editable_field(__('Position', 'gdlr-lms'), 'position', gdlr_lms_get_user_info($user_id, 'position'));
            render_editable_field(__('Current Work', 'gdlr-lms'), 'current-work', gdlr_lms_get_user_info($user_id, 'current-work'));
            render_editable_field(__('Past Work', 'gdlr-lms'), 'past-work', gdlr_lms_get_user_info($user_id, 'past-work'));
            render_editable_field(__('Specialist In', 'gdlr-lms'), 'specialist', gdlr_lms_get_user_info($user_id, 'specialist'));
            render_editable_field(__('Experience', 'gdlr-lms'), 'experience', gdlr_lms_get_user_info($user_id, 'experience'));
            render_editable_field(__('Social Network', 'gdlr-lms'), 'social-network', gdlr_lms_get_user_info($user_id, 'social-network'), 'textarea');
        }
    ?>
</div>