<?php
/**
 * Plugin Name: Hooshina Ai
 * Plugin URI: https://hooshina.com
 * Author: Hooshina
 * Author URI: https://Hooshina.com
 * Version: 1.0
 * Description: Intelligent image and text production with the help of Hooshina Ai.
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

if(class_exists('Hooshina_Ai_Plugin'))
    return false;

define('HOOSHINA_AI_PLUGIN_FILE_PATH', __FILE__);
define('HOOSHINA_AI_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('HOOSHINA_AI_BASENAME', basename(dirname(__FILE__)));
define('HOOSHINA_AI_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('HOOSHINA_AI_APP_PATH', trailingslashit(HOOSHINA_AI_PATH . 'app'));
define('HOOSHINA_AI_INC_PATH', trailingslashit(HOOSHINA_AI_PATH . 'includes'));
define('HOOSHINA_AI_LIB_PATH', trailingslashit(HOOSHINA_AI_INC_PATH . 'lib'));
define('HOOSHINA_AI_VIEW_PATH', trailingslashit(HOOSHINA_AI_PATH . 'views'));
define('HOOSHINA_AI_URL', trailingslashit(plugin_dir_url(__FILE__)));
define('HOOSHINA_AI_BUILD_URL', trailingslashit(HOOSHINA_AI_URL . 'build'));
define('HOOSHINA_AI_CSS_URL', trailingslashit(HOOSHINA_AI_URL . 'assets/css'));
define('HOOSHINA_AI_JS_URL', trailingslashit(HOOSHINA_AI_URL . 'assets/js'));
define('HOOSHINA_AI_IMG_URL', trailingslashit(HOOSHINA_AI_URL . 'assets/images'));


/*
 *
 *  Loaded plugin core
 *
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'core.php';

Hooshina_Ai_Plugin::Instance();
