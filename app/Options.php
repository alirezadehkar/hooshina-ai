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
}