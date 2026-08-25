<?php
/**
 * Plugin Name: Floating Messengers
 * Description: Плавающие кнопки мессенджеров
 * Version: 1.3.0
 * Author: xr
 * Text Domain: floating-messengers
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FM_OPTION_KEY', 'floating_messengers_settings');
define('FM_VERSION', '1.3.0');

require_once FM_PLUGIN_PATH . 'includes/helpers.php';
require_once FM_PLUGIN_PATH . 'includes/admin.php';
require_once FM_PLUGIN_PATH . 'includes/frontend.php';

register_activation_hook(__FILE__, function () {
    if (get_option(FM_OPTION_KEY) === false) {
        add_option(FM_OPTION_KEY, [
            'icon_size' => 56,
            'position'  => 'bottom-right',
            'offset_x'  => 20,
            'offset_y'  => 20,
            'buttons'   => fm_default_buttons(),
        ]);
    }
});
