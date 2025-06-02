<?php
namespace HooshinaAi\App;

use HooshinaAi\App\Generator\Generator;
use HooshinaAi\App\Generator\GeneratorHelper;

defined('ABSPATH') or die('No script kiddies please!');

class Assets {
    public static function get_handle_name($handle){
        return str_replace(['.'], '-', $handle);
    }

    public static function register_style($handle, $src, $deps = null, $ver = null, $media = null, $enqueue = false)
    {
        $ver = empty($ver) ? hooshina_ai_get_version() : $ver;
        if (!empty($handle)) {
            $handle = static::get_handle_name($handle);
            if ($enqueue) {
                wp_enqueue_style($handle, $src, $deps, $ver, $media);
            } else {
                wp_register_style($handle, $src, $deps, $ver, $media);
            }
        }
    }

    public static function register_script($handle, $src, $deps = null, $ver = null, $in_footer = null, $enqueue = false)
    {
        $ver = empty($ver) ? hooshina_ai_get_version() : $ver;
        if (!empty($handle)) {
            $handle = static::get_handle_name($handle);
            if ($enqueue) {
                wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
            } else {
                wp_register_script($handle, $src, $deps, $ver, $in_footer);
            }
        }
    }

    public static function enqueue_style($handle, $src = null, $deps = null, $ver = null, $media = null)
    {
        if (!empty($handle)) {
            self::register_style($handle, $src, $deps, $ver, $media, true);
        }
    }

    public static function enqueue_script($handle, $src = null, $deps = null, $ver = null, $in_footer = null)
    {
        if (!empty($handle)) {
            self::register_script($handle, $src, $deps, $ver, $in_footer, true);
        }
    }

    public static function localize_script($handle, $object_name, $data){
        if(is_array($handle)){
            if (empty($handle)) return false;

            foreach ($handle as $handle_name){
                self::localize_script($handle_name, $object_name, $data);
            }
        } else {
            wp_localize_script($handle, $object_name, $data);
        }
    }

