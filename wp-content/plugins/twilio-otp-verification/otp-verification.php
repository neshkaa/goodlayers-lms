<?php
/*
Plugin Name: Twilio OTP Verification
Description: Handles OTP verification via Twilio.
Version: 1.0
Author: snej
*/

// Load Twilio SDK if not already loaded
if (!class_exists('Twilio\Rest\Client')) {
    require_once plugin_dir_path(__FILE__) . 'src/Twilio/autoload.php'; // Load Twilio SDK
}

// Add Twilio settings page in WP Admin
function add_twilio_settings_page() {
    add_options_page(
        'Twilio Settings', // Page title
        'Twilio',          // Menu title
        'manage_options',  // Capability
        'twilio-settings', // Menu slug
        'twilio_settings_page_html' // Callback function
    );
}
add_action('admin_menu', 'add_twilio_settings_page');

// Define the callback function to render the settings page
function twilio_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Twilio Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('twilio_options_group');
            do_settings_sections('twilio-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Format phone number to ensure E.164 compliance
if (!function_exists('format_phone_number')) {
    function format_phone_number($phone_number) {
        $phone_number = trim($phone_number);
        $phone_number = preg_replace('/[^\d+]/', '', $phone_number);

        if (!preg_match('/^\+/', $phone_number)) {
            $phone_number = '+' . $phone_number;
        }

        return (strlen(preg_replace('/\D/', '', $phone_number)) < 10) ? false : $phone_number;
    }
}

// Validate phone number using Twilio's Lookups API
function validate_phone_number($twilio, $phone_number) {
    try {
        $lookup = $twilio->lookups->v2->phoneNumbers($phone_number)
                                      ->fetch(["type" => ["carrier"]]);

        // Ensure the number exists
        if (empty($lookup->phoneNumber)) {
            return "❌ Невалиден телефонен номер.";
//          return "❌ Invalid phone number.";
        }

        // Allow "mobile" OR empty carrier type (some numbers may not return carrier info)
        $carrierType = $lookup->carrier['type'] ?? ''; // Default to empty string if undefined
        if (!empty($carrierType) && $carrierType !== 'mobile') {
              return "❌ Tози телефонен номер не е класифициран като мобилен номер.";
      //      return "❌ This phone number is not classified as a mobile number.";
        }

        return true; // The number is valid
    } catch (Exception $e) {
        return "❌ Twilio Lookups API Error: " . $e->getMessage();
    }
}

// Add a function to send OTP
function send_otp_verification($phone_number) {
    $sid = get_option('twilio_account_sid'); 
    $token = get_option('twilio_auth_token'); 
    $service_sid = get_option('twilio_service_sid');
    
    $twilio = new Twilio\Rest\Client($sid, $token);
    
    // Validate phone number
    $validation_result = validate_phone_number($twilio, $phone_number);
    
    if ($validation_result !== true) {
        return array(
            'success' => false,
            'message' => $validation_result
        );
    }
    
    try {
        $verification = $twilio->verify->v2->services($service_sid)
                                    ->verifications
                                    ->create($phone_number, "sms");
        
        if ($verification->status === 'pending') {
            return array(
                'success' => true,
                'message' => "✅ SMS код е изпратен на " . $phone_number
            );
        } else {
            return array(
                'success' => false,
                'message' => "❌ Грешка при изпращане на SMS код."
            );
        }
    } catch (Exception $e) {
    $error_message = $e->getMessage();
    
    // Check specifically for invalid phone number errors
    if (strpos($error_message, "Invalid parameter `To`") !== false) {
        return array(
            'success' => false,
            'message' => "❌ Телефонният номер е невалиден. "
        );
    } else {
        // Keep the original message for other errors
        return array(
            'success' => false,
            'message' => "❌ Грешка при верификацията на телефонен номер: " . $e->getMessage()
        );
    }
  }
}

// Add a function to verify OTP
function verify_otp($phone_number, $otp) {
    $sid = get_option('twilio_account_sid'); 
    $token = get_option('twilio_auth_token'); 
    $service_sid = get_option('twilio_service_sid');
    
    $twilio = new Twilio\Rest\Client($sid, $token);
    
    try {
        $verification_check = $twilio->verify->v2->services($service_sid)
                                            ->verificationChecks
                                            ->create([
                                                'to' => $phone_number,
                                                'code' => $otp
                                            ]);
        
        if ($verification_check->status === 'approved') {
            return array(
                'success' => true,
                'message' => "✅ Телефонният номер е потвърден успешно."
            );
        } else {
            return array(
                'success' => false,
                'message' => "❌ Невалиден код. Моля, опитайте отново."
            );
        }
    } catch (Exception $e) {
        return array(
            'success' => false,
            'message' => "❌ Грешка при потвърждаването: " . $e->getMessage()
        );
    }
}

// OTP Verification Shortcode
function otp_verification_shortcode() {
    ob_start();

    $sid = get_option('twilio_account_sid'); 
    $token = get_option('twilio_auth_token'); 
    $service_sid = get_option('twilio_service_sid'); 

    $twilio = new Twilio\Rest\Client($sid, $token);

    $error = '';
    $phone_number = format_phone_number($_GET['phone'] ?? '');

    if (!$phone_number) {
        echo "<p style='color:red;'>❌ Invalid phone number format. Please go back and enter a valid number.</p>";
        return ob_get_clean();
    }


// Validate phone number using Twilio Lookups API
    $validation_result = validate_phone_number($twilio, $phone_number);
    if ($validation_result !== true) {
        $error = $validation_result;
    }

// Send OTP if the number is valid
    if (!$error) {
        try {
            $verification = $twilio->verify->v2->services($service_sid)
                                               ->verifications
                                               ->create($phone_number, "sms");

            if ($verification->status === 'pending') {
                echo "<p>✅  Моля въведи кода, който ще получиш на телефон <strong>$phone_number</strong>. </p>";
            } else {
                $error = "❌ Error sending OTP. Please try again.";
            }

        } catch (Exception $e) {
            $error = "❌ Failed to send OTP. Error: " . $e->getMessage();
        }
    }

// Handle OTP verification
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['otp'])) {
        $otp = trim($_POST['otp']);
        $phone_number = format_phone_number($_POST['phone']);

        if (!$phone_number) {
            $error = "❌ Invalid phone number format.";
        }

        try {
            // Verify OTP
            $verification_check = $twilio->verify->v2->services($service_sid)
                                                     ->verificationChecks
                                                     ->create([
                                                         'to' => $phone_number,
                                                         'code' => $otp
                                                     ]);

            if ($verification_check->status === 'approved') {
                wp_redirect(home_url('/registration-success')); // Redirect after successful verification
                exit;
            } else {
                $error = "❌ Невалиден код. Моля, опитайте отново.";
            }

        } catch (Exception $e) {
            $error = "❌ Грешка при потвърждаването на кода. " . $e->getMessage();
        }
    }

    ?>
    <form method="post">
        <input type="hidden" name="phone" value="<?php echo esc_attr($phone_number); ?>">
        <label for="otp">Въведи код:</label>
        <input type="text" name="otp" required>
        <button type="submit">Потвърди</button>
        
        /* GoodLMS Registration form Page 2 fields */
            <p>
        <input type="text" name="otp" placeholder="Enter OTP" required />
    </p>

    <p>
        <input type="text" name="facebook" placeholder="Facebook / Instagram" required />
    </p>
    
       <p>
        <button type="button" onclick="history.back();">← Back</button>
    </p>
        
    </form>

    <?php if (!empty($error)) : ?>
        <p style='color:red;'><?php echo $error; ?></p>
    <?php endif; ?>

    <?php
    return ob_get_clean();
}

