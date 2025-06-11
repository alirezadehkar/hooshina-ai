<?php
namespace HooshinaAi\App;

class Options
{
    public static function get_option($option, $default = false, $deprecated = true)
    {
        return is_multisite() ? get_blog_option(get_current_blog_id(), $option, $default) : get_option($option, $default);
    }

    public static function update_option($option, $value)
    {
        return is_multisite() ? update_blog_option(get_current_blog_id(), $option, $value) : update_option($option, $value);
    }

    public static function add_option($option, $value)
    {
        return is_multisite() ? add_blog_option(get_current_blog_id(), $option, $value) : add_option($option, $value);
    }

    public static function delete_option($option)
    {
        return is_multisite() ? delete_blog_option(get_current_blog_id(), $option) : delete_option($option);
    }
    
    public static function get_current_wallet()
    {
        return Helper::get_locale();
    }

    public static function hideRemindNotice()
    {
        $cookieKey = 'hai_dismiss_connection_notice_status';
        return isset($_COOKIE[$cookieKey]) && $_COOKIE[$cookieKey] == 1;
    }

    public static function is_rank_math_active()
    {
        return class_exists('\RankMath') || 
               (function_exists('is_plugin_active') && is_plugin_active('seo-by-rank-math/rank-math.php'));
    }

    public static function is_yoast_seo_active()
    {
        return function_exists('is_plugin_active') && is_plugin_active('wordpress-seo/wp-seo.php');
    }
}