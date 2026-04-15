<?php
if (!defined('ABSPATH')) exit;

// Enqueue admin styles (if needed)
add_action('admin_enqueue_scripts', 'marquee_admin_styles');
function marquee_admin_styles($hook) {
    if ($hook !== 'toplevel_page_marquee-running-text') {
        return;
    }
    wp_enqueue_style('marquee-admin', MARQUEE_PLUGIN_URL . 'assets/css/admin.css', [], MARQUEE_VERSION);
}

// Add admin menu
add_action('admin_menu', 'marquee_admin_menu');
function marquee_admin_menu() {
    add_menu_page(
        __('Marquee Running Text', 'marquee-text'),
                  __('Marquee Running Text', 'marquee-text'),
                  'manage_options',
                  'marquee-running-text',
                  'marquee_admin_page'
    );
}

// Admin page router
function marquee_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions.');
    }

    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_wpnonce'])) {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'marquee_manager')) {
            wp_die('Security check failed.');
        }

        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    marquee_add();
                    break;
                case 'save':
                    if (isset($_POST['index'])) {
                        marquee_save(intval($_POST['index']));
                    }
                    break;
                case 'delete':
                    if (isset($_POST['index'])) {
                        marquee_delete(intval($_POST['index']));
                    }
                    break;
            }
        }
    }

    // Display view
    $marquees = marquee_get_all();
    $edit_index = isset($_GET['edit']) ? $_GET['edit'] : null;

    if ($edit_index === 'new') {
        marquee_edit_form_new();
    } elseif (is_numeric($edit_index) && isset($marquees[intval($edit_index)])) {
        marquee_edit_form($marquees[intval($edit_index)], intval($edit_index));
    } else {
        marquee_list_view($marquees);
    }
}

// Add new marquee
function marquee_add() {
    $marquees = marquee_get_all();
    $next_number = count($marquees) + 1;
    $marquees[] = [
        'title'       => 'New Marquee #' . $next_number,
        'text'        => '',
        'direction'   => 'left',
        'position'    => 'header',
        'bg_color'    => '#000000',
        'text_color'  => '#ffffff',
        'close_button'=> false,
        'hide_days'   => 3,
        'enabled'     => false,
    ];
    marquee_save_all($marquees);
    echo '<div class="updated"><p>' . __('New marquee added.', 'marquee-text') . '</p></div>';
}

// Save marquee
function marquee_save($index) {
    $marquees = marquee_get_all();
    if (!isset($marquees[$index])) return;

    $marquees[$index] = [
        'title'       => sanitize_text_field($_POST['title']),
        'text'        => sanitize_text_field($_POST['text']),
        'direction'   => sanitize_text_field($_POST['direction']),
        'position'    => sanitize_text_field($_POST['position']),
        'bg_color'    => marquee_sanitize_hex_color($_POST['bg_color']),
        'text_color'  => marquee_sanitize_hex_color($_POST['text_color']),
        'close_button'=> !empty($_POST['close_button']),
        'hide_days'   => max(-1, intval($_POST['hide_days'])),
        'enabled'     => !empty($_POST['enabled']),
    ];
    marquee_save_all($marquees);
    echo '<div class="updated"><p>' . __('Marquee saved.', 'marquee-text') . '</p></div>';
}

// Delete marquee
function marquee_delete($index) {
    $marquees = marquee_get_all();
    if (isset($marquees[$index])) {
        array_splice($marquees, $index, 1);
        marquee_save_all($marquees);
        echo '<div class="updated"><p>' . __('Marquee deleted.', 'marquee-text') . '</p></div>';
    }
}