add_shortcode('otp_verification', 'otp_verification_shortcode');

//Register Twilio Settings
function twilio_register_settings() {
    register_setting('twilio_options_group', 'twilio_account_sid');
    register_setting('twilio_options_group', 'twilio_auth_token');
    register_setting('twilio_options_group', 'twilio_service_sid');

}
add_action('admin_init', 'twilio_register_settings');

//Add Twilio Fields
function twilio_settings_init() {
    add_settings_section(
        'twilio_settings_section',
        'Twilio API Credentials',
        'twilio_settings_section_callback',
        'twilio-settings'
    );

    add_settings_field(
        'twilio_account_sid',
        'Account SID',
        'twilio_account_sid_render',
        'twilio-settings',
        'twilio_settings_section'
    );

    add_settings_field(
        'twilio_auth_token',
        'Auth Token',
        'twilio_auth_token_render',
        'twilio-settings',
        'twilio_settings_section'
    );

            add_settings_field(
        'twilio_service_sid',
        'Service SID',
        'twilio_service_sid_render',
        'twilio-settings',
        'twilio_settings_section'
    );
    
}
add_action('admin_init', 'twilio_settings_init');

function twilio_account_sid_render() {
    $value = get_option('twilio_account_sid', '');
    echo '<input type="text" name="twilio_account_sid" value="' . esc_attr($value) . '">';
}

function twilio_auth_token_render() {
    $value = get_option('twilio_auth_token', '');
    echo '<input type="password" name="twilio_auth_token" value="' . esc_attr($value) . '">';
}

function twilio_service_sid_render() {
    $value = get_option('twilio_service_sid', '');
    echo '<input type="password" name="twilio_service_sid" value="' . esc_attr($value) . '">';
}

function twilio_settings_section_callback() {
    echo 'Enter your Twilio API credentials below:';
}

add_action('admin_menu', 'add_twilio_settings_page');
?>
