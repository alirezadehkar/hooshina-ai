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

    public function handle_connection_notices()
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
}