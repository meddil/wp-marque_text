<?php
if (!defined('ABSPATH')) exit;

/**
 * Sanitize hex color (fallback for older WP)
 */
function marquee_sanitize_hex_color($color) {
    if (function_exists('sanitize_hex_color')) {
        return sanitize_hex_color($color);
    }
    if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
        return $color;
    }
    return '#000000';
}

/**
 * Get all marquees from database
 */
function marquee_get_all() {
    return get_option('marquee_texts', []);
}

/**
 * Save all marquees
 */
function marquee_save_all($marquees) {
    update_option('marquee_texts', $marquees);
}

/**
 * Uninstall cleanup
 */
function marquee_uninstall() {
    delete_option('marquee_texts');
}
