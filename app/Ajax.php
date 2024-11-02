<?php
namespace Hooshina\App;

use Hooshina\App\Generator\Generator;

class Ajax
{
    public static function handle_check_is_connected()
    {
        check_ajax_referer('hooshina_nonce', 'nonce');

        if (!AiService::use()->isConnected())
            wp_send_json_error();

        wp_send_json_success();
    }

    public static function handle_connect_to_api()
    {
        check_ajax_referer('hooshina_nonce', 'nonce');

        $connectUrl = AiService::use()->getConnectUrl();

        if (is_wp_error($connectUrl) || empty($connectUrl))
            wp_send_json_error();

        wp_send_json_success(['redirect' => $connectUrl]);
    }

    public static function handle_disconnect_to_api()
    {
        check_ajax_referer('hooshina_nonce', 'nonce');

        $auth = AiService::use()->getConnectionAuth();

        if (empty($auth))
            wp_send_json_error();

        $revoke = AiService::use()->revokeConnection($auth);
        if (is_wp_error($revoke) || empty($revoke))
            wp_send_json_error();

        wp_send_json_success();
    }

    public static function handle_generate_content()
    {
        check_ajax_referer('hooshina_nonce', 'nonce');

        $subject = isset($_POST['subject']) ? Helper::unslash_sanitize($_POST['subject']) : null;
        $type = isset($_POST['type']) ? Helper::unslash_sanitize($_POST['type']) : null;

        $size = isset($_POST['size']) ? Helper::unslash_sanitize($_POST['size']) : null;
        $style = isset($_POST['style']) ? Helper::unslash_sanitize($_POST['style']) : null;

        $lang = isset($_POST['lang']) ? Helper::unslash_sanitize($_POST['lang']) : null;
        $tone = isset($_POST['tone']) ? Helper::unslash_sanitize($_POST['tone']) : null;

        $generator = new Generator();

        if($type == 'image'){
            $generate = $generator->image()->set_params([
                'subject' => $subject,
                'size' => $size,
                'tone' => $style,
            ])->generate();
        } elseif($type == 'text'){
            $generate = $generator->content()->set_params([
                'subject' => $subject,
                'lang' => $lang,
                'tone' => $tone,
            ])->generate();
        }

        if (empty($generate))
            wp_send_json_error();

        wp_send_json_success(['content' => $generate]);
    }
}