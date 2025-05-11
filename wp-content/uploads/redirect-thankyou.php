<?php
// Load WordPress environment if necessary
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Ensure Polylang functions are available
if (function_exists('pll_current_language')) {
    $lang = pll_current_language(); // Get the current Polylang language slug
} else {
    // Fallback to default language if Polylang is not available
    $lang = 'en';
}

// Determine the correct thank-you page
$thankYouPage = ($lang === 'bg') ? '/thankyou/' : '/en/thank-you/';

// Redirect to the thank-you page
header("Location: $thankYouPage");
exit;
?>
