<?php
/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

use HooshinaAi\App\Assets;
use HooshinaAi\App\Options;
use HooshinaAi\App\mihanwpUpdater;
use HooshinaAi\App\Api\Routes\CreatePostApi;
use HooshinaAi\App\Api\Routes\TermsListApi;
use HooshinaAi\App\Connection;
use HooshinaAi\App\Fields;
use HooshinaAi\App\Helper;
use HooshinaAi\App\Hooks;
use HooshinaAi\App\Notice\Notice;
use HooshinaAi\App\Settings;

final class Hooshina_Ai_Plugin {
    private static $instance = null;

    public static function Instance(){
        if(!self::$instance){
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->load_translations();
        $this->load_notices();
        $this->register_activation();
        add_action('plugins_loaded', [$this, 'include_files']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        add_action('plugins_loaded', [$this, 'handleUpdater']);
    }

    /**
     * Include init files
     *
     * @return void
     */
    public function include_files()
    {
        require_once HOOSHINA_AI_PATH . 'vendor/autoload.php';

        require_once HOOSHINA_AI_INC_PATH . 'helpers.php';

        $this->register_autoload();

        Hooks::init();
        
        if(Connection::isConnected() && !Settings::api_is_deactivated()){
            new CreatePostApi;
            new TermsListApi;
        }
    }

    public function enqueue_front_assets()
    {
        Assets::enqueue_style('hooshina-ai-style', Assets::get_css('style'));

        Assets::enqueue_script('hooshina-ai-script', Assets::get_js('script'), [], null, true);
        Assets::localize_script('hooshina-ai-script', 'hai_data', Assets::get_localize_data());
    }
    
    public function enqueue_admin_assets()
    {
        Assets::enqueue_style('select2', Assets::get_css('select2.min'), [], '4.1.0');
        Assets::enqueue_style('hooshina-ai-admin', Assets::get_css('admin'));

        Assets::enqueue_script('hooshina-ai-admin', Assets::get_js('admin'), ['jquery', 'select2'], null, true);
        Assets::enqueue_script('select2', Assets::get_js('select2.min'), ['jquery'], '4.1.0', true);
        Assets::localize_script('hooshina-ai-admin', 'hai_data', Assets::get_localize_data());

        Assets::enqueue_script('hooshina-ai-script', HOOSHINA_AI_BUILD_URL . 'index.js', ['wp-element', 'wp-components', 'wp-i18n', 'wp-hooks', 'wp-blocks', 'wp-rich-text', 'wp-editor'], hooshina_ai_get_asset_data('version'), true);
        Assets::localize_script('hooshina-ai-script', 'hai_data', Assets::get_localize_data());
    }

    public function register_autoload()
    {
        if (function_exists('__autoload')) {
            spl_autoload_register('__autoload');
        }

        spl_autoload_register([$this, 'autoloader']);
    }

    public function register_activation()
    {
        register_activation_hook(HOOSHINA_AI_PLUGIN_BASENAME, function (){
            $this->register_autoload();

            Options::add_option('hooshina_ai_plugin_activated', true);
        });
    }

    /**
     * Class autoloader
     *
     * @param string $class
     * @return void
     */
    public function autoloader($class)
    {
        if(strpos($class, 'HooshinaAi') !== false){
            $class = str_replace(['HooshinaAi\\', 'HooshinaAi', '\\'], ['', '', '/'], $class);
            $class_arr = explode('/', $class);
            $file_name = $class_arr[array_key_last($class_arr)] . '.php';
            unset($class_arr[array_key_last($class_arr)]);
            $file_path = HOOSHINA_AI_PATH . strtolower(implode('/', $class_arr)) . '/' . $file_name;

            if(file_exists($file_path) && is_readable($file_path)){
                include_once($file_path);
            }
        }
    }

    /**
     *
     *
     * Load text domain
     *
     */
    private function load_translations(){
        $locale = apply_filters('plugin_locale', get_locale(), 'hooshina-ai');
        $wp_core_lang = trailingslashit(WP_LANG_DIR) . 'hooshina-ai' . '/' . 'hooshina-ai' . '-' . $locale . '.mo';
        if (file_exists($wp_core_lang)) {
            load_textdomain('hooshina-ai', $wp_core_lang);
        }
        load_plugin_textdomain('hooshina-ai', false, HOOSHINA_AI_BASENAME . '/languages/');
    }

    private function load_notices(){
        $min_php_version = '7.4';
        $errors = [];

        $this->register_autoload();

        if(!version_compare(phpversion(),$min_php_version,'>=')) {
            // translators: %s is the minimum php version
            $errors[] = sprintf(__('We detect your server php version is to old, this plugin need php version %s. please call to your host service to update php', 'hooshina-ai'), $min_php_version);
        }

        if(!empty($errors)){
            Notice::error(
                __('Hooshina Plugin:', 'hooshina-ai'),
                $errors
            )->adminNotice();
        }

        
        if(Helper::is_blocked_urls()){
            Notice::error(
                __('External URL access is blocked by WordPress configuration', 'hooshina-ai'),
                __('To fix this, please add app.hooshina.com to the list of allowed hosts in your wp-config.php file.', 'hooshina-ai'),
            )->adminNotice();
        }
    }

    public function handleUpdater()
    {
        $plugin_version = hooshina_ai_get_version();
        $updaterArgs = [
            'base_api_server' => 'https://mihanwp.com',
            'license_key' => 'free',
            'item_id' => 1152433,
            'current_version' => $plugin_version,
            'plugin_slug' => plugin_basename(HOOSHINA_AI_PLUGIN_FILE_PATH),
            'license_status' => true,
        ];

        mihanwpUpdater::init($updaterArgs);
    }
}