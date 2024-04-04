<?php
/**
* Plugin Name: Marquee Running Text
* Plugin URI: https://www.github.com/meddil/wp-marquee_text
* Description: Add marquee running text to page
* Version: 0.1
* Author: Med Maaoui
* Author URI: https://www.github.com/meddil
**/

// initial code
function marquee_content() {
    $content = '<marquee>Running text here</marquee>';
    return $content;
}
add_shortcode('marquee', 'marquee_content');





// add shortcut in dashboard under "appearence"
function marquee_plugin_menu() {
    add_submenu_page(
        'themes.php', 
        'Add A Marquee Running Text', 
        'Marquee Running Text', 
        'manage_options',
        'marquee-plugin',
        'marquee_plugin_page_display'
    );
}

add_action('admin_menu', 'marquee_plugin_menu');

function marquee_plugin_page_display() {
    echo '<div class="wrap"><h1>Add Marquee Running Text</h1></div>';
}