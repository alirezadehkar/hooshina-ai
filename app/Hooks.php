<?php
namespace Hooshina\App;

class Hooks
{
    public static function init()
    {
        if(is_admin()){
            add_action('admin_menu', ['\Hooshina\App\AdminMenu', 'init']);
            add_action('wp_ajax_hai_connect_to_api', ['\Hooshina\App\Ajax', 'handle_connect_to_api']);
            add_action('wp_ajax_hai_disconnect_to_api', ['\Hooshina\App\Ajax', 'handle_disconnect_to_api']);
            add_action('wp_ajax_hai_check_is_connected', ['\Hooshina\App\Ajax', 'handle_check_is_connected']);

            add_action('wp_ajax_hai_generate_content', ['\Hooshina\App\Ajax', 'handle_generate_content']);
        }
    }
}