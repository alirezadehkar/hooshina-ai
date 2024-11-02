<?php
namespace Hooshina\App;

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
            2
        );

        add_action('load-' . AdminMenu::$parent_menu, ['\Hooshina\App\Connection', 'handle_return_from_ai']);
    }

    public static function handle_menu_content(){
        $page = isset($_GET['page']) ? str_replace('hai-', '', Helper::unslash_sanitize($_GET['page'])) : null;
        if(empty($page) || $page === self::MENU_PARENT_SLUG){
            $page = 'options';
        }
        do_action("hai_admin_page_{$page}");
        hai_view('admin.pages.' . $page, ['page' => $page]);
    }

    public static function get_options_url()
    {
        return admin_url('admin.php?page=' . self::MENU_PARENT_SLUG);
    }
}