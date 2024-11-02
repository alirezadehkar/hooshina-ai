<?php
/**
 * Plugin Name: Hooshina Ai
 * Plugin URI: https://hooshina.com
 * Author: Hooshina
 * Author URI: https://Hooshina.com
 * Version: 1.0
 * Description: Adding the ability to inquire the price of WooCommerce products through Telegram, WhatsApp, call, message and inquiry form.
 * Text Domain: hooshina-ai
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

if(class_exists('HooshinaAi_Plugin'))
    return false;

define('HAI_PLUGIN_FILE_PATH', __FILE__);
define('HAI_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('HAI_BASENAME', basename(dirname(__FILE__)));
define('HAI_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('HAI_APP_PATH', trailingslashit(HAI_PATH . 'app'));
define('HAI_INC_PATH', trailingslashit(HAI_PATH . 'includes'));
define('HAI_LIB_PATH', trailingslashit(HAI_INC_PATH . 'lib'));
define('HAI_VIEW_PATH', trailingslashit(HAI_PATH . 'views'));
define('HAI_URL', trailingslashit(plugin_dir_url(__FILE__)));
define('HAI_BUILD_URL', trailingslashit(HAI_URL . 'build'));
define('HAI_CSS_URL', trailingslashit(HAI_URL . 'assets/css'));
define('HAI_JS_URL', trailingslashit(HAI_URL . 'assets/js'));
define('HAI_IMG_URL', trailingslashit(HAI_URL . 'assets/images'));


/*
 *
 *  Loaded plugin core
 *
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'core.php';

HooshinaAi_Plugin::Instance();
