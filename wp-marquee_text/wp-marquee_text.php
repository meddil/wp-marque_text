<?php
/**
* Plugin Name: Marquee Running Text
* Plugin URI: https://www.github.com/meddil/wp-marquee_text
* Description: Add marquee running text to page
* Version: 0.0.7
* Author: Med Maaoui
* Author URI: https://www.github.com/meddil
**/

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('MARQUEE_VERSION', '0.0.7');
define('MARQUEE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MARQUEE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Check if files exist
$helpers_file = MARQUEE_PLUGIN_DIR . 'includes/helpers.php';
$admin_file = MARQUEE_PLUGIN_DIR . 'includes/admin.php';
$frontend_file = MARQUEE_PLUGIN_DIR . 'includes/frontend.php';

// Include helper functions
if (file_exists($helpers_file)) {
    require_once $helpers_file;
} else {
    // Defining helper functions here if file missing
    function marquee_get_all() {
        return get_option('marquee_texts', []);
    }
    function marquee_sanitize_hex_color($color) {
        if (function_exists('sanitize_hex_color')) {
            return sanitize_hex_color($color);
        }
        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            return $color;
        }
        return '#000000';
    }
}

if (is_admin()) {
    if (file_exists($admin_file)) {
        require_once $admin_file;
    }
}

if (!is_admin()) {
    if (file_exists($frontend_file)) {
        require_once $frontend_file;
    }
}
