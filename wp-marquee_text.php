<?php
/**
* Plugin Name: Marquee Running Text
* Plugin URI: https://www.github.com/meddil/wp-marquee_text
* Description: Add marquee running text to page
* Version: 0.0.1
* Author: Med Maaoui
* Author URI: https://www.github.com/meddil
**/


// Enqueue styles
add_action('wp_enqueue_scripts', 'marquee_enqueue_styles');
function marquee_enqueue_styles() {
    wp_enqueue_style(
        'marquee-style',
        plugin_dir_url(__FILE__) . 'css/plugin_page_display.css',
        [],
        '1.1'
    );
}

// Admin menu
add_action('admin_menu', 'marquee_running_text_menu');
function marquee_running_text_menu() {
    add_menu_page(
        'Marquee Running Text',
        'Marquee Running Text',
        'manage_options',
        'marquee-running-text',
        'marquee_running_text_page'
    );
}

// Admin settings page
function marquee_running_text_page() {
    if (isset($_POST['marquee_save'])) {
        check_admin_referer('marquee_save_settings');

        update_option('marquee_text', sanitize_text_field($_POST['marquee_text']));
        update_option('marquee_direction', sanitize_text_field($_POST['marquee_direction']));
        update_option('marquee_position', sanitize_text_field($_POST['marquee_position']));

        $bg_color = sanitize_hex_color($_POST['marquee_bg_color'] ?? '#000000');
        $text_color = sanitize_hex_color($_POST['marquee_text_color'] ?? '#ffffff');
        $close_enabled = isset($_POST['marquee_close_button']) ? '1' : '0';
        $hide_days = intval($_POST['marquee_hide_days'] ?? 3);
        if ($hide_days < -1) $hide_days = -1; // enforce valid range
        update_option('marquee_hide_days', $hide_days);

        update_option('marquee_bg_color', $bg_color);
        update_option('marquee_text_color', $text_color);
        update_option('marquee_close_button', $close_enabled);

        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $marquee_text         = get_option('marquee_text', '');
    $marquee_direction    = get_option('marquee_direction', 'left');
    $marquee_position     = get_option('marquee_position', 'header');
    $marquee_bg_color     = get_option('marquee_bg_color', '#000000');
    $marquee_text_color   = get_option('marquee_text_color', '#ffffff');
    $marquee_close_button = (get_option('marquee_close_button', '0') === '1');
    $marquee_hide_days = get_option('marquee_hide_days', '3');
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
                <tr>
                    <th scope="row">Background Color</th>
                    <td>
                        <input type="color" name="marquee_bg_color" value="<?php echo esc_attr($marquee_bg_color); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Text Color</th>
                    <td>
                        <input type="color" name="marquee_text_color" value="<?php echo esc_attr($marquee_text_color); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Enable Close Button</th>
                    <td>
                        <label>
                            <input type="checkbox" name="marquee_close_button" value="1" <?php checked($marquee_close_button); ?> />
                            Show close button (styled with above colors)
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Hide Duration After Close</th>
                    <td>
                        <input type="number" name="marquee_hide_days" 
                            value="<?php echo esc_attr(get_option('marquee_hide_days', '3')); ?>" 
                            min="-1" step="1" style="width: 80px;" />
                        <p class="description">
                            Number of days to hide after close. Use <code>-1</code> to never show again.
                        </p>
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

// Render marquee on frontend
add_action('wp_body_open', 'marquee_render');
add_action('wp_footer', 'marquee_render');

function marquee_render() {
    $text       = get_option('marquee_text');
    $direction  = get_option('marquee_direction', 'left');
    $position   = get_option('marquee_position', 'disabled');
    $bg_color   = get_option('marquee_bg_color', '#000000');
    $text_color = get_option('marquee_text_color', '#ffffff');
    $show_close = (get_option('marquee_close_button', '0') === '1');

    if (!$text || $position === 'disabled') {
        return;
    }

    $current_hook = current_filter();
    if (
        ($position === 'header' && $current_hook !== 'wp_body_open') ||
        ($position === 'footer' && $current_hook !== 'wp_footer')
    ) {
        return;
    }

    $pos_class = $position === 'footer' ? 'marquee-footer' : 'marquee-header';
    $dir_class = $direction === 'right' ? 'marquee-right' : 'marquee-left';
    $container_style = "background-color: {$bg_color}; color: {$text_color};";

    echo '<div class="marquee-container ' . esc_attr($pos_class . ' ' . $dir_class) . '" style="' . esc_attr($container_style) . '">';

    if ($show_close) {
        $close_pos_class = ($direction === 'right') ? 'marquee-close-right' : 'marquee-close-left';
        $hide_days = (int) get_option('marquee_hide_days', 3);
        // Pass to JS
        echo '<div class="marquee-js-config" data-hide-days="' . esc_attr($hide_days) . '" style="display:none;"></div>';
        $close_style = "background-color: {$bg_color}; color: {$text_color};";
        echo '<button type="button" class="marquee-close ' . esc_attr($close_pos_class) . '" 
                    aria-label="Close announcement" 
                    style="' . esc_attr($close_style) . '">✕</button>';
    }

    echo '<div class="marquee-track">';
    echo '  <div class="marquee-text">' . esc_html($text) . '</div>';
    echo '</div>';

    echo '</div>';

    // JS for close + 3-day persistence
    if ($show_close) {
        ?>
        <script>
    (function() {
        const container = document.querySelector('.marquee-container');
        if (!container) return;

        const configEl = document.querySelector('.marquee-js-config');
        const hideDays = configEl ? parseInt(configEl.dataset.hideDays, 10) : 3;
        const KEY = 'marquee_closed_until';
        const now = Date.now();

        let shouldHide = false;

        if (hideDays === -1) {
            // Never show again if previously closed
            if (localStorage.getItem(KEY) !== null) {
                shouldHide = true;
            }
        } else if (hideDays >= 0) {
            const closedUntil = localStorage.getItem(KEY);
            if (closedUntil && now < parseInt(closedUntil)) {
                shouldHide = true;
            }
        }

        if (shouldHide) {
            container.style.display = 'none';
            return;
        }

        const btn = container.querySelector('.marquee-close');
        if (btn) {
            btn.addEventListener('click', function() {
                container.style.display = 'none';
                if (hideDays === -1) {
                    localStorage.setItem(KEY, 'never');
                } else if (hideDays > 0) {
                    localStorage.setItem(KEY, (now + (hideDays * 86400000)).toString());
                } else {
                    // hideDays = 0 to hide only this session (optional)
                    localStorage.removeItem(KEY);
                }
            });
        }
    })();
    </script>
        <?php
    }
}
