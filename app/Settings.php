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

        $tabs = [
            'content-generation' => [
                'default_content_tone' => [],
                'default_image_style' => [],
                'default_product_image_style' => [],
                'default_image_size' => [],
            ],
            'auto-content-generation' => [
                'default_post_type' => [],
                'default_post_status' => [],
                'default_author' => [],
                'default_taxonomy' => [],
                'default_category' => [],
            ] 
        ];

        if($tabs){
            foreach($tabs as $tab => $options){
                if(self::get_current_tab() == $tab || self::get_current_subtab() == $tab){
                    foreach ($options as $key => $value){
                        $type = isset($value['type']) ? $value['type'] : null;
                        if($type == 'checkbox'){
                            self::update_option($key, isset($_POST[$key]));
                        } else {
                            if(isset($_POST[$key])){
                                $option = sanitize_text_field(wp_unslash($_POST[$key]));
                                self::update_option($key, $option);
                            }
                        }
                    } 
                }
            }
        }
        
        Notice::displaySuccess(__('Settings saved successfully.', 'hooshina-ai'));

        $subtab = self::get_current_subtab();
        wp_redirect(add_query_arg('subtab', $subtab));
        exit;
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

    public static function get_default_post_type()
    {
        return self::get_option('default_post_type', 'post');
    }

    public static function get_default_post_status()
    {
        return self::get_option('default_post_status', 'draft');
    }

    public static function get_default_author()
    {
        $author = self::get_option('default_author');
        if (!$author) {
            $admins = get_users(['role' => 'administrator', 'number' => 1]);
            if (!empty($admins)) {
                $author = $admins[0]->ID;
            } elseif(is_user_logged_in()) {
                $author = get_current_user_id();
            }
        }
        return $author;
    }

    public static function get_default_taxonomy()
    {
        return self::get_option('default_taxonomy', 'category');
    }

    public static function get_default_category()
    {
        return self::get_option('default_category', get_option('default_category'));
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

    public static function get_current_subtab()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $currentSubtab = isset($_GET['subtab']) ? sanitize_key(wp_unslash($_GET['subtab'])) : null;
        
        if ($currentSubtab && in_array($currentSubtab, ['content-generation', 'auto-content-generation'], true)) {
            return $currentSubtab;
        }
        
        return 'content-generation';
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
        $subtab = self::get_current_subtab();

        $contentTones = GeneratorHelper::get_content_tones();
        $imageStyles = GeneratorHelper::get_image_styles(Generator::TextToImage);
        $productImageStyles = GeneratorHelper::get_image_styles(Generator::ProductImage);
        $imageSizes = GeneratorHelper::get_image_sizes();
        
        $defContentTone = self::get_default_content_tone();
        $defImageStyle = self::get_default_image_style();
        $defProductImageStyle = self::get_default_product_image_style();
        $defImageSize = self::get_default_image_size();
        $defPostType = self::get_default_post_type();
        $defPostStatus = self::get_default_post_status();
        $defAuthor = self::get_default_author();
        $defTaxonomy = self::get_default_taxonomy();
        $defCategory = self::get_default_category();

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
            'defPostType',
            'defPostStatus',
            'defAuthor',
            'defTaxonomy',
            'defCategory',
            'accountBalance',
            'subtab'
        ));
    }
}