<?php
/**
* Plugin Name: Marquee Running Text
* Plugin URI: https://www.github.com/meddil/wp-marquee_text
* Description: Add marquee running text to page
* Version: 0.0.1
* Author: Med Maaoui
* Author URI: https://www.github.com/meddil
**/

/*
// initial code
function marquee_content() {
    $content = '<marquee>Running text here</marquee>';
    return $content;
}
//add_shortcode('marquee', 'marquee_content');
*/
// css/plugin_page_diplay.css

add_action('wp_enqueue_scripts', 'marquee_enqueue_styles');
function marquee_enqueue_styles() {
    wp_enqueue_style(
        'marquee-style',
        plugin_dir_url(__FILE__) . 'css/plugin_page_display.css',
        [],
        '1.0'
    );
}



// add shortcut in dashboard under "appearence"
add_action('admin_menu', 'marquee_running_text_menu');
function marquee_running_text_menu() {
    add_menu_page('Marquee Running Text', 'Marquee Running Text', 'manage_options', 'marquee-running-text', 'marquee_running_text_page');
}


//interface
function marquee_running_text_page() {

    if (isset($_POST['marquee_save'])) {
        check_admin_referer('marquee_save_settings');

        update_option('marquee_text', sanitize_text_field($_POST['marquee_text']));
        update_option('marquee_direction', sanitize_text_field($_POST['marquee_direction']));
        update_option('marquee_position', sanitize_text_field($_POST['marquee_position']));

        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $marquee_text      = get_option('marquee_text', '');
    $marquee_direction = get_option('marquee_direction', 'left');
    $marquee_position  = get_option('marquee_position', 'header');
    ?>
    <div class="wrap">
        <h1>Marquee Running Text</h1>

        <form method="post">
            <?php wp_nonce_field('marquee_save_settings'); ?>

            <table class="form-table">

                <tr>
                    <th scope="row">Marquee Text</th>
                    <td>
                        <textarea name="marquee_text" rows="4" cols="50"><?php echo esc_textarea($marquee_text); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Direction</th>
                    <td>
                        <select name="marquee_direction">
                            <option value="left" <?php selected($marquee_direction, 'left'); ?>>Right → Left</option>
                            <option value="right" <?php selected($marquee_direction, 'right'); ?>>Left → Right</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Position</th>
                    <td>
                        <select name="marquee_position">
                            <option value="header" <?php selected($marquee_position, 'header'); ?>>Header (Top)</option>
                            <option value="footer" <?php selected($marquee_position, 'footer'); ?>>Footer (Sticky)</option>
                            <option value="disabled" <?php selected($marquee_position, 'disabled'); ?>>Disabled</option>
                        </select>
                    </td>
                </tr>

            </table>

            <p>
                <input type="submit" name="marquee_save" class="button button-primary" value="Save Settings">
            </p>
        </form>
    </div>
    <?php
}

add_action('wp_body_open', 'marquee_render');
add_action('wp_footer', 'marquee_render');

function marquee_render() {
    $text      = get_option('marquee_text');
    $direction = get_option('marquee_direction', 'left');
    $position  = get_option('marquee_position', 'disabled');

    if (!$text || $position === 'disabled') {
        return;
    }

    // render in the correct hook
    $current_hook = current_filter();
    if (
        ($position === 'header' && $current_hook !== 'wp_body_open') ||
        ($position === 'footer' && $current_hook !== 'wp_footer')
    ) {
        return;
    }

    $pos_class = $position === 'footer' ? 'marquee-footer' : 'marquee-header';
    $dir_class = $direction === 'right' ? 'marquee-right' : 'marquee-left';

    echo '<div class="marquee-container ' . esc_attr($pos_class . ' ' . $dir_class) . '">';
    echo '  <div class="marquee-track">';
    echo '    <div class="marquee-text">' . esc_html($text) . '</div>';
    echo '  </div>';
    echo '</div>';
}


/*

*/

/*
//shortcode generate
function marquee_text_shortcode($atts, $content = null) {
    $marquee_text = get_option('marquee_text');
    if ($marquee_text) {
        return '<marquee behavior="scroll" direction="left">' . esc_html($marquee_text) . '</marquee>';
    } else {
        return '';
    }
}
//add_shortcode('running_text', 'marquee_text_shortcode');
*/
