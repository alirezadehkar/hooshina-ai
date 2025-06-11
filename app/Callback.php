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

    public static function handle_connection_notices()
    {
        if(!Connection::isConnected() && !Options::hideRemindNotice()){
            add_action('admin_notices', function (){ ?>
                <div class="wrap">
                    <div class="hai-no-connection-notice">
                        <span class="border-line"></span>
                        <span><img src="<?php echo esc_attr(Assets::get_img('logo-icon', 'svg')) ?>" alt="hooshina-ai-logo"></span>
                        <span class="notice-text">
                            <?php esc_html_e('You are not connected to Hooshina. You must be connected to use the features and publish automatic content.', 'hooshina-ai') ?>
                        </span>
                        <a href="<?php echo esc_attr(\HooshinaAi\App\AdminMenu::get_options_url('account')) ?>" class="connect-btn">
                            <?php esc_html_e('Activation Hooshina', 'hooshina-ai') ?>
                        </a>
                        <a class="close-remind-notice" href="#">
                            <?php esc_html_e('Remind later', 'hooshina-ai')?>
                        </a>
                    </div>
                </div>
            <?php
            }, 1);
        }
    }

    public static function handle_posts_filter_query($query)
    {
        global $pagenow;
        $key = PostMeta::get_filter_meta_key();

        if (is_admin() && $pagenow === 'edit.php' && isset($_GET[$key]) && $_GET[$key] === '1') {
            $query->set('meta_key', $key);
            $query->set('meta_value', '1');
        }
    }

    public static function handle_post_status_statuses_filter($statuses)
    {
        global $wpdb;
        $key = PostMeta::get_filter_meta_key();
        
        $count = $wpdb->get_var(sprintf("
            SELECT COUNT(*) 
            FROM {$wpdb->posts} p 
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
            WHERE pm.meta_key = '%s' 
            AND pm.meta_value = '1'
        ", $key));

        $class = isset($_GET[$key]) && $_GET[$key] === '1' ? 'current' : '';
        
        $url = remove_query_arg(array_keys($_GET));

        $post_type = !empty($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : get_post_type();

        $url = add_query_arg([
            'post_type' => $post_type,
            $key => '1'
        ], $url);
        
        $statuses['hooshina'] = sprintf(
            '<a href="%s" class="hooshina-posts-filter-btn %s">%s <span class="count">(%d)</span></a>',
            esc_url($url),
            $class,
            esc_html__('Hooshina', 'hooshina-ai'),
            $count
        );

        return $statuses;
    }
}