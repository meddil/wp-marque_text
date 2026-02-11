<?php
/**
* Plugin Name: Marquee Running Text
* Plugin URI: https://www.github.com/meddil/wp-marquee_text
* Description: Add marquee running text to page
* Version: 0.0.6
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

// Helper function for hex color sanitization
function marquee_sanitize_hex_color($color) {
    if (function_exists('sanitize_hex_color')) {
        return sanitize_hex_color($color);
    }
    // Fallback 
    if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
        return $color;
    }
    return '#000000';
}

// admin settings page
function marquee_running_text_page() {
    // security check
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    

    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_wpnonce'])) {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'marquee_manager')) {
            wp_die('Security check failed.');
        }

        if (isset($_POST['action']) && $_POST['action'] === 'add') {
            $marquees = get_option('marquee_texts', []);
            $next_number = count($marquees) + 1;
            $marquees[] = [
                'title' => 'New Marquee #' . $next_number,
                'text' => '',
                'direction' => 'left',
                'position' => 'header',
                'bg_color' => '#000000',
                'text_color' => '#ffffff',
                'close_button' => false,
                'hide_days' => 3,
                'enabled' => false,
            ];
            update_option('marquee_texts', $marquees);
            echo '<div class="updated"><p>New marquee added.</p></div>';
        } elseif (isset($_POST['action']) && $_POST['action'] === 'save' && isset($_POST['index'])) {
            $index = intval($_POST['index']);
            $marquees = get_option('marquee_texts', []);
            if (isset($marquees[$index])) {
                $marquees[$index] = [
                    'title' => sanitize_text_field($_POST['title']),
                    'text' => sanitize_text_field($_POST['text']),
                    'direction' => sanitize_text_field($_POST['direction']),
                    'position' => sanitize_text_field($_POST['position']),
                    'bg_color' => marquee_sanitize_hex_color($_POST['bg_color']),
                    'text_color' => marquee_sanitize_hex_color($_POST['text_color']),
                    'close_button' => !empty($_POST['close_button']),
                    'hide_days' => max(-1, intval($_POST['hide_days'])),
                    'enabled' => !empty($_POST['enabled']),
                ];
                update_option('marquee_texts', $marquees);
                echo '<div class="updated"><p>Marquee saved.</p></div>';
            }

        } elseif (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['index'])) {
            $index = intval($_POST['index']);
            $marquees = get_option('marquee_texts', []);
            if (isset($marquees[$index])) {
                array_splice($marquees, $index, 1);
                update_option('marquee_texts', $marquees);
                echo '<div class="updated"><p>Marquee deleted.</p></div>';
            }
        }
    }

    $marquees = get_option('marquee_texts', []);
    $edit_index = null;

    if (isset($_GET['edit'])) {
        $edit_val = $_GET['edit'];
        if ($edit_val === 'new') {
            // blank form
            $blank = [
                'title' => 'New Marquee',
                'text' => '',
                'direction' => 'left',
                'position' => 'header',
                'bg_color' => '#000000',
                'text_color' => '#ffffff',
                'close_button' => false,
                'hide_days' => 3,
                'enabled' => true,
            ];
            marquee_edit_form($blank, 'new');
            return;
        } else {
            $edit_index = intval($edit_val);
        }
    }

    if ($edit_index !== null && isset($marquees[$edit_index])) {
        marquee_edit_form($marquees[$edit_index], $edit_index);
    } else {
        marquee_list_view($marquees);
    }
}

// List View
function marquee_list_view($marquees) {
    ?>
    <div class="wrap">
        <h1>Marquee Running Texts</h1>
        <form method="post">
            <?php wp_nonce_field('marquee_manager'); ?>
            <input type="hidden" name="action" value="add" />
            <button type="submit" class="page-title-action">Add New Marquee</button>
        </form>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($marquees)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center; color:#72777c;">
                            — No marquees configured —
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($marquees as $i => $m): ?>
                    <tr>
                        <td><?php echo esc_html($m['title']); ?></td>
                        <td><?php echo esc_html(ucfirst($m['position'])); ?></td>
                        <td><?php echo !empty($m['enabled']) ? 'Enabled' : 'Disabled'; ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg('edit', $i)); ?>" class="button button-small">Edit</a>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this marquee?');">
                                <?php wp_nonce_field('marquee_manager'); ?>
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="index" value="<?php echo esc_attr($i); ?>" />
                                <button type="submit" class="button button-small button-link-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Edit Form
function marquee_edit_form($marquee, $index) {
    ?>
    <div class="wrap">
        <h1><?php echo $index === 'new' ? 'Add New Marquee' : 'Edit Marquee'; ?></h1>
        <a href="<?php echo admin_url('admin.php?page=marquee-running-text'); ?>">&laquo; Back to list</a>

        <form method="post" style="margin-top: 20px;">
            <?php wp_nonce_field('marquee_manager'); ?>
            <input type="hidden" name="action" value="save" />
            <input type="hidden" name="index" value="<?php echo esc_attr($index); ?>" />

            <table class="form-table">
                <tr>
                    <th scope="row">Status</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enabled" value="1" <?php checked($marquee['enabled']); ?> />
                            Enabled
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Title</th>
                    
                    <td>
                        <input type="text" name="title" value="<?php echo esc_attr($marquee['title']); ?>" required />
                        <!-- <p class="description">Used only in admin dashboard.</p>
                    -->
                    </td>
                </tr>
                <tr>
                    <th scope="row">Marquee Text</th>
                    <td>
                        <textarea name="text" rows="4" cols="50" required><?php echo esc_textarea($marquee['text']); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Direction</th>
                    <td>
                        <select name="direction">
                            <option value="left" <?php selected($marquee['direction'], 'left'); ?>>Right → Left</option>
                            <option value="right" <?php selected($marquee['direction'], 'right'); ?>>Left → Right</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Position</th>
                    <td>
                        <select name="position">
                            <option value="header" <?php selected($marquee['position'], 'header'); ?>>Header (Top)</option>
                            <option value="footer" <?php selected($marquee['position'], 'footer'); ?>>Footer (Sticky)</option>
                            <option value="disabled" <?php selected($marquee['position'], 'disabled'); ?>>Disabled</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Background Color</th>
                    <td><input type="color" name="bg_color" value="<?php echo esc_attr($marquee['bg_color']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Text Color</th>
                    <td><input type="color" name="text_color" value="<?php echo esc_attr($marquee['text_color']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Enable Close Button</th>
                    <td>
                        <label>
                            <input type="checkbox" name="close_button" value="1" <?php checked($marquee['close_button']); ?> />
                            Show close button
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Hide Duration After Close</th>
                    <td>
                        <input type="number" name="hide_days" value="<?php echo esc_attr($marquee['hide_days']); ?>" min="-1" step="1" style="width:80px;" />
                        <p class="description">Days to hide. Use <code>-1</code> to never show again.</p>
                    </td>
                </tr>
            </table>

            <p>
                <button type="submit" class="button button-primary">Save Marquee</button>
                <a href="<?php echo admin_url('admin.php?page=marquee-running-text'); ?>" class="button">Cancel</a>
            </p>
        </form>
    </div>
    <?php
}

// Render marquee on frontend
add_action('wp_body_open', 'marquee_render_header');
add_action('wp_footer', 'marquee_render_footer');

function marquee_render_header() {
    marquee_render('header');
}

function marquee_render_footer() {
    marquee_render('footer');
}

function marquee_render($position) {
    $marquees = get_option('marquee_texts', []);
    if (empty($marquees)) return;

    // Filter & sort: oldest first (so rendered top-to-bottom in DOM)
    $to_render = [];
    foreach ($marquees as $i => $m) {
        if (!empty($m['enabled']) && $m['position'] === $position && !empty($m['text'])) {
            $to_render[] = [$i, $m]; // preserve index for localStorage key
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

        echo '<div id="' . esc_attr($id) . '" class="marquee-container ' . esc_attr($pos_class . ' ' . $dir_class) . '" style="' . esc_attr($container_style) . '">';

        // Close button
        if (!empty($m['close_button'])) {
            $close_pos = $m['direction'] === 'right' ? 'marquee-close-right' : 'marquee-close-left';
            $close_style = sprintf(
                'background-color: %s; color: %s;',
                esc_attr($m['bg_color']),
                esc_attr($m['text_color'])
            );
            echo '<button type="button" class="marquee-close ' . esc_attr($close_pos) . '" 
                        aria-label="Close" 
                        style="' . esc_attr($close_style) . '">✕</button>';
        }

        echo '<div class="marquee-track">';
        echo '  <div class="marquee-text">' . esc_html($m['text']) . '</div>';
        echo '</div>';
        echo '</div>';

        // JS config per marquee
        if (!empty($m['close_button'])) {
            $hide_days = (int) $m['hide_days'];
            ?>
            <script>
            (function(id, hideDays) {
                const container = document.getElementById(id);
                if (!container) return;

                const KEY = 'marquee_closed_' + id;
                const now = Date.now();

                let shouldHide = false;
                const stored = localStorage.getItem(KEY);

                if (hideDays === -1) {
                    if (stored === 'never') shouldHide = true;
                } else if (hideDays >= 0 && stored) {
                    if (now < parseInt(stored)) shouldHide = true;
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
                            localStorage.setItem(KEY, (now + hideDays * 86400000).toString());
                        }
                    });
                }
            })('<?php echo $id; ?>', <?php echo json_encode($hide_days); ?>);
            </script>
            <?php
        }
    }
}