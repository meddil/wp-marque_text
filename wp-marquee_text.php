<?php
/**
* Plugin Name: Marquee Running Text
* Plugin URI: https://www.github.com/meddil/wp-marquee_text
* Description: ...
* Version: 0.1
* Author: Med Maaoui
* Author URI: https://www.github.com/meddil
**/

function marquee_content() {
    $content = '<marquee>Running text here</marquee>';
    return $content;
}
add_shortcode('marquee', 'marquee_content');