// List view
function marquee_list_view($marquees) {
    ?>
    <div class="wrap">
    <h1><?php _e('Marquee Running Texts', 'marquee-text'); ?></h1>
    <form method="post">
    <?php wp_nonce_field('marquee_manager'); ?>
    <input type="hidden" name="action" value="add" />
    <button type="submit" class="page-title-action"><?php _e('Add New Marquee', 'marquee-text'); ?></button>
    </form>

    <table class="wp-list-table widefat fixed striped">
    <thead>
    <tr>
    <th><?php _e('Title', 'marquee-text'); ?></th>
    <th><?php _e('Position', 'marquee-text'); ?></th>
    <th><?php _e('Status', 'marquee-text'); ?></th>
    <th><?php _e('Actions', 'marquee-text'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($marquees)): ?>
    <tr><td colspan="4" style="text-align:center;">— <?php _e('No marquees configured', 'marquee-text'); ?> —</td></tr>
    <?php else: ?>
    <?php foreach ($marquees as $i => $m): ?>
    <tr>
    <td><?php echo esc_html($m['title']); ?></td>
    <td><?php echo esc_html(ucfirst($m['position'])); ?></td>
    <td><?php echo !empty($m['enabled']) ? __('Enabled', 'marquee-text') : __('Disabled', 'marquee-text'); ?></td>
    <td>
    <a href="<?php echo esc_url(add_query_arg('edit', $i)); ?>" class="button button-small"><?php _e('Edit', 'marquee-text'); ?></a>
    <form method="post" style="display:inline;" onsubmit="return confirm('<?php esc_attr_e('Delete this marquee?', 'marquee-text'); ?>');">
    <?php wp_nonce_field('marquee_manager'); ?>
    <input type="hidden" name="action" value="delete" />
    <input type="hidden" name="index" value="<?php echo esc_attr($i); ?>" />
    <button type="submit" class="button button-small button-link-delete"><?php _e('Delete', 'marquee-text'); ?></button>
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

// Edit form for existing marquee
function marquee_edit_form($marquee, $index) {
    ?>
    <div class="wrap">
    <h1><?php _e('Edit Marquee', 'marquee-text'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=marquee-running-text'); ?>">&laquo; <?php _e('Back to list', 'marquee-text'); ?></a>
    <form method="post" style="margin-top:20px;">
    <?php wp_nonce_field('marquee_manager'); ?>
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="index" value="<?php echo esc_attr($index); ?>" />

    <table class="form-table">
    <tr><th scope="row"><?php _e('Status', 'marquee-text'); ?></th>
    <td><label><input type="checkbox" name="enabled" value="1" <?php checked($marquee['enabled']); ?> /> <?php _e('Enabled', 'marquee-text'); ?></label></td></tr>

    <tr><th scope="row"><?php _e('Title', 'marquee-text'); ?></th>
    <td><input type="text" name="title" value="<?php echo esc_attr($marquee['title']); ?>" required /></td></tr>

    <tr><th scope="row"><?php _e('Marquee Text', 'marquee-text'); ?></th>
    <td><textarea name="text" rows="4" cols="50" required><?php echo esc_textarea($marquee['text']); ?></textarea></td></tr>

    <tr><th scope="row"><?php _e('Direction', 'marquee-text'); ?></th>
    <td><select name="direction">
    <option value="left" <?php selected($marquee['direction'], 'left'); ?>><?php _e('Right → Left', 'marquee-text'); ?></option>
    <option value="right" <?php selected($marquee['direction'], 'right'); ?>><?php _e('Left → Right', 'marquee-text'); ?></option>
    </select></td></tr>

    <tr><th scope="row"><?php _e('Position', 'marquee-text'); ?></th>
    <td><select name="position">
    <option value="header" <?php selected($marquee['position'], 'header'); ?>><?php _e('Header (Top)', 'marquee-text'); ?></option>
    <option value="footer" <?php selected($marquee['position'], 'footer'); ?>><?php _e('Footer (Sticky)', 'marquee-text'); ?></option>
    </select></td></tr>

    <tr><th scope="row"><?php _e('Background Color', 'marquee-text'); ?></th>
    <td><input type="color" name="bg_color" value="<?php echo esc_attr($marquee['bg_color']); ?>" /></td></tr>

    <tr><th scope="row"><?php _e('Text Color', 'marquee-text'); ?></th>
    <td><input type="color" name="text_color" value="<?php echo esc_attr($marquee['text_color']); ?>" /></td></tr>

    <tr><th scope="row"><?php _e('Enable Close Button', 'marquee-text'); ?></th>
    <td><label><input type="checkbox" name="close_button" value="1" <?php checked($marquee['close_button']); ?> /> <?php _e('Show close button', 'marquee-text'); ?></label></td></tr>

    <tr><th scope="row"><?php _e('Hide Duration After Close', 'marquee-text'); ?></th>
    <td><input type="number" name="hide_days" value="<?php echo esc_attr($marquee['hide_days']); ?>" min="-1" step="1" style="width:80px;" />
    <p class="description"><?php _e('Days to hide. Use -1 to never show again.', 'marquee-text'); ?></p></td></tr>
    </table>
    <p><button type="submit" class="button button-primary"><?php _e('Save Marquee', 'marquee-text'); ?></button>
    <a href="<?php echo admin_url('admin.php?page=marquee-running-text'); ?>" class="button"><?php _e('Cancel', 'marquee-text'); ?></a></p>
    </form>
    </div>
    <?php
}

// New marquee form
function marquee_edit_form_new() {
    $default = [
        'title'       => __('New Marquee', 'marquee-text'),
        'text'        => '',
        'direction'   => 'left',
        'position'    => 'header',
        'bg_color'    => '#000000',
        'text_color'  => '#ffffff',
        'close_button'=> false,
        'hide_days'   => 3,
        'enabled'     => true,
    ];
    ?>
    <div class="wrap">
    <h1><?php _e('Add New Marquee', 'marquee-text'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=marquee-running-text'); ?>">&laquo; <?php _e('Back to list', 'marquee-text'); ?></a>
    <form method="post" style="margin-top:20px;">
    <?php wp_nonce_field('marquee_manager'); ?>
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="index" value="new" />

    <table class="form-table">
    <tr><th scope="row"><?php _e('Status', 'marquee-text'); ?></th>
    <td><label><input type="checkbox" name="enabled" value="1" <?php checked($default['enabled']); ?> /> <?php _e('Enabled', 'marquee-text'); ?></label></td></tr>

    <tr><th scope="row"><?php _e('Title', 'marquee-text'); ?></th>
    <td><input type="text" name="title" value="<?php echo esc_attr($default['title']); ?>" required /></td></tr>

    <tr><th scope="row"><?php _e('Marquee Text', 'marquee-text'); ?></th>
    <td><textarea name="text" rows="4" cols="50" required><?php echo esc_textarea($default['text']); ?></textarea></td></tr>

    <tr><th scope="row"><?php _e('Direction', 'marquee-text'); ?></th>
    <td><select name="direction">
    <option value="left" <?php selected($default['direction'], 'left'); ?>><?php _e('Right → Left', 'marquee-text'); ?></option>
    <option value="right" <?php selected($default['direction'], 'right'); ?>><?php _e('Left → Right', 'marquee-text'); ?></option>
    </select></td></tr>

    <tr><th scope="row"><?php _e('Position', 'marquee-text'); ?></th>
    <td><select name="position">
    <option value="header" <?php selected($default['position'], 'header'); ?>><?php _e('Header (Top)', 'marquee-text'); ?></option>
    <option value="footer" <?php selected($default['position'], 'footer'); ?>><?php _e('Footer (Sticky)', 'marquee-text'); ?></option>
    </select></td></tr>

    <tr><th scope="row"><?php _e('Background Color', 'marquee-text'); ?></th>
    <td><input type="color" name="bg_color" value="<?php echo esc_attr($default['bg_color']); ?>" /></td></tr>

    <tr><th scope="row"><?php _e('Text Color', 'marquee-text'); ?></th>
    <td><input type="color" name="text_color" value="<?php echo esc_attr($default['text_color']); ?>" /></td></tr>

    <tr><th scope="row"><?php _e('Enable Close Button', 'marquee-text'); ?></th>
    <td><label><input type="checkbox" name="close_button" value="1" <?php checked($default['close_button']); ?> /> <?php _e('Show close button', 'marquee-text'); ?></label></td></tr>

    <tr><th scope="row"><?php _e('Hide Duration After Close', 'marquee-text'); ?></th>
    <td><input type="number" name="hide_days" value="<?php echo esc_attr($default['hide_days']); ?>" min="-1" step="1" style="width:80px;" />
    <p class="description"><?php _e('Days to hide. Use -1 to never show again.', 'marquee-text'); ?></p></td></tr>
    </table>
    <p><button type="submit" class="button button-primary"><?php _e('Save Marquee', 'marquee-text'); ?></button>
    <a href="<?php echo admin_url('admin.php?page=marquee-running-text'); ?>" class="button"><?php _e('Cancel', 'marquee-text'); ?></a></p>
    </form>
    </div>
    <?php
}
