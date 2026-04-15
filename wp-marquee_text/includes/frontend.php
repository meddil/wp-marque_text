<?php
if (!defined('ABSPATH')) exit;

// Enqueue frontend assets
add_action('wp_enqueue_scripts', 'marquee_frontend_assets');
function marquee_frontend_assets() {
    wp_enqueue_style('marquee-style', MARQUEE_PLUGIN_URL . 'assets/css/plugin_page_display.css', [], MARQUEE_VERSION);
    wp_enqueue_script('marquee-js', MARQUEE_PLUGIN_URL . 'assets/js/marquee.js', [], MARQUEE_VERSION, true);
}

// Render marquees at appropriate hooks
add_action('wp_body_open', 'marquee_render_header');
add_action('wp_footer', 'marquee_render_footer');

function marquee_render_header() {
    marquee_render('header');
}

function marquee_render_footer() {
    marquee_render('footer');
}

function marquee_render($position) {
    $marquees = marquee_get_all();
    if (empty($marquees)) return;

    $to_render = [];
    foreach ($marquees as $i => $m) {
        if (!empty($m['enabled']) && $m['position'] === $position && !empty($m['text'])) {
            $to_render[] = [$i, $m];
        }
    }

    if (empty($to_render)) return;

    foreach ($to_render as [$index, $m]) {
        $id = 'marquee-' . $index;
        $dir_class = $m['direction'] === 'right' ? 'marquee-right' : 'marquee-left';
        $pos_class = $position === 'footer' ? 'marquee-footer' : 'marquee-header';
        $container_style = sprintf(
            'background-color: %s; color: %s;',
            esc_attr($m['bg_color']),
                                   esc_attr($m['text_color'])
        );

        echo '<div id="' . esc_attr($id) . '" class="marquee-container ' . esc_attr($pos_class . ' ' . $dir_class) . '" style="' . esc_attr($container_style) . '" data-hide-days="' . esc_attr($m['hide_days']) . '">';

        if (!empty($m['close_button'])) {
            $close_pos = $m['direction'] === 'right' ? 'marquee-close-right' : 'marquee-close-left';
            $close_style = sprintf(
                'background-color: %s; color: %s;',
                esc_attr($m['bg_color']),
                                   esc_attr($m['text_color'])
            );
            echo '<button type="button" class="marquee-close ' . esc_attr($close_pos) . '" aria-label="Close" style="' . esc_attr($close_style) . '">✕</button>';
        }

        echo '<div class="marquee-track">';
        echo '  <div class="marquee-text">' . esc_html($m['text']) . '</div>';
        echo '</div>';
        echo '</div>';
    }
}
