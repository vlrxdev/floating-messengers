<?php
/**
 * Plugin Name: Floating Messengers
 * Description: Плавающие кнопки мессенджеров в правом нижнем углу
 * Version: 1.1.0
 * Author: xr
 * Text Domain: floating-messengers
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FM_OPTION_KEY', 'floating_messengers_settings');
define('FM_VERSION', '1.1.0');

require_once FM_PLUGIN_PATH . 'includes/helpers.php';
require_once FM_PLUGIN_PATH . 'includes/admin.php';
require_once FM_PLUGIN_PATH . 'includes/frontend.php';

register_activation_hook(__FILE__, function () {
    if (get_option(FM_OPTION_KEY) === false) {
        add_option(FM_OPTION_KEY, [
            'buttons' => fm_default_buttons(),
        ]);
    }
});
