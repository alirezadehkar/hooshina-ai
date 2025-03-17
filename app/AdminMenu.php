<?php
namespace HooshinaAi\App;

defined('ABSPATH') or die('No script kiddies please!');

class AdminMenu
{
    private const MENU_PARENT_SLUG = 'hooshina';
    public static $parent_menu;

    public static function init()
    {
        self::register_menu_items();
    }

    public static function register_menu_items(){
         self::$parent_menu = add_menu_page(
            __('Hooshina', 'hooshina-ai'),
            __('Hooshina', 'hooshina-ai'),
            'manage_options',
            self::MENU_PARENT_SLUG,
            [__CLASS__, 'handle_menu_content'],
            'dashicons-smiley',
            99
        );

        add_action('load-' . AdminMenu::$parent_menu, ['\HooshinaAi\App\Connection', 'handle_return_from_ai']);
        add_action('load-' . AdminMenu::$parent_menu, ['\HooshinaAi\App\Settings', 'handle_save_settings']);
    }

    public static function handle_menu_content(){
        hooshina_ai_view('admin.pages.options');
    }

    public static function get_options_url($tab = null)
    {
        return add_query_arg('tab', $tab, admin_url('admin.php?page=' . self::MENU_PARENT_SLUG));
    }

    public static function handle_activation_redirect()
    {
        $key = 'hooshina_ai_plugin_activated';

        if (Options::get_option($key, false)) {
            Options::delete_option($key);

            wp_redirect(AdminMenu::get_options_url('account'));
            exit;
        }
    }
}