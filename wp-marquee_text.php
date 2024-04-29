<?php
/**
* Plugin Name: Marquee Running Text
* Plugin URI: https://www.github.com/meddil/wp-marquee_text
* Description: Add marquee running text to page
* Version: 0.0.1
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
add_action('admin_menu', 'marquee_running_text_menu');
function marquee_running_text_menu() {
    add_menu_page('Marquee Running Text', 'Marquee Running Text', 'manage_options', 'marquee-running-text', 'marquee_running_text_page');
}
//interface
function marquee_running_text_page() {
    $marquee_text = isset($_POST['marquee_text']) ? sanitize_text_field($_POST['marquee_text']) : '';
    $shortcode = '[running_text]';

    if ($marquee_text) {
        update_option('marquee_text', $marquee_text);
    }

    ?>
    <div class="wrap">
        <h2>Marquee Running Text</h2>
        <form method="post" action="">
            <label for="marquee-text">Marquee Text:</label>
            <textarea id="marquee-text" name="marquee_text" rows="4" cols="50"><?php echo esc_textarea($marquee_text); ?></textarea><br>
            <input type="submit" name="submit" value="Submit">
        </form>
        <p><?php echo esc_html("Shortcode: ") . $shortcode; ?> <button onclick="copyShortcode()">Copy</button></p>
        <p><?php echo esc_html("Submit the text and copy/paste the shortcut code to your page builder"); ?></p>
    </div>

    <script>
        function copyShortcode() {
            var shortcode = document.createElement("textarea");
            shortcode.value = "<?php echo esc_js($shortcode); ?>";
            document.body.appendChild(shortcode);
            shortcode.select();
            document.execCommand("copy");
            document.body.removeChild(shortcode);
            alert("Shortcode copied!");
        }
    </script>
    <?php
}

//shortcode generate
function marquee_text_shortcode($atts, $content = null) {
    $marquee_text = get_option('marquee_text');
    if ($marquee_text) {
        return '<marquee behavior="scroll" direction="left">' . esc_html($marquee_text) . '</marquee>';
    } else {
        return '';
    }
}
add_shortcode('running_text', 'marquee_text_shortcode');
