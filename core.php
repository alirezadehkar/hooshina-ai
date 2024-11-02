<?php
/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

use Hooshina\App\Assets;

final class HooshinaAi_Plugin {
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
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /**
     * Include init files
     *
     * @return void
     */
    public function include_files()
    {
        require_once HAI_INC_PATH . 'helpers.php';

        $this->register_autoload();

        \Hooshina\App\Hooks::init();
    }

    /**
     * Enqueue admin scripts
     *
     * @return void
     */
    public function enqueue_admin_assets()
    {
        Assets::enqueue_style('hai-style', Assets::get_css('style'));

        Assets::enqueue_script('hai-script', HAI_BUILD_URL . 'index.js', ['wp-element', 'wp-components', 'wp-i18n', 'wp-hooks'], get_hai_asset_data('version'), true);
        Assets::localize_script('hai-script', 'hai_data', Assets::get_localize_data());
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
        register_activation_hook(HAI_PLUGIN_BASENAME, function (){
            $this->register_autoload();
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
        if(strpos($class, 'Hooshina') !== false){
            $class = str_replace(['Hooshina\\', 'Hooshina', '\\'], ['', '', '/'], $class);
            $class_arr = explode('/', $class);
            $file_name = $class_arr[array_key_last($class_arr)] . '.php';
            unset($class_arr[array_key_last($class_arr)]);
            $file_path = HAI_PATH . strtolower(implode('/', $class_arr)) . '/' . $file_name;

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
        load_plugin_textdomain('hooshina-ai', false, HAI_BASENAME . '/languages/');
    }

    private function load_notices(){
        $min_php_version = '7.4';
        $errors = [];

        if(!version_compare(phpversion(),$min_php_version,'>=')) {
            // translators: %s is the minimum php version
            $errors[] = sprintf(__('We detect your server php version is to old, this plugin need php version %s. please call to your host service to update php', 'hooshina-ai'), $min_php_version);
        }

        if(!empty($errors)){
            add_action('admin_notices', function () use ($errors){
                $title = __('Hooshina Plugin:', 'hooshina-ai');
                printf('<div class="notice notice-error notice-alt"> <p><strong>%s</strong> %s</p> </div>', esc_html($title), esc_html(implode('<hr>', $errors)));
            }, 1);
        }
    }
}