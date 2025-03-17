<?php
namespace HooshinaAi\App;

defined('ABSPATH') or die('No script kiddies please!');

class Callback
{
    public static function handle_show_ai_reviews_excerpt()
    {
        if(!is_singular('product')){
            return false;
        }

        global $product;

        $postId = get_the_ID();
        
        if(!comments_open($product->get_id())){
            return false;
        }

        $show = PostMeta::show_product_reviews_summary($postId);
        $reviewsSummary = PostMeta::get_product_reviews_summary($postId);

        if(!$show || empty($reviewsSummary)){
            return false;
        }

        hooshina_ai_view('front.product.reviews-summary', compact('reviewsSummary'));
    }

    public static function handle_wc_edit_advanced_tab()
    {
        woocommerce_wp_checkbox([
            'id' => 'hooshina_ai_show_product_reviews_summary',
            'label' => __('Product reviews summary', 'hooshina-ai'),
        ]);

        wp_nonce_field('hooshina_ai_nonce', 'hooshina_ai_nonce');
    }

    public static function handle_save_edit_product_data($product)
    {
        if (!isset($_POST['hooshina_ai_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['hooshina_ai_nonce'])), 'hooshina_ai_nonce')) {
            return;
        }
    
        $checkboxValue = isset($_POST['hooshina_ai_show_product_reviews_summary']) ? 'yes' : 'no';
        $product->update_meta_data('hooshina_ai_show_product_reviews_summary', $checkboxValue);
    }
}