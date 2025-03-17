<?php
namespace HooshinaAi\App;

use HooshinaAi\App\Generator\Generator;
use HooshinaAi\App\Generator\GeneratorHelper;
use HooshinaAi\App\Notice\Notice;
use HooshinaAi\App\Provider\Account;

class Settings
{
    private static $option_name_prefix = 'hooshina_ai_';

    public static function handle_save_settings()
    {
        if(!isset($_SERVER["REQUEST_METHOD"]) || $_SERVER["REQUEST_METHOD"] != 'POST'){
            return false;
        }

        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'hooshina_ai_nonce')) {
            exit('Sorry, your nonce did not verify!');
        }

        $options = [
            'default_content_tone' => [],
            'default_image_style' => [],
            'default_product_image_style' => [],
            'default_image_size' => [],
        ];

        if($options){
            foreach ($options as $key => $value){
                $type = isset($value['type']) ? $value['type'] : null;
                if($type == 'checkbox'){
                    self::update_option($key, isset($_POST[$key]));
                } else {
                    if(isset($_POST[$key])){
                        $option = sanitize_text_field(wp_unslash($_POST[$key]));
                        self::update_option($key, $option);
                    } else {
                        self::delete_option($key);
                    }
                }
            }
        }
        
        Notice::displaySuccess(__('Settings saved successfully.', 'hooshina-ai'));
    }

    public static function get_option($key, $default = null){
        return Options::get_option(self::$option_name_prefix . $key, $default);
    }

    public static function update_option($key, $value){
        return Options::update_option(self::$option_name_prefix . $key, $value);
    }

    public static function delete_option($key){
        return Options::delete_option(self::$option_name_prefix . $key);
    }

    public static function get_default_content_tone()
    {
        return self::get_option('default_content_tone', 'professional');
    }

    public static function get_default_image_style()
    {
        return self::get_option('default_image_style', 'classical-realism');
    }

    public static function get_default_product_image_style()
    {
        return self::get_option('default_product_image_style', 'studio-shot');
    }

    public static function get_default_image_size()
    {
        return self::get_option('default_image_size', '1792x1024');
    }

    public static function get_current_tab()
    {
        $items = self::get_menu_items();
        $keys = array_keys($items);
    
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $currentTab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : null;
    
        if ($currentTab && in_array($currentTab, $keys, true)) {
            return $currentTab;
        }
    
        return $keys[array_key_first($keys)];
    }

    public static function get_menu_items()
    {
        $items = [
            'general' => [
                'title' => __('Settings', 'hooshina-ai'),
            ],
            'account' => [
                'title' => __('Connect to Hooshina', 'hooshina-ai'),
            ],
        ];

        return apply_filters('hai_admin_setting_menus', $items);
    }

    public static function render_options_content()
    {
        $tab = self::get_current_tab();

        $contentTones = GeneratorHelper::get_content_tones();
        $imageStyles = GeneratorHelper::get_image_styles(Generator::TextToImage);
        $productImageStyles = GeneratorHelper::get_image_styles(Generator::ProductImage);
        $imageSizes = GeneratorHelper::get_image_sizes();
        
        $defContentTone = self::get_default_content_tone();
        $defImageStyle = self::get_default_image_style();
        $defProductImageStyle = self::get_default_product_image_style();
        $defImageSize = self::get_default_image_size();

        $accountBalance = (new Account)->get_balance();

        hooshina_ai_view("admin.pages.{$tab}-tab", compact(
            'contentTones', 
            'imageStyles', 
            'productImageStyles', 
            'imageSizes',
            'defContentTone',
            'defImageStyle',
            'defProductImageStyle',
            'defImageSize',
            'accountBalance',
        ));
    }
}