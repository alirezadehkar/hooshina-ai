<?php
namespace HooshinaAi\App;

class Hooks
{
    public static function init()
    {
        if(is_admin()){
            add_action('admin_menu', ['\HooshinaAi\App\AdminMenu', 'init']);
            add_action('admin_init', ['\HooshinaAi\App\AdminMenu', 'handle_activation_redirect']);
            add_action('wp_ajax_hai_connect_to_api', ['\HooshinaAi\App\Ajax', 'handle_connect_to_api']);
            add_action('wp_ajax_hai_disconnect_to_api', ['\HooshinaAi\App\Ajax', 'handle_disconnect_to_api']);
            add_action('wp_ajax_hai_check_is_connected', ['\HooshinaAi\App\Ajax', 'handle_check_is_connected']);
            add_action('wp_ajax_hai_check_account_balance', ['\HooshinaAi\App\Ajax', 'handle_check_account_balance']);
            add_action('wp_ajax_hai_account_balance', ['\HooshinaAi\App\Ajax', 'handle_show_account_balance']);

            add_action('wp_ajax_hai_generate_comment_answer', ['\HooshinaAi\App\Ajax', 'handle_generate_comment_text']);
            add_action('wp_ajax_hai_submit_comment_answer', ['\HooshinaAi\App\Ajax', 'handle_submit_comment_answer']);
            add_action('wp_ajax_hai_submit_product_reviews_summary', ['\HooshinaAi\App\Ajax', 'handle_submit_product_reviews_summary']);

            add_action('wp_ajax_hai_generate_content', ['\HooshinaAi\App\Ajax', 'handle_generate_content']);
            add_action('wp_ajax_hai_check_image_status', ['\HooshinaAi\App\Ajax', 'handle_check_image_status']);

            add_action('woocommerce_product_options_advanced', ['\HooshinaAi\App\Callback', 'handle_wc_edit_advanced_tab']);
            add_action('woocommerce_admin_process_product_object', ['\HooshinaAi\App\Callback', 'handle_save_edit_product_data']);

            add_action('wp_ajax_hai_get_terms_by_taxonomy', ['\HooshinaAi\App\Ajax', 'handle_get_terms_by_taxonomy']);

            add_action('wp_ajax_hai_search_users', ['\HooshinaAi\App\Ajax', 'handle_search_users']);

            add_action('admin_init', ['\HooshinaAi\App\Callback', 'handle_connection_notices']);

            add_action('wp_ajax_hai_dismiss_remind_notice', ['\HooshinaAi\App\Ajax', 'handle_dismiss_remind_notice']);
        } else {
            add_action('comment_form_before', ['\HooshinaAi\App\Callback', 'handle_show_ai_reviews_excerpt']);
        }
    }
}