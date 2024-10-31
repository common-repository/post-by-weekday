<?php

/*
 * Post By Weekday
 * 
 * Plugin Name: Post By Weekday
 * Plugin URI: 
 * Description: Choose publish post follow weekday
 * Version: 2.1.0
 * Author: Truong Thang @yensubldg
 * Author URI: https://www.facebook.com/windev.winstudio
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: post-by-weekday
 * Domain Path: /languages/
 *
* Post By Weekday is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
* Post By Weekday is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

* You should have received a copy of the GNU General Public License
along with Post By Weekday.. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

defined('ABSPATH') || exit;

if (!defined('PBWD_PATH')) {
    define('PBWD_PATH', plugin_dir_path(__FILE__));
}

if (!defined('PBWD_URL')) {
    define('PBWD_URL', plugins_url('', __FILE__));
}

define('PBWD_Version', '2.1.0');

function PBWD_add_shortcode_button()
{
    echo '<style>
        .fpost_media_link {
          padding-left: 0.4em;
        }
        .dashicons, .dashicons-before:before{
          line-height: 1.5;
        }
        #TB_window{
            overflow: auto;
        }
      </style>
      <a href="#TB_inline?&inlineId=tb_window" class=" button fpost_media_link thickbox" id="add_weekday" title="' . __('Post by Weekday', 'post-by-weekday') . '">
<span class="dashicons dashicons-calendar-alt"></span>
 <span>' . __('Choose Weekday', 'post-by-weekday') . '</span>
</a>
';
}

add_action('media_buttons', 'PBWD_add_shortcode_button');

function PBWD_add_form_popup()
{
    $week = array(
        0 => __('Sunday', 'post-by-weekday'),
        1 => __('Monday', 'post-by-weekday'),
        2 => __('Tuesday', 'post-by-weekday'),
        3 => __('Wednesday', 'post-by-weekday'),
        4 => __('Thursday', 'post-by-weekday'),
        5 => __('Friday', 'post-by-weekday'),
        6 => __('Saturday', 'post-by-weekday'),
    );

    $post_id = get_the_ID();

    $option = get_option('post_by_weekday_' . $post_id);
    $option = explode(',', $option);
?>
    <div id="tb_window" style="display: none;">
        <div class="win-weekday-ui-wrap">
            <div id="win-weekday-ui-container">
                <h2>Choose weekday for Publish</h2>
                <ul id="listday">
                    <?php
                    foreach ($week as $key => $value) {
                        $checked = '';
                        if (in_array($key, $option)) {
                            $checked = 'checked';
                        }
                    ?>
                        <li>
                            <input type="checkbox" name="weekday[]" value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($checked); ?>>
                            <label><?php echo esc_html($value); ?></label>
                        </li>
                    <?php
                    }
                    ?>
                </ul>
            </div>
            <input id="btn-pbwd" type="submit" class="button-primary" value="Apply">
            <a href="#" id="pbwd_btn_cancel" onclick="tb_remove();" class="button">Cancel</a>
        </div>
    </div>
    </div>
<?php
}
add_action('admin_footer', 'PBWD_add_form_popup');

function PBWD_register_scripts()
{
    wp_enqueue_script('pbwd_ws', PBWD_URL . '/script.js', array('jquery'), PBWD_Version);
}
add_action('admin_print_scripts', 'PBWD_register_scripts');
add_action('wp_ajax_setup_post_by_weekday', 'PBWD_setup');
function PBWD_setup()
{
    $post_id = sanitize_text_field($_POST['post_id']);
    $selected = sanitize_text_field($_POST['selected']);
    if ($post_id) {
        if ($selected !== '') {
            // check if opion exist
            get_option('post_by_weekday_' . $post_id) !== null ? update_option('post_by_weekday_' . $post_id, $selected) : add_option('post_by_weekday_' . $post_id, $selected);
        } else {
            // set all day to default
            delete_option('post_by_weekday_' . $post_id);
        }
        wp_send_json(array(
            'success' => true,
            'data' => get_option('post_by_weekday_' . $post_id) !== null,
            'select' => $selected !== '',
        ));
    } else {
        wp_send_json(array(
            'success' => false,
            'data' => $selected,
        ));
    }
    wp_die();
}

function PBWD_check_all_post_and_set()
{
    $args = array(
        'post_type' => array('post', 'product'),
        'post_status' => 'any',
        'posts_per_page' => -1,
    );
    $posts = get_posts($args);
    foreach ($posts as $post) {
        $post_id = $post->ID;
        $option = get_option('post_by_weekday_' . $post_id);
        if (empty($option)) {
            $option = array(0, 1, 2, 3, 4, 5, 6);
            $option = implode(',', $option);
            add_option('post_by_weekday_' . $post_id, $option);
        }
        $option = explode(',', $option);
        $option = array_map('intval', $option);
        $option = array_unique($option);
        $option = array_values($option);

        $date = date('w');
        foreach ($option as $key => $value) {
            if ($value == $date) {
                $post_status = 'publish';
                break;
            } else {
                $post_status = 'draft';
            }
        }
        wp_update_post(array(
            'ID' => $post_id,
            'post_status' => $post_status,
        ));
    }
}
// run every time to check
add_action('init', 'PBWD_check_all_post_and_set');
