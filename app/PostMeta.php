<?php
namespace HooshinaAi\App;

class PostMeta
{
    public static function update_meta($post_id, $meta_key, $meta_value)
    {
        return update_post_meta($post_id, $meta_key, $meta_value);
    }

    public static function get_meta($post_id, $meta_key, $single = false)
    {
        return get_post_meta($post_id, $meta_key, $single);
    }

    public static function show_product_reviews_summary($post_id)
    {
        return self::get_meta($post_id, 'hooshina_ai_show_product_reviews_summary', true) == 'yes';
    }

    public static function get_product_reviews_summary($post_id)
    {
        return self::get_meta($post_id, 'hooshina_ai_product_reviews_summary', true);
    }

    public static function update_product_reviews_summary($post_id, $summary)
    {
        return self::update_meta($post_id, 'hooshina_ai_product_reviews_summary', $summary);
    }

    public static function get_filter_meta_key()
    {
        return 'by_hooshina';
    }
}