    /**
     *
     * Localize js translate
     *
     * @param null $key
     *
     * @return array|mixed
     */
    public static function get_localize_data($key = null)
    {
        $types = GeneratorHelper::get_generator_types();

        $contentTones = GeneratorHelper::get_content_tones();
        $imageStyles = GeneratorHelper::get_image_styles(Generator::TextToImage);
        $productImageStyles = GeneratorHelper::get_image_styles(Generator::ProductImage);
        $imageSizes = GeneratorHelper::get_image_sizes();
        $languages = GeneratorHelper::get_supported_lanuages();

        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => Helper::generate_nonce(),

            'pluginOptionsUrl' => AdminMenu::get_options_url(),

            'chargePageUrl' => Connection::get_charge_page_url(),

            'generator' => [
                'types' => $types,
                'contentTones' => $contentTones,
                'imageStyles' => $imageStyles,
                'productImageStyles' => $productImageStyles,
                'imageSizes' => $imageSizes,
                'languages' => $languages,
                'defaults' => [
                    'image_style' => Settings::get_default_image_style(),
                    'product_image_style' => Settings::get_default_product_image_style(),
                    'image_size' => Settings::get_default_image_size(),
                    'content_lang' => Helper::get_current_lang(),
                    'content_tone' => Settings::get_default_content_tone(),
                    'post_category' => Settings::get_default_category()
                ]
            ],

            'texts' => [
                'toolbar_button' => __('Hooshina Ai', 'hooshina-ai'),
                'text_button' => __('Hooshina Ai', 'hooshina-ai'),
                'large_button' => __('Hooshina Ai', 'hooshina-ai'),
                'modal_heading' => __('Hooshina Ai', 'hooshina-ai'),
                'modal_license_err_primary' => __('Stay a few steps ahead of the rest with Hooshina Ai.', 'hooshina-ai'),
                'modal_license_err_secondary' => __('You can intelligently generate text and images.', 'hooshina-ai'),
                'connect_button' => __('Connect to Hooshina', 'hooshina-ai'),
                'revoke_connection' => __('Revoke Connection', 'hooshina-ai'),
                'style' => __('Style', 'hooshina-ai'),
                'size' => __('Size', 'hooshina-ai'),
                'language' => __('Language', 'hooshina-ai'),
                'tone' => __('Tone', 'hooshina-ai'),
                'subject_input_placeholder' => __('Type something...', 'hooshina-ai'),
                'doing' => __('Doing...', 'hooshina-ai'),
                'generate' => __('Generate', 'hooshina-ai'),
                'type' => __('Type', 'hooshina-ai'),
                'title_generate' => __('Generate Title with Hooshina Ai', 'hooshina-ai'),
                'description_generate' => __('Generate Description with Hooshina Ai', 'hooshina-ai'),
                'comment_reply' => __('Reply Comment with Hooshina Ai', 'hooshina-ai'),
                'image_generate' => __('Generate Image with Hooshina Ai', 'hooshina-ai'),
                'generate_success' => __('Generate was done successfully.', 'hooshina-ai'),
                'generate_error' => __('An error occurred, please try again.', 'hooshina-ai'),
                'select_image' => __('Select Image', 'hooshina-ai'),
                'click_select_image' => __('Click for select Image', 'hooshina-ai'),
                'generate_product_image' => __('Generate product image', 'hooshina-ai'),
                'answer_for' => __('Answer For:', 'hooshina-ai'),
                'submit' => __('Submit', 'hooshina-ai'),
                'ai_answer' => __('Ai Answer', 'hooshina-ai'),
                'delete_thumb' => __('Delete Thumbnail', 'hooshina-ai'),
                'add_thumb' => __('Add Thumbnail', 'hooshina-ai'),
                'excerpt_generate' => __('Generate Excerpt with Hooshina Ai', 'hooshina-ai'),
                'connected_text' => __('Your website is connected and you can use Hooshina Ai.', 'hooshina-ai'),
                'disconnected_text' => __('To use Hooshina and use artificial intelligence in production', 'hooshina-ai'),
                'connect_page_title' => __('Hooshina Connect', 'hooshina-ai'),
                'wait_for_answer' => __('Receiving reply...', 'hooshina-ai'),
                'select_answer_warning' => __('Select the desired answer.', 'hooshina-ai'),
                'keyword_generate' => __('Generate keyword with Hooshina Ai', 'hooshina-ai'),
                'charge_account' => __('Charge Account', 'hooshina-ai'),
                'product_review_generate' => __('Generate customer reviews summary with Hooshina Ai', 'hooshina-ai'),
                'image_replace_button' => __('Image replace with Hooshina Ai', 'hooshina-ai'),
                'select' => __('Select...', 'hooshina-ai'),
                'select2' => [
                    'errorLoading' => __('The results could not be loaded.', 'hooshina-ai'),
                    'inputTooLong' => __('Please delete %d characters', 'hooshina-ai'),
                    'inputTooShort' => __('Please enter %d or more characters', 'hooshina-ai'),
                    'loadingMore' => __('Loading more results...', 'hooshina-ai'),
                    'maximumSelected' => __('You can only select %d items', 'hooshina-ai'),
                    'noResults' => __('No results found', 'hooshina-ai'),
                    'searching' => __('Searching...', 'hooshina-ai')
                ],
                'insufficient_balance' => __('Your account balance is insufficient', 'hooshina-ai'),
                'insufficient_balance_description'=> __('To use Hooshina services, you must top up your account.', 'hooshina-ai'),
                'seo_helper' => __('Before entering SEO information, be sure to complete the post title and content.', 'hooshina-ai')
            ]
        );

        $params = apply_filters('hooshina_script_localize_data', $params);

        return (!empty($key)) ? $params[$key] : $params;
    }

    public static function get_css($name){
        return HOOSHINA_AI_CSS_URL . $name . '.css';
    }

    public static function get_js($name){
        return HOOSHINA_AI_JS_URL . $name . '.js';
    }

    public static function get_img($name, $extension = 'png'){
        return HOOSHINA_AI_IMG_URL . $name . '.' . $extension;
    }
}