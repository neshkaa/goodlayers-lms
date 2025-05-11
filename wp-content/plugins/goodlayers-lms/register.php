<?php 
session_start();
require_once WP_PLUGIN_DIR . '/twilio-otp-verification/otp-verification.php';

// Pre-fill Step 1 fields
$value_first_name = isset($_SESSION['registration']['first_name']) ? esc_attr($_SESSION['registration']['first_name']) : '';
$value_last_name = isset($_SESSION['registration']['last_name']) ? esc_attr($_SESSION['registration']['last_name']) : '';
$value_email = isset($_SESSION['registration']['email']) ? esc_attr($_SESSION['registration']['email']) : '';
$value_phone = isset($_SESSION['registration']['phone']) ? esc_attr($_SESSION['registration']['phone']) : '';
$value_country_code = isset($_SESSION['registration']['country_code']) ? esc_attr($_SESSION['registration']['country_code']) : '+359';
$value_consent = isset($_SESSION['registration']['consent']) ? esc_attr($_SESSION['registration']['consent']) : '';

// Initialize error messages
$error = array();
$step = isset($_SESSION['registration_step']) ? $_SESSION['registration_step'] : 1;

// Handle OTP resend
if (isset($_POST['resend_otp']) && $_SESSION['registration_step'] == 2) {
    $full_phone_number = $_SESSION['registration']['full_phone_number'];
    
    if ($full_phone_number) {
        $otp_result = send_otp_verification($full_phone_number);
        
        if ($otp_result['success']) {
            $success_message = $otp_result['message'];
        } else {
            $error[] = $otp_result['message'];
        }
    } else {
        $error[] = esc_html__('Невалиден телефонен номер.', 'gdlr-lms');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle "Back" button - Key fix #1
    if (isset($_POST['back']) && $_POST['back'] === 'back-to-step-1') {
        $_SESSION['registration_step'] = 1;
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'step-1') {
        // Validate Step 1
        if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['phone'])) {
            $error[] = esc_html__('Моля, попълнете всички задължителни полета.', 'gdlr-lms');
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $error[] = esc_html__('Email адресът не е валиден.', 'gdlr-lms');
        } elseif (email_exists($_POST['email'])) {
            $error[] = esc_html__('Вече има регистриран потребител с този email адрес.', 'gdlr-lms');    
        } else {
            // Store registration data in session
            $_SESSION['registration'] = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'country_code' => $_POST['country_code'],
                'consent' => isset($_POST['consent']) ? $_POST['consent'] : ''
            ];
            
            // Format and send OTP
            $full_phone_number = format_phone_number($_POST['country_code'] . $_POST['phone']);
            
            if (!$full_phone_number) {
                $error[] = esc_html__('Невалиден формат на телефонен номер.', 'gdlr-lms');
            } else {
                $otp_result = send_otp_verification($full_phone_number);
                
                if ($otp_result['success']) {
                    // Store the formatted phone number for later verification
                    $_SESSION['registration']['full_phone_number'] = $full_phone_number;
                    $_SESSION['registration_step'] = 2;
                    
                    // Store success message to display on next page
                    $_SESSION['otp_message'] = $otp_result['message'];
                    
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                } else {
                    $error[] = $otp_result['message'];
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'step-2') {
        // Get the OTP and phone number
        $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
        $full_phone_number = $_SESSION['registration']['full_phone_number'];
        
        if (empty($otp)) {
            $error[] = esc_html__('Моля, въведете получения SMS код.', 'gdlr-lms');
        } else {
            // Fix #2: Verify OTP correctly
            $verify_result = verify_otp($full_phone_number, $otp);
            
            if (!$verify_result['success']) {
                $error[] = $verify_result['message'];
            } else {
                // OTP verified - continue with password validation and registration
                if (empty($_POST['password']) || empty($_POST['re-password']) || $_POST['password'] !== $_POST['re-password']) {
                    $error[] = esc_html__('Паролите не съвпадат или липсват.', 'gdlr-lms');
                } else {
                    // Generate a username if not provided
                    $username = !empty($_POST['username']) ? $_POST['username'] : sanitize_user($_SESSION['registration']['email']);
                    
                    // Registration logic
                    $user_id = wp_insert_user([
                        'user_login' => $username,
                        'user_pass' => $_POST['password'],
                        'user_email' => $_SESSION['registration']['email'],
                        'role' => 'unverified_user'
                    ]);

                    if (is_wp_error($user_id)) {
                        $error[] = esc_html__('Моля, попълвайте само латински символи в полетата за потребителско име и парола.', 'gdlr-lms');
                    } else {
                        // Keep existing user meta update code
                        if(!empty($_SESSION['registration']['first_name'])) {
                            update_user_meta($user_id, 'first_name', esc_attr($_SESSION['registration']['first_name']));
                        }
                        if(!empty($_SESSION['registration']['last_name'])) {
                            update_user_meta($user_id, 'last_name', esc_attr($_SESSION['registration']['last_name']));
                        }
                        if(!empty($_POST['facebook'])) {
                            update_user_meta($user_id, 'facebook', esc_attr($_POST['facebook']));
                        }
                        if(!empty($_POST['city'])) {
                            update_user_meta($user_id, 'city', esc_attr($_POST['city']));
                        }
                        if(!empty($_POST['area'])) {
                            update_user_meta($user_id, 'area', esc_attr($_POST['area']));
                        }
                        if(!empty($_SESSION['registration']['phone'])) {
                            update_user_meta($user_id, 'phone', esc_attr($_SESSION['registration']['phone']));
                        }
                        
                        wp_new_user_notification($user_id, null, 'both');
                        
                        // Automatically log in the user
                        wp_set_current_user($user_id);
                        wp_set_auth_cookie($user_id);
                        
                        // Clean up session after successful registration
                        unset($_SESSION['registration']);
                        unset($_SESSION['registration_step']);
                        unset($_SESSION['otp_message']);
                        
                        $redirect_url = home_url('/registration-success/');

                        header('Location: ' . $redirect_url);
                        exit;
                        
                    }
                }
            }
        }
    }
}

get_header(); 
?>

<div id="primary" class="content-area gdlr-lms-primary-wrapper">
<div id="content" class="site-content" role="main">
<?php
    if (function_exists('gdlr_lms_get_header') && !empty($gdlr_lms_option['show-header']) && $gdlr_lms_option['show-header'] == 'enable') {
        gdlr_lms_get_header();
    }
?>
    <div class="gdlr-lms-content">
        <div class="gdlr-lms-container gdlr-lms-container">
            <div class="gdlr-lms-item">
                <?php
                    if (!empty($error)) {
                        echo '<div class="gdlr-lms-error">' . implode("<br />", $error) . '</div>'; 
                    }       
                ?>

                <?php if ($step == 1): ?>
                <form class="gdlr-lms-form" method="post" action="">
                    <p><input type="text" name="first_name" placeholder="<?php esc_html_e('Име', 'gdlr-lms'); ?>" value="<?php echo $value_first_name; ?>" /></p>
                    <p><input type="text" name="last_name" placeholder="<?php esc_html_e('Фамилия', 'gdlr-lms'); ?>" value="<?php echo $value_last_name; ?>" /></p>
                    <p><input type="text" name="email" id="email" placeholder="<?php esc_html_e('Email', 'gdlr-lms'); ?>" value="<?php echo $value_email; ?>" /></p>
                    <div>
                        <div style="display: flex;">
                            <select name="country_code" id="country_code" style="width: 30%;">
                                <option value="+359" <?php selected($value_country_code, '+359'); ?>>🇧🇬 +359 (Bulgaria)</option>
                                <option value="+44" <?php selected($value_country_code, '+44'); ?>>🇬🇧 +44 (UK)</option>
                                <option value="+33" <?php selected($value_country_code, '+33'); ?>>🇫🇷 +33 (France)</option>
                                <option value="+49" <?php selected($value_country_code, '+49'); ?>>🇩🇪 +49 (Germany)</option>
                                <option value="+39" <?php selected($value_country_code, '+39'); ?>>🇮🇹 +39 (Italy)</option>
                                <option value="+34" <?php selected($value_country_code, '+34'); ?>>🇪🇸 +34 (Spain)</option>
                                <option value="+31" <?php selected($value_country_code, '+31'); ?>>🇳🇱 +31 (Netherlands)</option>
                                <option value="+32" <?php selected($value_country_code, '+32'); ?>>🇧🇪 +32 (Belgium)</option>
                                <option value="+41" <?php selected($value_country_code, '+41'); ?>>🇨🇭 +41 (Switzerland)</option>
                                <option value="+43" <?php selected($value_country_code, '+43'); ?>>🇦🇹 +43 (Austria)</option>
                                <option value="+46" <?php selected($value_country_code, '+46'); ?>>🇸🇪 +46 (Sweden)</option>
                                <option value="+45" <?php selected($value_country_code, '+45'); ?>>🇩🇰 +45 (Denmark)</option>
                                <option value="+47" <?php selected($value_country_code, '+47'); ?>>🇳🇴 +47 (Norway)</option>
                                <option value="+358" <?php selected($value_country_code, '+358'); ?>>🇫🇮 +358 (Finland)</option>
                                <option value="+351" <?php selected($value_country_code, '+351'); ?>>🇵🇹 +351 (Portugal)</option>
                                <option value="+30" <?php selected($value_country_code, '+30'); ?>>🇬🇷 +30 (Greece)</option>
                                <option value="+48" <?php selected($value_country_code, '+48'); ?>>🇵🇱 +48 (Poland)</option>
                                <option value="+420" <?php selected($value_country_code, '+420'); ?>>🇨🇿 +420 (Czech Republic)</option>
                                <option value="+36" <?php selected($value_country_code, '+36'); ?>>🇭🇺 +36 (Hungary)</option>
                                <option value="+1" <?php selected($value_country_code, '+1'); ?>>🇺🇸 +1 (USA/Canada)</option>
                            </select>
                            <input type="text" name="phone" id="phone" placeholder="Enter phone number" value="<?php echo $value_phone; ?>" style="width: 70%;" />
                        </div>
                    </div>
                    <input type="hidden" name="action" value="step-1" />
                    <input type="submit" value="<?php esc_html_e('Продължи', 'gdlr-lms'); ?>" />
                </form>

                <?php elseif ($step == 2): ?>
                <?php 
                    // Display OTP message if available
                    if (isset($_SESSION['otp_message'])) {
                        echo '<div class="gdlr-lms-success">' . $_SESSION['otp_message'] . '</div>';
                        unset($_SESSION['otp_message']); // Clear after displaying
                    }
                    
                    if (!empty($success_message)) {
                        echo '<div class="gdlr-lms-success">' . $success_message . '</div>';
                    }
                ?>
                <form class="gdlr-lms-form" method="post" action="">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="flex-grow: 1;">
                            <input type="text" name="otp" placeholder="<?php esc_html_e('Въведи SMS код *', 'gdlr-lms'); ?>" />
                        </div>
                        <div>
                            <input type="submit" name="resend_otp" value="<?php esc_html_e('Изпрати нов код', 'gdlr-lms'); ?>" class="resend-button" />
                        </div>
                    </div>
                    <p><input type="password" name="password" placeholder="<?php esc_html_e('Избери парола *', 'gdlr-lms'); ?>" /></p>
                    <p><input type="password" name="re-password" placeholder="<?php esc_html_e('Повтори паролата *', 'gdlr-lms'); ?>" /></p>
                    <p><input type="text" name="facebook" placeholder="<?php esc_html_e('Facebook / Instagram', 'gdlr-lms'); ?>" /></p>
                    <p><input type="text" name="city" placeholder="<?php esc_html_e('Град', 'gdlr-lms'); ?>" /></p>
                    <p><input type="text" name="area" placeholder="<?php esc_html_e('Квартал', 'gdlr-lms'); ?>" /></p>

                    <input type="hidden" name="action" value="step-2" />
                    <div style="display: flex; gap: 10px;">
                        <input type="submit" value="<?php esc_html_e('Регистрация', 'gdlr-lms'); ?>" />
                    </div>
                </form>
                
                <!-- Fixed back button - Key fix #1 -->
                <form class="gdlr-lms-form" method="post" action="">
                    <input type="hidden" name="back" value="back-to-step-1" />
                    <input type="submit" value="<?php esc_html_e('⬅ Назад', 'gdlr-lms'); ?>" />
                </form>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php 
get_footer(); 
?>